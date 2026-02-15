# Restaurant Management

Nowoczesny system do zarządzania restauracją zbudowany w oparciu o **Laravel 12**, **Blade + Tailwind CSS**, **Laravel Breeze** oraz **Laravel Reverb** (zdarzenia realtime).

Projekt obejmuje pełny przepływ operacyjny restauracji:
- zarządzanie salą (stoliki + rezerwacje),
- obsługę zamówień od kelnera do kuchni,
- panel kuchenny i panel kelnerski,
- fakturowanie,
- harmonogramy zmian pracowników,
- dashboard menedżerski z KPI, alertami i feedem aktywności.


## Spis treści
1. [Najważniejsze funkcje](#najważniejsze-funkcje)
2. [Role i autoryzacja](#role-i-autoryzacja)
3. [Stack technologiczny](#stack-technologiczny)
4. [Architektura projektu](#architektura-projektu)
5. [Model danych (skrót)](#model-danych-skrót)
6. [Wymagania](#wymagania)
7. [Szybki start (lokalnie)](#szybki-start-lokalnie)
8. [Szybki start przez Docker Sail](#szybki-start-przez-docker-sail)
9. [Konta testowe](#konta-testowe)
10. [Uruchamianie testów i jakości kodu](#uruchamianie-testów-i-jakości-kodu)
11. [Realtime i kanały broadcast](#realtime-i-kanały-broadcast)
12. [Przydatne komendy Artisan](#przydatne-komendy-artisan)
13. [Najczęstsze problemy](#najczęstsze-problemy)
14. [Roadmapa rozwoju](#roadmapa-rozwoju)

---

## Najważniejsze funkcje

### Dashboard operacyjno-menedżerski
- KPI dzienne i miesięczne (przychód, liczba zamówień, średnia wartość, obłożenie stolików).
- Segmenty dashboardu zależne od roli (manager / chef / waiter).
- Alerty operacyjne (np. brak wolnych stolików, zamówienia zalegające w kuchni, brak kucharza na zmianie).
- Feed aktywności realtime (zamówienia, rezerwacje, faktury, statusy pozycji zamówień).

### Zamówienia
- Tworzenie zamówienia dla stolika z pozycjami menu.
- Statusy zamówień i pozycji zamówień (od `pending` do `served`/`paid`).
- Logika biznesowa wydzielona do serwisu (`OrderService`).

### Kuchnia i kelnerzy
- Panel kuchenny z kolejką pozycji do przygotowania.
- Panel kelnerski z pozycjami gotowymi do wydania (`ready`).
- Aktualizacje statusów w czasie rzeczywistym przez eventy i kanały prywatne.

### Rezerwacje i stoliki
- Zarządzanie stolikami (`available`, `occupied`, `reserved`).
- Rezerwacje z cyklem życia (`pending`, `confirmed`, `cancelled`, `completed`).

### Kadry i finanse
- Zarządzanie pracownikami i rolami.
- Zmiany (`morning`, `evening`, `full_day`).
- Faktury z metodami płatności (`cash`, `card`, `online`).

---

## Role i autoryzacja
Aplikacja korzysta z polityk Laravel (`app/Policies`) oraz kontroli uprawnień zależnej od roli użytkownika.


## Stack technologiczny

### Backend
- PHP **8.5+**
- Laravel **12**
- Eloquent ORM, Policies, Events/Broadcasting
- DomPDF (`barryvdh/laravel-dompdf`)

### Frontend
- Blade Templates + Blade Components
- Tailwind CSS
- Alpine.js
- ApexCharts (wizualizacje dashboardu)

### Realtime
- Laravel Reverb
- Laravel Echo + kanały prywatne

### Baza danych i infrastruktura
- MySQL 8 / MariaDB
- Redis (cache / wsparcie dla realtime)
- Mailpit (lokalny SMTP pod Sail)

---

## Architektura projektu

```text
app/
├── Http/Controllers/    # kontrolery zasobów + kitchen/waiter/dashboard
├── Models/              # modele domenowe (Order, Table, Reservation, Invoice...)
├── Services/            # logika biznesowa (OrderService, DashboardService, statystyki)
├── Policies/            # autoryzacja RBAC
├── Events/              # zdarzenia realtime
└── Data/                # payloady do feedu dashboardu

resources/views/         # widoki Blade pogrupowane modułowo
database/migrations/     # schemat danych
database/seeders/        # dane startowe
routes/web.php           # routing panelu WWW
routes/channels.php      # autoryzacja kanałów broadcast
```

---

## Model danych (skrót)
Główne encje:
- `users` (rola: manager/waiter/chef),
- `tables`,
- `reservations`,
- `dishes` + `menu_items`,
- `orders` + `order_items` (`ready_at` dla pomiaru wydania),
- `shifts`,
- `invoices`.

Relacje są utrzymywane kluczami obcymi i `onDelete('cascade')`.

---

## Wymagania
- PHP 8.5+
- Composer 2+
- Node.js 18+ i npm
- MySQL 8+ (lub MariaDB)
- (opcjonalnie) Redis
- (opcjonalnie) Docker + Docker Compose (dla Sail)

---

## Szybki start (lokalnie)

1. **Instalacja zależności**
   ```bash
   composer install
   npm install
   ```

2. **Konfiguracja środowiska**
   ```bash
   copy .env.example .env
   cp .env.example .env
   php artisan key:generate
   ```

3. **Uzupełnij konfigurację bazy** w `.env`:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=restaurant_management`
   - `DB_USERNAME=...`
   - `DB_PASSWORD=...`

4. **Migracje + seed**
   ```bash
   php artisan migrate --seed
   ```

5. **Uruchom aplikację**
   ```bash
   php artisan serve
   npm run dev
   ```

Domyślnie panel będzie dostępny pod: `http://127.0.0.1:8000`.

---

## Szybki start przez Docker Sail

1. Skonfiguruj `.env`:
   ```bash
   cp .env.example .env
   ```

2. Uruchom kontenery:
   ```bash
   ./vendor/bin/sail up -d
   ```

3. Wykonaj setup:
   ```bash
   ./vendor/bin/sail composer install
   ./vendor/bin/sail npm install
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ./vendor/bin/sail npm run dev
   ```

Usługi (domyślnie):
- aplikacja: `http://localhost`
- Vite: `http://localhost:5173`
- Mailpit UI: `http://localhost:8025`
- Reverb: port `8080`

---

## Konta testowe
Po `php artisan migrate --seed` dostępne są konta:

- **Manager**: `manager@restaurant.com` / `password`
- **Waiter**: `waiter@restaurant.com` / `password`
- **Chef**: `chef@restaurant.com` / `password`

---

## Uruchamianie testów i jakości kodu

```bash
php artisan test
./vendor/bin/phpunit
./vendor/bin/pint
npm run build
```

> Uwaga: część testów może wymagać poprawnie skonfigurowanej bazy testowej (`.env.testing`).

---

## Realtime i kanały broadcast
Aplikacja wysyła zdarzenia m.in. dla:
- utworzenia zamówienia,
- utworzenia/zmiany rezerwacji,
- wystawienia faktury,
- aktualizacji statusu pozycji zamówienia.

Kanały prywatne:
- `private-kitchen`
- `private-dashboard`

Autoryzacja kanałów jest realizowana w `routes/channels.php` na podstawie roli użytkownika.

---

## Przydatne komendy Artisan

```bash
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
php artisan queue:work
php artisan reverb:start --debug
```

---

## Najczęstsze problemy

### 1) Brak połączenia z bazą
- Sprawdź dane `DB_*` w `.env`.
- Upewnij się, że baza istnieje i użytkownik ma uprawnienia.

### 2) Frontend się nie odświeża
- Uruchom `npm run dev` i zweryfikuj port Vite.
- Wyczyść cache:
  ```bash
  php artisan optimize:clear
  ```

### 3) Realtime nie działa
- Sprawdź konfigurację Reverb/Echo w `.env`.
- Upewnij się, że działa Redis oraz serwer Reverb.

---

## Roadmapa rozwoju
- [ ] Zaawansowane raporty finansowe (export CSV/PDF, porównania okresowe).
- [ ] Powiadomienia push/email o statusie zamówień i rezerwacji.
- [ ] Moduł magazynowy (stany składników + alerty braków).
- [ ] Testy E2E krytycznych ścieżek (kitchen ↔ waiter ↔ invoice).
- [ ] Integracja płatności online (gateway).

---