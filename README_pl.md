# Restaurant Management

<div align="center">

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.5+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

</div>

**Restaurant Management System** to aplikacja do obsługi restauracji: zamówienia, rezerwacje, stoliki, kuchnia, kelnerzy, faktury i grafiki. Zbudowana w Laravel 12 z real-time (WebSocket), kontrolą dostępu wg ról oraz sidebarowym UI (Blade, Tailwind, Alpine.js).

## Spis treści

- [Najważniejsze funkcje](#najważniejsze-funkcje)
- [Role i autoryzacja](#role-i-autoryzacja)
- [Stack technologiczny](#stack-technologiczny)
- [Architektura projektu](#architektura-projektu)
- [Model danych (skrót)](#model-danych-skrót)
- [Wymagania](#wymagania)
- [Szybki start (lokalnie)](#szybki-start-lokalnie)
- [Szybki start przez Docker Sail](#szybki-start-przez-docker-sail)
- [Konta testowe](#konta-testowe)
- [Uruchamianie testów i jakości kodu](#uruchamianie-testów-i-jakości-kodu)
- [Realtime i kanały broadcast](#realtime-i-kanały-broadcast)
- [Przydatne komendy Artisan](#przydatne-komendy-artisan)
- [Najczęstsze problemy](#najczęstsze-problemy)
- [Roadmapa rozwoju](#roadmapa-rozwoju)

---

## Najważniejsze funkcje

### Dashboard & Analytics

- **KPI w czasie rzeczywistym**: przychód, zamówienia, aktywne stoliki, kolejka kuchni
- **Wykresy (ApexCharts)**: przychód w czasie, metody płatności, zakres dat (Today / 7 / 30 dni)
- **Wskaźniki**: wydajność kuchni, top dania
- **Alerty**: np. brak personelu, zaległe zamówienia
- **Widoki zależne od roli**: Manager widzi pełny dashboard, kelner/kucharz/barman — uproszczony

### Zamówienia (Orders)

- **Wielopozycyjne zamówienia** z przypisaniem do stolika
- **Śledzenie statusu** w czasie rzeczywistym
- **Edycja zamówień** (m.in. pozycje, stolik)
- **Filtry**: wszystkie / dzisiejsze / pending

### Kitchen Display System (KDS)

- **Tablica zamówień** na żywo (Kanban: Pending → Preparing → Ready)
- **Zmiana statusu pozycji** jednym kliknięciem
- **WebSocket**: brak odświeżania strony

### Widok kelnera (Waiter)

- **Moje stoliki**: tylko stoliki przypisane do kelnera (przez Table Assignment)
- **Pozycje gotowe do podania** z możliwością „Mark as served”
- **Nadchodzące rezerwacje** dla jego stolików: „Guests arrived” (seated), „No show”, zmiana statusu rezerwacji
- **Real-time** przez Echo/Reverb

### Stoliki i sale (Tables & Rooms)

- **Sale (Rooms)** z kolorami i opisami
- **Stoliki** w salach lub „Unassigned”; **przypisanie kelnera** do stolika (aktywny shift)
- **Grid View**: przeciąganie stolików (SortableJS) — kolejność w sali i między salami; przeciąganie sal (uchwyt)
- **Table View**: lista stolików
- **Statusy**: Available, Occupied, Reserved; **real-time** aktualizacja statusu stolika (broadcast)

### Rezerwacje

- **Kalendarz (FullCalendar)** oraz widok tabelaryczny
- **Przypisanie do stolika**, wykrywanie konfliktów
- **Statusy**: pending, confirmed, seated, completed, cancelled, no_show
- **Auto „Completed”** przy rejestracji płatności za zamówienie powiązane z rezerwacją

### Menu i dania

- **Dishes**: kategorie (Starter, Main, Dessert, Drink, Side), ceny, dostępność
- **Menu Items**: pozycje w menu powiązane z daniami i cenami

### Faktury i płatności

- **Faktury z zamówień**, wiele metod płatności (Cash, Card, Online)
- **NIP**, drukowanie (DomPDF)
- **Generowanie i opłacenie** w jednym kroku

### Kadry i grafik (Shifts)

- **Użytkownicy** z rolami: Manager, Chef, Waiter, Bartender, Host
- **Shifts**: tworzenie zmian (dzień, typ, użytkownik), konflikty (nakładanie, max godziny)
- **Widok tabelaryczny** + **kalendarz (FullCalendar)** — resource view (kolumny = pracownicy), filtry ról (Waiters, Chefs, Bartenders, Managers)
- **Dostęp**: Manager i Host widzą wszystkie zmiany; kelner/kucharz/barman — tylko swoje

### Real-time (Broadcasting)

- **Laravel Reverb** (WebSocket)
- **Kanały prywatne**: `kitchen`, `dashboard`, `tables` (autoryzacja wg ról)
- **Eventy**: m.in. `OrderCreated`, `OrderItemStatusUpdated`, `TableStatusUpdated`

---

## Role i autoryzacja

| Rola        | Dostęp |
|------------|--------|
| **Manager** | Pełny: dashboard, zamówienia, stoliki, sale, rezerwacje, menu, użytkownicy, faktury, grafik, kuchnia, kelner. |
| **Waiter**  | Zamówienia, rezerwacje, widok kelnera (moje stoliki, rezerwacje), zmiany (tylko swoje). Brak dostępu do /tables (zarządzanie stolikami). |
| **Host**    | Stoliki (floor plan, zmiana statusu), rezerwacje (pełny CRUD), zamówienia (tylko podgląd), zmiany (podgląd wszystkich). Dashboard (uproszczony). Brak kuchni, widoku kelnera i zarządzania. |
| **Chef**    | Kuchnia, zmiany (swoje), dashboard (uproszczony). |
| **Bartender** | Kuchnia (np. drinki), zmiany (swoje), dashboard (uproszczony). |

Autoryzacja: **Policies** (Order, Table, Room, Reservation, Dish, MenuItem, User, Invoice, Shift, Kitchen, Waiter) + middleware `auth`. API pod `/api/*` (np. shifts/calendar-events, reservations/calendar-events, tables/reorder) — te same middleware co web.

---

## Stack technologiczny

| Warstwa     | Technologie |
|------------|-------------|
| **Backend** | PHP 8.5+, Laravel 12, Eloquent ORM, Policies, Events/Broadcasting, Form Requests |
| **Frontend** | Blade, Blade Components, Tailwind CSS, Alpine.js, ApexCharts, FullCalendar, SortableJS, Tippy.js |
| **Real-time** | Laravel Reverb, Laravel Echo, Pusher.js |
| **Baza danych** | MySQL 8 / MariaDB |
| **Cache** | Redis / file driver |
| **PDF** | DomPDF (`barryvdh/laravel-dompdf`) |
| **Infrastruktura** | Docker Compose (Laravel Sail), Vite |

---

## Architektura projektu

- **`app/Http/Controllers`** — kontrolery web (resource + dedykowane akcje, np. Kitchen, Waiter, Dashboard).
- **`app/Services`** — logika biznesowa: OrderService, TableService, ReservationService, InvoiceService, DashboardService, KitchenService, WaiterDashboardService, ShiftCreationService, ShiftAnalyticsService, ShiftCalendarService, CalendarRangeService itd.
- **`app/Models`** — User, Room, Table, TableAssignment, Order, OrderItem, Dish, MenuItem, Reservation, Shift, Invoice.
- **`app/Policies`** — autoryzacja dla powyższych modeli (+ Kitchen, Waiter).
- **`app/Enums`** — UserRole, OrderStatus, OrderItemStatus, TableStatus, ReservationStatus, ShiftType, DishCategory, PaymentMethod.
- **`app/Events`** — eventy broadcast: OrderCreated, OrderItemCreated, OrderItemStatusUpdated, TableStatusUpdated, ReservationCreated, ReservationUpdated, InvoiceIssued.
- **`app/Http/Requests`** — walidacja (Store/Update + AssignTable, ReorderTables, UpdateKitchenItemStatus itd.).
- **Frontend**: layout z **sidebar** (`layouts/sidebar`, `layouts/app`), komponenty Blade (flash, page-header, delete-button, tabs, sidebar-link, dropdown). Vite: `app.js`, `dashboard-chart.js`, `shifts-calendar.js`, `reservations-calendar.js`, `tables-sortable.js`.

---

## Model danych (skrót)

- **User** — first_name, last_name, email, role (enum), phone_number, notes.
- **Room** — name, description, color, sort_order.
- **Table** — table_number, capacity, room_id (nullable), sort_order, status (enum); relacje: room, activeAssignment (TableAssignment), orders.
- **TableAssignment** — table_id, user_id, shift_id (aktywne przypisanie kelnera do stolika).
- **Order** — table_id, status, total_price, ordered_at; OrderItem (menu_item_id, quantity, unit_price, status).
- **Reservation** — table_id, guest_name, guest_phone, reserved_at, party_size, status (enum).
- **Dish** — name, category (enum), price, is_available.
- **MenuItem** — dish_id, name, price (override).
- **Shift** — user_id, date, start_time, end_time, type (enum).
- **Invoice** — order_id, payment_method (enum), tax_id, amount, issued_at.

---

## Wymagania

- **PHP** 8.5+
- **Composer** 2.x
- **Node.js** 18+ i **npm** (lub yarn) — do Vite i assetów
- **MySQL** 8.0 lub **MariaDB** 10.3+
- **Redis** (opcjonalnie, do cache/session/queue)
- Do **real-time**: uruchomiony serwer WebSocket (Laravel Reverb)

---

## Szybki start (lokalnie)

1. **Zależności**
   ```bash
   composer install
   npm install
   ```

2. **Środowisko**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Baza w `.env`**: `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

4. **Migracje i seed**
   ```bash
   php artisan migrate --seed
   ```

5. **Uruchomienie**
   ```bash
   php artisan serve
   npm run dev
   php artisan reverb:start --debug
   ```

(Opcjonalnie: `php artisan queue:work` jeśli używasz kolejek.)

---

## Szybki start przez Docker Sail

1. **`.env`**
   ```bash
   cp .env.example .env
   ```

2. **Kontenery**
   ```bash
   ./vendor/bin/sail up -d
   ```

3. **Setup**
   ```bash
   ./vendor/bin/sail composer install
   ./vendor/bin/sail npm install
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ./vendor/bin/sail npm run build
   ```

4. **Real-time** (w osobnym terminalu):
   ```bash
   ./vendor/bin/sail artisan reverb:start --debug
   ```

Aplikacja: `http://localhost` (port zależy od konfiguracji Sail).

---

## Konta testowe

Po `php artisan migrate --seed`:

| Rola       | E-mail                   | Hasło     |
|-----------|---------------------------|-----------|
| Manager   | `manager@restaurant.com`  | `password` |
| Kelner   | `waiter@restaurant.com`   | `password` |
| Kelner 2 | `waiter2@restaurant.com`  | `password` |
| Kucharz  | `chef@restaurant.com`     | `password` |
| Barman   | `bartender@restaurant.com`| `password` |
| Host     | `host@restaurant.com`     | `password` |

---

## Uruchamianie testów i jakości kodu

```bash
php artisan test
```

Jakość kodu (Laravel Pint):

```bash
./vendor/bin/pint
```

---

## Realtime i kanały broadcast

- **Reverb**: `php artisan reverb:start --debug` (lub przez Sail).
- **Kanały** (`routes/channels.php`):
  - `kitchen` — Manager, Chef, Waiter.
  - `dashboard` — Manager, Chef, Waiter, Host.
  - `tables` — Manager, Waiter, Host.
- **Eventy**: np. `OrderItemStatusUpdated` (kuchnia/kelner), `TableStatusUpdated` (grid stolików), `OrderCreated`.

W frontendzie: Laravel Echo + Pusher driver łączą się z Reverb; subskrypcja kanałów prywatnych po zalogowaniu.

---

## Przydatne komendy Artisan

```bash
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
php artisan queue:work
php artisan reverb:start --debug
```

Build assetów (produkcja): `npm run build` (lub `sail npm run build`).

---

## Najczęstsze problemy

- **Stoliki / kuchnia nie aktualizują się na żywo** — sprawdź, czy Reverb działa i czy w `.env` masz poprawną konfigurację `BROADCAST_*`, `REVERB_*`. W przeglądarce: czy nie ma 403 na `broadcasting/auth` (kanały w `channels.php`).
- **Po zmianie kodu JS/CSS brak efektu** — `npm run dev` (lub `sail npm run dev`) oraz ewentualnie `npm run build` i odświeżenie z pominięciem cache (Ctrl+F5).
- **Błąd 500 po migracji** — `php artisan config:clear` i `php artisan cache:clear`; sprawdź `.env` i uprawnienia do `storage/`, `bootstrap/cache/`.

---

## Roadmapa rozwoju

- [ ] Zaawansowane raporty finansowe (eksport CSV/PDF, porównania okresowe).
- [ ] Powiadomienia push/email o statusie zamówień i rezerwacji.
- [ ] Moduł magazynowy (stany składników, alerty braków).
- [ ] Testy E2E krytycznych ścieżek (kitchen ↔ waiter ↔ invoice).
- [ ] Integracja płatności online (gateway).

---
