# Restaurant Management

<div align="center">

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.5+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

</div>

**Restaurant Management System** is a full-stack application for restaurant operations: orders, reservations, tables, kitchen display, waiters, invoices, and staff scheduling. Built with Laravel 12, it features real-time updates (WebSocket), role-based access control, and a sidebar UI (Blade, Tailwind, Alpine.js).

## Table of contents

- [Features](#features)
- [Roles and authorization](#roles-and-authorization)
- [Tech stack](#tech-stack)
- [Project architecture](#project-architecture)
- [Data model (overview)](#data-model-overview)
- [Requirements](#requirements)
- [Quick start (local)](#quick-start-local)
- [Quick start with Docker Sail](#quick-start-with-docker-sail)
- [Test accounts](#test-accounts)
- [Tests and code quality](#tests-and-code-quality)
- [Real-time and broadcast channels](#real-time-and-broadcast-channels)
- [Useful Artisan commands](#useful-artisan-commands)
- [Troubleshooting](#troubleshooting)
- [Roadmap](#roadmap)

---

## Features

### Dashboard & Analytics

- **Real-time KPIs**: revenue, orders, active tables, kitchen queue
- **Charts (ApexCharts)**: revenue over time, payment methods, date range (Today / 7 / 30 days)
- **Metrics**: kitchen efficiency, top dishes
- **Alerts**: e.g. staff shortages, pending orders
- **Role-based views**: Manager sees full dashboard; waiter/chef/bartender see a simplified one

### Orders

- **Multi-item orders** with table assignment
- **Real-time status** tracking
- **Order editing** (items, table)
- **Filters**: all / today / pending

### Kitchen Display System (KDS)

- **Live order board** (Kanban: Pending → Preparing → Ready)
- **One-click status** updates for items
- **WebSocket** updates without page refresh

### Waiter view

- **My tables**: only tables assigned to the waiter (via Table Assignment)
- **Ready-to-serve items** with “Mark as served”
- **Upcoming reservations** for their tables: “Guests arrived” (seated), “No show”, status changes
- **Real-time** via Echo/Reverb

### Tables & Rooms

- **Rooms** with colors and descriptions
- **Tables** in rooms or “Unassigned”; **waiter assignment** per table (active shift)
- **Grid view**: drag-and-drop tables (SortableJS) — reorder within and across rooms; drag rooms by handle
- **Table view**: list of tables
- **Statuses**: Available, Occupied, Reserved; **real-time** table status updates (broadcast)

### Reservations

- **Calendar (FullCalendar)** and table view
- **Table assignment**, conflict detection
- **Statuses**: pending, confirmed, seated, completed, cancelled, no_show
- **Auto “Completed”** when payment is recorded for an order linked to the reservation

### Menu & Dishes

- **Dishes**: categories (Starter, Main, Dessert, Drink, Side), prices, availability
- **Menu items**: menu entries linked to dishes and prices

### Invoices & Payments

- **Invoices from orders**, multiple payment methods (Cash, Card, Online)
- **Tax ID**, print (DomPDF)
- **Generate and pay** in one step

### Staff & Shifts

- **Users** with roles: Manager, Chef, Waiter, Bartender, Host
- **Shifts**: create shifts (date, type, user), conflict checks (overlap, max hours)
- **Table view** and **calendar (FullCalendar)** — resource view (columns = staff), role filters (Waiters, Chefs, Bartenders, Managers)
- **Access**: Manager and Host see all shifts; waiter/chef/bartender see only their own shifts

### Real-time (Broadcasting)

- **Laravel Reverb** (WebSocket)
- **Private channels**: `kitchen`, `dashboard`, `tables` (role-based authorization)
- **Events**: e.g. `OrderCreated`, `OrderItemStatusUpdated`, `TableStatusUpdated`

---

## Roles and authorization

| Role        | Access |
|------------|--------|
| **Manager** | Full: dashboard, orders, tables, rooms, reservations, menu, users, invoices, shifts, kitchen, waiter view. |
| **Waiter**  | Orders, reservations, waiter view (my tables, reservations), shifts (own only). No access to /tables (table management). |
| **Host**    | Tables (floor plan, change status), reservations (full CRUD), orders (view only), shifts (view all). Dashboard (simplified). No kitchen, waiter view, or management. |
| **Chef**    | Kitchen, shifts (own), dashboard (simplified). |
| **Bartender** | Kitchen (e.g. drinks), shifts (own), dashboard (simplified). |

Authorization: **Policies** (Order, Table, Room, Reservation, Dish, MenuItem, User, Invoice, Shift, Kitchen, Waiter) and `auth` middleware. API under `/api/*` (e.g. shifts/calendar-events, reservations/calendar-events, tables/reorder) uses the same middleware as web.

---

## Tech stack

| Layer      | Technologies |
|-----------|--------------|
| **Backend** | PHP 8.5+, Laravel 12, Eloquent ORM, Policies, Events/Broadcasting, Form Requests |
| **Frontend** | Blade, Blade Components, Tailwind CSS, Alpine.js, ApexCharts, FullCalendar, SortableJS, Tippy.js |
| **Real-time** | Laravel Reverb, Laravel Echo, Pusher.js |
| **Database** | MySQL 8 / MariaDB |
| **Cache** | Redis / file driver |
| **PDF** | DomPDF (`barryvdh/laravel-dompdf`) |
| **Infrastructure** | Docker Compose (Laravel Sail), Vite |

---

## Project architecture

- **`app/Http/Controllers`** — web controllers (resource + custom actions, e.g. Kitchen, Waiter, Dashboard).
- **`app/Services`** — business logic: OrderService, TableService, ReservationService, InvoiceService, DashboardService, KitchenService, WaiterDashboardService, ShiftCreationService, ShiftAnalyticsService, ShiftCalendarService, CalendarRangeService, etc.
- **`app/Models`** — User, Room, Table, TableAssignment, Order, OrderItem, Dish, MenuItem, Reservation, Shift, Invoice.
- **`app/Policies`** — authorization for the above models (+ Kitchen, Waiter).
- **`app/Enums`** — UserRole, OrderStatus, OrderItemStatus, TableStatus, ReservationStatus, ShiftType, DishCategory, PaymentMethod.
- **`app/Events`** — broadcast events: OrderCreated, OrderItemCreated, OrderItemStatusUpdated, TableStatusUpdated, ReservationCreated, ReservationUpdated, InvoiceIssued.
- **`app/Http/Requests`** — validation (Store/Update + AssignTable, ReorderTables, UpdateKitchenItemStatus, etc.).
- **Frontend**: **sidebar** layout (`layouts/sidebar`, `layouts/app`), Blade components (flash, page-header, delete-button, tabs, sidebar-link, dropdown). Vite entries: `app.js`, `dashboard-chart.js`, `shifts-calendar.js`, `reservations-calendar.js`, `tables-sortable.js`.

---

## Data model (overview)

- **User** — first_name, last_name, email, role (enum), phone_number, notes.
- **Room** — name, description, color, sort_order.
- **Table** — table_number, capacity, room_id (nullable), sort_order, status (enum); relations: room, activeAssignment (TableAssignment), orders.
- **TableAssignment** — table_id, user_id, shift_id (active waiter assignment to table).
- **Order** — table_id, status, total_price, ordered_at; OrderItem (menu_item_id, quantity, unit_price, status).
- **Reservation** — table_id, guest_name, guest_phone, reserved_at, party_size, status (enum).
- **Dish** — name, category (enum), price, is_available.
- **MenuItem** — dish_id, name, price (override).
- **Shift** — user_id, date, start_time, end_time, type (enum).
- **Invoice** — order_id, payment_method (enum), tax_id, amount, issued_at.

---

## Requirements

- **PHP** 8.5+
- **Composer** 2.x
- **Node.js** 18+ and **npm** (or yarn) for Vite and assets
- **MySQL** 8.0 or **MariaDB** 10.3+
- **Redis** (optional, for cache/session/queue)
- For **real-time**: WebSocket server (Laravel Reverb) running

---

## Quick start (local)

1. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure database** in `.env`: `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

4. **Migrations and seed**
   ```bash
   php artisan migrate --seed
   ```

5. **Run the app**
   ```bash
   php artisan serve
   npm run dev
   php artisan reverb:start --debug
   ```

(Optional: `php artisan queue:work` if using queues.)

---

## Quick start with Docker Sail

1. **`.env`**
   ```bash
   cp .env.example .env
   ```

2. **Start containers**
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

4. **Real-time** (in a separate terminal):
   ```bash
   ./vendor/bin/sail artisan reverb:start --debug
   ```

App URL: `http://localhost` (port depends on Sail config).

---

## Test accounts

After `php artisan migrate --seed`:

| Role       | Email                    | Password  |
|-----------|---------------------------|-----------|
| Manager   | `manager@restaurant.com`  | `password` |
| Waiter    | `waiter@restaurant.com`   | `password` |
| Waiter    | `waiter2@restaurant.com`  | `password` |
| Chef      | `chef@restaurant.com`     | `password` |
| Bartender | `bartender@restaurant.com`| `password` |
| Host      | `host@restaurant.com`    | `password` |

---

## Tests and code quality

```bash
php artisan test
```

Code style (Laravel Pint):

```bash
./vendor/bin/pint
```

---

## Real-time and broadcast channels

- **Reverb**: `php artisan reverb:start --debug` (or via Sail).
- **Channels** (`routes/channels.php`):
  - `kitchen` — Manager, Chef, Waiter.
  - `dashboard` — Manager, Chef, Waiter, Host.
  - `tables` — Manager, Waiter, Host.
- **Events**: e.g. `OrderItemStatusUpdated` (kitchen/waiter), `TableStatusUpdated` (table grid), `OrderCreated`.

On the frontend, Laravel Echo with the Pusher driver connects to Reverb; private channels are subscribed after login.

---

## Useful Artisan commands

```bash
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
php artisan queue:work
php artisan reverb:start --debug
```

Production asset build: `npm run build` (or `sail npm run build`).

---

## Troubleshooting

- **Tables / kitchen not updating in real time** — Ensure Reverb is running and `.env` has correct `BROADCAST_*` and `REVERB_*` settings. In the browser, check for 403 on `broadcasting/auth` (channel auth in `channels.php`).
- **JS/CSS changes not applied** — Run `npm run dev` (or `sail npm run dev`), or `npm run build` and hard refresh (Ctrl+F5).
- **500 error after migration** — Run `php artisan config:clear` and `php artisan cache:clear`; verify `.env` and permissions on `storage/`, `bootstrap/cache/`.

---

## Roadmap

- [ ] Advanced financial reports (CSV/PDF export, period comparison).
- [ ] Push/email notifications for order and reservation status.
- [ ] Inventory module (ingredient stock, low-stock alerts).
- [ ] E2E tests for critical flows (kitchen ↔ waiter ↔ invoice).
- [ ] Online payment gateway integration.

---
