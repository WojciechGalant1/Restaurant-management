# Restaurant Management System (Modern Edition)

A high-performance, secure, and modern restaurant management platform built with **Laravel 10**, **Tailwind CSS**, and **Alpine.js**. This project represents a complete refactor from a legacy codebase into a modern, English-first monolithic architecture.

## Key Features

- **Dynamic Dashboard**: A central hub providing an overview of restaurant operations and quick access to all modules.
- **Order Management**: Real-time tracking of orders, from entry to kitchen preparation and final delivery.
- **Interactive Tables**: Manage restaurant layout and track table availability in real-time.
- **Reservation System**: Comprehensive booking management to handle customer schedules efficiently.
- **Unified Menu & Dishes**: A two-tier system for managing base dish definitions and their specific pricing or availability as menu items.
- **Staff & Shifts**: Full staff management with role-based schedules and shift tracking.
- **Invoicing**: Generates financial records and handles order settlements with multiple payment methods.

## Security & Authorization

The application implements a robust **Role-Based Access Control (RBAC)** system using Laravel Policies:
- **Managers**: Full access to staff management, financial records (invoices), and configuration.
- **Waiters**: Access to orders, tables, and reservations.
- **Chefs**: Specialized view for kitchen orders and meal preparation.

## Tech Stack

- **Backend**: Laravel 10 (PHP 8.2+)
- **Frontend**: Blade Components + Tailwind CSS
- **Interactivity**: Alpine.js (for dynamic forms and UI states)
- **Icons**: Blade Heroicons
- **Auth**: Laravel Breeze
- **Database**: MySQL / MariaDB

## Project Structure

- `app/Models/`: Core domain models (Order, Table, User, Shift, etc.)
- `app/Http/Controllers/`: Resourceful controllers handling business flow.
- `app/Services/`: Business logic extraction (e.g., `OrderService`).
- `app/Policies/`: Security layer for model-level authorization.
- `resources/views/`: Modern, responsive Blade views organized by module.
- `database/migrations/`: Structured database schema definitions.

## Getting Started

1. **Clone & Install**:
   ```bash
   composer install
   npm install
   ```
2. **Configure Environment**:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```
3. **Database Setup**:
   ```bash
   php artisan migrate --seed
   ```
4. **Run Locally**:
   ```bash
   php artisan serve
   npm run dev
   ```

## Performance Notes

The application uses **Eager Loading** to prevent N+1 query issues and is optimized for fast response times. For development, **Laravel Debugbar** is integrated to monitor query performance and application state.

---
*Maintained as a modern, English-standard professional codebase.*
