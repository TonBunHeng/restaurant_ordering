# Royal Khmer Kitchen — Restaurant Ordering & Table Reservations

A complete **Laravel Full-Stack Restaurant Ordering and Table Reservations System** built with **Laravel 12, Blade Templates, Eloquent ORM, Session Authentication & Cart, and MySQL**.

---

## 🍽️ System Overview & Features

- **Unified Laravel Monolith Architecture**:
  - `backend/`: Complete full-stack application (Blade templates, Web controllers, Eloquent models, Web & API routes, Session auth and shopping cart).
- **Restaurant Menu & Catalog**:
  - Categorized browsing (Khmer Specialties, Steaks & Grills, Gourmet Burgers, Artisan Pizzas, Salads, Desserts, Beverages).
  - Search by dish keyword, dietary filters (*Vegetarian*, *Spicy*, *Chef Specials*), and individual dish detail pages.
- **Session-Based Cart & Checkout**:
  - Add, update quantities, remove items, and enter special cooking instructions.
  - Server recalculates prices directly from the database within a `DB::transaction()` to ensure price integrity.
  - Generates itemized order receipts with live status progression (*Pending* → *Confirmed* → *Preparing* → *Ready* → *Completed*).
  - Protected against IDOR: Customers can only view their own order receipts.
- **Dining Table Reservations**:
  - Interactive table picker with date, time slot, and guest count selector.
  - Enforces table capacity validation and strict **double-booking prevention** via atomic database locking (`lockForUpdate()`).
  - Customers can view booking history and self-cancel upcoming reservations.
- **Staff & Admin Management Portal (`/admin`)**:
  - Operations Dashboard with real-time stats (today's orders, revenue, table bookings, available dining tables, and pending actions).
  - Dining Tables CRUD (capacities, locations, statuses).
  - Food Dishes and Menu Categories CRUD with safe deletion protection.
  - Order and kitchen queue management (live status updates).
  - Table booking approvals and reassignments.
  - Super Admin user governance and staff role management.

---

## 🚀 Running the Application

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Visit the application at: **`http://127.0.0.1:8000`**

---

## 🔑 Demo Accounts

| Role | Email | Password | Access |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@aitourism.kh` | `password123` | Full administrative control (`/admin`) |
| **Staff Chef** | `staff@aitourism.kh` | `password123` | Kitchen orders, menu items, table reservations (`/admin`) |
| **Customer** | `traveler@example.com` | `password123` | Ordering, cart, checkout, reservations, profile |

---

## 🧪 Automated Testing

```bash
cd backend
php artisan test
```

- **31 Feature & Unit Tests Passed (187 assertions, 100% green)**