# Plan implementacji dashboardu (dashboard2.md)

Kroki uporządkowane tak, aby najpierw była warstwa danych i logiki, potem widoki i real-time.

---

## Faza 1: Architektura i warstwa danych

### Krok 1.1 – DashboardService (agregacja danych)
**Cel:** Jeden serwis zamiast wielu zapytań w kontrolerze.

- [ ] Utworzyć `App\Services\DashboardService`.
- [ ] **Wydajność – zgrupowane zapytania:** Nie robić osobnego zapytania na każdą metrykę. Np. jedno zapytanie dla revenue:
  - `Invoice::selectRaw("DATE(issued_at) as date, SUM(amount) as total")->whereBetween('issued_at', [now()->subDays(7), now()])->groupBy('date')` → w PHP wyliczyć: today, yesterday, last week avg i procenty.
  - Analogicznie: orders (Order/Invoice po dacie), tabele (jedno zapytanie count), kitchen queue (jedno zapytanie z groupBy status), rezerwacje (jedno count).
- [ ] Metoda `getKpis(): array` zwracająca wszystkie KPI + porównania w **1–2 zapytaniach z agregacjami** (bez 20+ pojedynczych zapytań).
- [ ] **Cache:** `Cache::remember('dashboard:kpis', 60, fn() => $this->calculateKpis());` – KPI nie muszą być liczone przy każdym requestcie (np. TTL 60 s dla managera).

**Zależności:** Modele Order, Invoice, Table, OrderItem, Reservation (już istnieją).

---

### Krok 1.2 – Dane do wykresów i breakdown
**Cel:** Dane pod Revenue Chart i Payment Breakdown.

- [ ] W `DashboardService` dodać:
  - `revenueByDay(int $days = 7): array` (np. `[date => amount]` dla ostatnich 7 lub 30 dni)
  - `paymentMethodBreakdown(): array` (np. `['card' => sum, 'cash' => sum, 'online' => sum]` z Invoice na dziś lub na okres)
- [ ] **Cache:** Wykres przychodu 7/30 dni nie musi być live – np. `Cache::remember('dashboard:revenue_by_day:7', 300, ...)` (5 min). Podobnie breakdown płatności – krótki TTL (np. 60–300 s).
- [ ] Ustalić zakres: tylko dziś vs ostatnie 7/30 dni (np. breakdown dziś, wykres 7/30).

---

### Krok 1.3 – Kitchen Performance
**Cel:** Metryki kuchni + kolorowanie (OK / Warning / Critical).

- [ ] **Źródło czasu przygotowania:** `updated_at` jest nieprecyzyjne (zmienia się przy każdej edycji). Dodać kolumnę `ready_at` (lub `completed_at`) w `order_items` i wypełniać ją przy przejściu na status `ready`/`served`. Metryki liczyć na podstawie `ready_at` (lub `created_at` → `ready_at`).
- [ ] W `DashboardService` dodać:
  - `avgPrepTimeMinutes(): ?float` (średni czas od created_at do **ready_at** dla OrderItem ze statusem ready/served; tylko dziś)
  - `longestWaitingOrderMinutes(): ?int` (najdłużej czekające zamówienie – pending/preparing)
  - `pendingOver15MinCount(): int` (OrderItem pending, created_at < now()->subMinutes(15))
- [ ] Metoda `getKitchenPerformance(): array` zwracająca powyższe + status: `ok` / `warning` / `critical` (progi np. 15 / 20 min).

---

### Krok 1.4 – Staff & Shifts
**Cel:** Kto na zmianie, następna zmiana.

- [ ] W `DashboardService` dodać:
  - `staffOnShiftToday(): array` (np. `['chef' => 2, 'waiter' => 3]` – grupowanie User po Shift na dziś po `role`)
  - `nextShiftChange(): ?string` (np. czas następnej zmiany z Shift – np. najbliższy `date` + shift_type po aktualnej godzinie)
- [ ] Wykorzystać modele User, Shift; uwzględnić pole `date` i ewentualnie godziny w Shift (jeśli są – inaczej założyć np. morning/evening i mapować na godziny).

---

### Krok 1.5 – Alerty (Alert Center)
**Cel:** Lista warunków do wyświetlenia w Alert Center.

- [ ] W `DashboardService` dodać `getAlerts(): array` zwracający listę alertów, np.:
  - `['type' => 'orders_pending_20', 'message' => '...', 'severity' => 'warning']`
- [ ] Warunki:
  - Zamówienia (OrderItem) pending > 20 min
  - Brak kucharza na zmianie dziś (staffOnShiftToday chef == 0)
  - Brak wolnych stolików (activeTablesCount == total tables)
  - Rezerwacja na dziś bez potwierdzenia (np. Reservation gdzie status != confirmed)
- [ ] Każdy alert: typ, treść, link (opcjonalnie), severity (info / warning / critical).
- [ ] **Priorytetyzacja:** Sortowanie alertów: najpierw `critical`, potem `warning`, potem `info`; w ramach tej samej severity – np. wg czasu (najnowsze lub najpilniejsze na górze). Zwracać z `getAlerts()` już posortowaną tablicę.

---

### Krok 1.6 – Top Performers (opcjonalnie w tej fazie)
**Cel:** Top 5 dań, najlepszy kelner, dominująca metoda płatności.

- [ ] W `DashboardService` dodać:
  - `topDishesToday(int $limit = 5): Collection` (np. OrderItem + MenuItem + Dish, grupowanie po dish, sum quantity, sort desc)
  - `bestWaiterByRevenueToday(): ?User` (Order → User, sum po invoice amount)
  - `mostUsedPaymentMethodToday(): ?string` (Invoice today, group by payment_method, max count)

---

## Faza 2: Kontroler i routing

### Krok 2.1 – DashboardController i role
**Cel:** Jeden endpoint dashboardu, dane zależne od roli.

- [ ] Utworzyć `App\Http\Controllers\DashboardController`.
- [ ] Wstrzyknąć `DashboardService`.
- [ ] Metoda `index()`:
  - Dla **manager**: wywołać pełny zestaw (KPI, wykresy, kitchen, staff, alerty, top performers).
  - Dla **chef**: tylko: kitchen queue, prep time, live feed (te same dane co manager dla kuchni + feed).
  - Dla **waiter**: tylko: active tables, reservations today, orders (lista/liczba) – uproszczone KPI.
- [ ] Zwracać `view('dashboard.index', compact(...))` z odpowiednim zestawem zmiennych (np. `$kpis`, `$revenueByDay`, `$paymentBreakdown`, `$kitchenPerformance`, `$staffOnShift`, `$alerts`, `$topPerformers`).
- [ ] Route: `Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');` (zastąpić obecną closure).

---

## Faza 3: Widok – layout i KPI

### Krok 3.1 – Layout strony dashboardu
**Cel:** Układ zgodny z dashboard2 (KPI → wykresy → Kitchen | Staff → Live Feed → Quick Actions).

- [ ] Stworzyć `resources/views/dashboard/index.blade.php` (lub przerobić obecny `dashboard.blade.php`).
- [ ] Sekcje w kolejności:
  1. Górny pasek: Performance Indicator (badge) + tytuł.
  2. KPI Cards (siatka 6 kart).
  3. Drugi rząd: Revenue Chart | Payment Breakdown.
  4. Trzeci rząd: Kitchen Performance Panel | Staff & Shifts Overview.
  5. Live Activity Feed (pełna szerokość).
  6. Quick Actions (obecne kafelki: New Order, New Reservation, Add Staff, Go to Kitchen itd.).
- [ ] Zastosować layout: minimalistycznie, dużo whitespace, kolory głównie dla statusów.

---

### Krok 3.2 – Performance Indicator (badge)
**Cel:** Jeden badge: Good day / Average / Below target.

- [ ] W `DashboardService` dodać `performanceIndicator(): string` (np. `good` / `average` / `below`) na podstawie **revenue today vs średnia przychodu z ostatnich 7 dni**, z ustalonymi progami:
  - **good** → revenue dziś **> +10%** względem średniej 7-dniowej
  - **average** → od **-10%** do **+10%**
  - **below** → **< -10%**
- [ ] W widoku: jeden badge w headerze (zielony / żółty / czerwony) + krótki tekst.

---

### Krok 3.3 – KPI Cards
**Cel:** 6 kart: Orders Today, Revenue Today, Avg Order Value, Active Tables, Kitchen Queue, Reservations Today.

- [ ] Wyświetlić wartości z `$kpis` (z `DashboardService::getKpis()`).
- [ ] Dla Orders/Revenue dodać „vs yesterday” / „vs last week” (np. +12% / -8%) z `$kpis['revenue_vs_yesterday']` itd.
- [ ] Kitchen Queue: np. „pending: X, preparing: Y” lub dwa małe badge’e.

---

## Faza 4: Wykresy

### Krok 4.1 – Biblioteka wykresów
**Cel:** Wykres przychodu 7/30 dni.

- [ ] Dodać ApexCharts lub Chart.js (np. przez CDN lub npm + Vite).
- [ ] Przekazać z kontrolera: `$revenueByDay` (np. etykiety + wartości dla 7 i 30 dni).

---

### Krok 4.2 – Revenue Chart (7 / 30 dni)
**Cel:** Jeden wykres z przełącznikiem 7 / 30 dni.

- [ ] Komponent wykresu (np. w Blade + Alpine lub mały JS).
- [ ] Dane: `revenueByDay(7)` i `revenueByDay(30)` z `DashboardService`.
- [ ] Przełącznik: 7 dni | 30 dni (np. tabs lub przyciski).

---

### Krok 4.3 – Payment Methods Breakdown
**Cel:** Wykres kołowy (lub słupkowy): cash / card / online.

- [ ] Dane: `$paymentBreakdown` z `DashboardService`.
- [ ] Wyświetlić procenty + etykiety (np. 45% card, 35% online, 20% cash).

---

## Faza 5: Panele operacyjne

### Krok 5.1 – Kitchen Performance Panel
**Cel:** Metryki + kolory OK / Warning / Critical.

- [ ] Wyświetlić: średni czas przygotowania, najdłużej czekające zamówienie, liczba pending > 15 min.
- [ ] Kolorowanie: np. zielony jeśli brak problemów, żółty jeśli pending > 15 min, czerwony jeśli > 20 min (zgodnie z `getKitchenPerformance()`).

---

### Krok 5.2 – Staff & Shifts Overview
**Cel:** Kto na zmianie, następna zmiana.

- [ ] Widget: „Chef: 2 on shift”, „Waiters: 3 on shift”, „Next shift change: 16:00”.
- [ ] Dane: `$staffOnShift`, `$nextShiftChange` z `DashboardService`.

---

### Krok 5.3 – Alert Center
**Cel:** Lista alertów z możliwością kliknięcia (link do zamówień / stolików / rezerwacji).

- [ ] Sekcja „Alert Center” z listą `$alerts`.
- [ ] Każdy alert: ikona (severity), tekst, opcjonalnie link (np. do listy zamówień lub konkretnego zamówienia).
- [ ] Jeśli brak alertów: komunikat „No alerts” lub ukryć sekcję.

---

## Faza 6: Real-time (Live Activity Feed)

### Krok 6.1 – Eventy pod Live Feed
**Cel:** Jedna „taśma” zdarzeń: zamówienia, statusy, rezerwacje, faktury.

- [ ] Zdecydować kanał: np. `private-dashboard` lub rozszerzyć `kitchen` (tylko dla managerów).
- [ ] W `routes/channels.php`: kanał `dashboard` (np. `private-dashboard`) dla roli manager (i ewentualnie chef/waiter z ograniczonym zestawem eventów).
- [ ] Eventy (już masz część):
  - Order created → OrderCreated.
  - OrderItem status updated → OrderItemStatusUpdated (wykorzystać).
  - Reservation created/updated → ReservationCreated / ReservationUpdated (broadcast).
  - Invoice issued → InvoiceIssued (broadcast).
- [ ] **Wspólny format payloadu:** Utworzyć DTO/transformer (np. `DashboardFeedEventFormatter` lub `DashboardFeedItem` DTO) ujednolicający format eventów w feedzie. Każdy event po stronie backendu mapować na ten sam kształt: `type`, `message`, `time`, `link` (opcjonalnie), ewentualnie `severity`. Dzięki temu frontend ma jeden szablon renderowania – bez rozgałęzień na typ eventu.

---

### Krok 6.2 – Frontend Live Feed
**Cel:** Strumień w stylu: [12:32] Order #241 created – Table 4.

- [ ] W widoku dashboardu: sekcja „Live Activity” z listą (np. `<ul>` lub divy).
- [ ] Alpine.js + Echo: subskrypcja kanału `private-dashboard` (lub odpowiedniego), nasłuch na eventy (OrderCreated, OrderItemStatusUpdated, ReservationCreated, InvoiceIssued).
- [ ] Mapowanie eventów na jednolity format (tekst + czas) i dodawanie na górę listy (np. max 50 ostatnich).
- [ ] Opcjonalnie: auto-scroll, czas względny („2 min ago”).

---

## Faza 7: Role-based view

### Krok 7.1 – Ukrywanie sekcji według roli
**Cel:** Manager – wszystko; Chef – kitchen + feed; Waiter – tables, reservations, orders.

- [ ] **Kontroler decyduje, widok tylko renderuje:** `DashboardController` przekazuje tablicę `$sections` (np. `['kpis' => true, 'charts' => true, 'kitchen' => true, ...]`) określającą, które sekcje są widoczne dla danej roli. W Blade: `@if($sections['kitchen'])` – bez rozbudowanych `@role('manager')` / wielu warunków. Łatwiejsze testy i zmiana logiki w jednym miejscu.
- [ ] Manager: wszystkie sekcje. Chef: kitchen, live feed, ewentualnie uproszczone KPI. Waiter: kpis (tables, reservations, orders), live feed, quick actions.

---

### Krok 7.2 – Testy ról
**Cel:** Upewnienie się, że chef i waiter nie widzą danych managera (np. revenue, pełne alerty).

- [x] Manualnie lub prosty test: zalogowani jako manager / chef / waiter – sprawdzić widoczne sekcje i brak błędów 403 tam, gdzie nie trzeba. Zrealizowano: `tests/Feature/DashboardTest.php` (manager pełny zestaw, chef bez Alert Center/Staff/Top Performers, waiter bez Kitchen/Alert Center/Top Performers, guest → redirect na login).

---

## Faza 8: Quick Actions i dopracowanie

### Krok 8.1 – Quick Actions
**Cel:** Obecne kafelki jako sekcja na dole.

- [ ] Przenieść obecne karty z `dashboard.blade.php` (Orders, Tables, Reservations, Menu, Dishes, Staff, Shifts, Invoices) do sekcji „Quick Actions” na dole.
- [ ] Zachować linki i ikony; można zmniejszyć rozmiar (mniejsze karty lub ikony + tekst).

---

### Krok 8.2 – Top Performers (blok w widoku)
**Cel:** Top 5 dań, najlepszy kelner, najczęstsza metoda płatności.

- [ ] Sekcja „Top Performers” (dla managera): wyświetlić `$topDishesToday`, `$bestWaiterToday`, `$mostUsedPaymentMethod` (dane z DashboardService).
- [ ] Układ: np. 3 kolumny lub jedna linia z trzema „kafelkami”.

---

### Krok 8.3 – Responsywność i styl
**Cel:** Minimalistycznie, whitespace, kolory tylko do statusów.

- [x] Sprawdzić układ na mobile/tablet (siatka KPI, dwa panele Kitchen | Staff). Zrealizowano: Kitchen i Staff w jednym rzędzie na lg (`grid grid-cols-1 lg:grid-cols-2`).
- [x] Ujednolicić kolory: zielony/żółty/czerwony tylko dla statusów i alertów; reszta neutralna (szarości, biel).

---

## Faza 9 (opcjonalna): Enterprise

- [ ] Eksport raportów CSV (np. zamówienia/faktury z wybranego okresu).
- [ ] Raport dzienny PDF (podsumowanie KPI + wykres).
- [ ] Top selling dishes (strona raportu lub rozszerzenie Top Performers).
- [ ] Heatmapa godzin sprzedaży (wykres godzinowy).
- [ ] Analiza średniego czasu obsługi stolika (Order created → paid).

---

## Kolejność realizacji (rekomendowana)

1. **Krok 1.1** – DashboardService + getKpis()  
2. **Krok 1.2** – revenueByDay, paymentMethodBreakdown  
3. **Krok 2.1** – DashboardController + route  
4. **Krok 3.1, 3.2, 3.3** – layout, badge, KPI cards  
5. **Krok 1.3, 1.4, 1.5** – kitchen, staff, alerts w DashboardService  
6. **Krok 5.1, 5.2, 5.3** – panele Kitchen, Staff, Alert Center w widoku  
7. **Krok 4.1–4.3** – wykresy Revenue + Payment Breakdown  
8. **Krok 1.6, 8.2** – Top Performers (serwis + widok)  
9. **Krok 6.1, 6.2** – eventy + Live Activity Feed  
10. **Krok 7.1, 7.2** – role-based view + testy  
11. **Krok 8.1, 8.3** – Quick Actions + responsywność i styl  

Po Fazie 8 masz wdrożony pełny plan z dashboard2.md; Faza 9 to rozszerzenia na później.
