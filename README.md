# Restaurant Management

<div align="center">

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

**A comprehensive, real-time restaurant management system built with Laravel**

[Features](#-features) • [Installation](#-installation) • [Usage](#-usage) • [Tech Stack](#-tech-stack) • [Screenshots](#-screenshots)

</div>

**Restaurant Management System** is a full-featured, production-ready application designed to streamline restaurant operations. Built with Laravel 12 and modern web technologies, it offers real-time updates, role-based access control, and comprehensive management tools for orders, reservations, staff, and inventory.

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
- **Real-time KPI Monitoring**: Revenue, orders, active tables, kitchen queue
- **Interactive Charts**: Revenue trends, payment method breakdown (ApexCharts)
- **Performance Indicators**: Kitchen efficiency, staff performance, top dishes
- **Live Activity Feed**: WebSocket-powered real-time updates
- **Role-Specific Views**: Customized dashboards for each user role
- **Alert System**: Critical notifications for pending orders, staff shortages

### Order Management
- **Multi-Item Orders**: Support for complex orders with multiple dishes
- **Order Tracking**: Real-time status updates from placement to payment
- **Table Assignment**: Associate orders with specific tables
- **Quick Actions**: Fast order creation and modification
- **Order History**: Complete audit trail of all orders

### Kitchen Display System (KDS)
- **Real-time Order Board**: Live updates when orders are placed
- **Kanban-Style Workflow**: Pending → Preparing → Ready columns
- **Status Management**: Quick status updates with one click
- **Order Prioritization**: Oldest orders highlighted
- **Live WebSocket Updates**: Zero refresh, instant notifications

### Waiter Interface
- **Ready Items Display**: View all items ready to serve
- **Table Organization**: Orders grouped by table number
- **Quick Serving**: One-click mark as served
- **Real-time Notifications**: Instant updates when items are ready

### Table Management
- **Visual Table Layout**: Grid view of all restaurant tables
- **Status Tracking**: Available, Occupied, Reserved
- **Capacity Management**: Define seating capacity per table
- **Real-time Updates**: Automatic status changes based on orders

### Reservation System
- **Booking**: Customer reservation management
- **Table Assignment**: Auto-assign or manually select tables
- **Conflict Detection**: Prevent double-bookings
- **Status Workflow**: Pending → Confirmed → Completed
- **Customer Information**: Store contact details and party size

### Menu & Inventory
- **Dish Management**: Create and categorize dishes
- **Pricing Control**: Set and update menu item prices
- **Availability Toggle**: Mark items as available/unavailable
- **Category System**: Starters, mains, desserts, drinks, sides

### Invoice & Billing
- **Automated Invoice Generation**: Create invoices from orders
- **Multiple Payment Methods**: Cash, card, online payments
- **Tax ID Support**: VAT/NIP number recording
- **Printable Invoices**: Professional invoice template
- **Payment Tracking**: Complete financial records

### Staff Management
- **Employee Profiles**: Store staff information and roles
- **Shift Scheduling**: Morning, evening, and full-day shifts
- **Role-Based Access**: Manager, chef, waiter permissions
- **Performance Tracking**: Revenue per waiter, dish preparation times

### Real-time Broadcasting
- **Laravel Reverb**: WebSocket server for live updates
- **Private Channels**: Secure, authenticated broadcasting
- **Event-Driven**: OrderCreated, OrderItemStatusUpdated, etc.
- **Zero Latency**: Instant UI updates without polling

---

## 📸 Screenshots (todo)

### Dashboard - Manager View
![Dashboard](docs/screenshots/dashboard.png)
*Comprehensive analytics with revenue charts, KPIs, and live activity feed*

### Kitchen Display System
![Kitchen](docs/screenshots/kitchen.png)
*Real-time order board with Kanban-style workflow*

### Order Management
![Orders](docs/screenshots/orders.png)
*Complete order tracking and management*

### Invoice Generation
![Invoice](docs/screenshots/invoice.png)
*Professional invoice template with print support*

### Waiter Interface
![Waiter](docs/screenshots/waiter.png)
*Ready-to-serve items with quick actions*

---


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

---

## Konta testowe
Po `php artisan migrate --seed` dostępne są konta:

- **Manager**: `manager@restaurant.com` / `password`
- **Waiter**: `waiter@restaurant.com` / `password`
- **Chef**: `chef@restaurant.com` / `password`

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

## Roadmapa rozwoju
- [ ] Zaawansowane raporty finansowe (export CSV/PDF, porównania okresowe).
- [ ] Powiadomienia push/email o statusie zamówień i rezerwacji.
- [ ] Moduł magazynowy (stany składników + alerty braków).
- [ ] Testy E2E krytycznych ścieżek (kitchen ↔ waiter ↔ invoice).
- [ ] Integracja płatności online (gateway).

---