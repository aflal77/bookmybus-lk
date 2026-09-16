# Bus Reservation System (BookMyBus LK)

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-InnoDB-4479A1?logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## Overview

**BookMyBus LK** is an intercity bus ticket reservation and transport operations management web application built for Sri Lankan express transit corridors.

The platform provides an end-to-end digital travel experience: passengers can search routes, view real-time seat availability, reserve seats on an interactive coach map, and receive digital QR-coded boarding passes. Simultaneously, back-office portals provide fleet management, trip dispatching, boarding validation, and financial reporting for administrators, station staff, and drivers.

---

## Features

All features listed below are fully implemented in the codebase:

### 1. Passenger Portal
* **Route Search & Filtering:** Search by origin, destination, travel date, and bus tier (Super Luxury, Luxury, Semi Luxury).
* **Interactive 2×2 Seat Selection:** Visual bus cabin layout displaying color-coded seat availability (Available, Selected, Booked).
* **Concurrency Protection:** ACID database transaction locking (`SELECT ... FOR UPDATE`) prevents double-booking if multiple users checkout the same seat concurrently.
* **Instant Digital QR Boarding Pass:** Generates printable e-tickets with booking reference codes (`BMB-YYYY-NNNNN`) and verifiable QR codes.
* **Booking Management (`my_bookings.php`):** View upcoming and past trips, cancel reservations with automated seat release, and write trip reviews.
* **User Authentication & Profile:** Secure registration, login, and password management with BCrypt hashing (`profile.php`).
* **Ticket Verification (`verify_ticket.php`):** Public/conductor ticket verification endpoint by booking reference or QR token.

### 2. Administrator Operations Portal (`/admin`)
* **Executive Dashboard (`admin/index.php`):** Key metrics (Total Users, Active Buses, Today's Bookings, Monthly Revenue) and Chart.js route visualizer.
* **Fleet Management (`admin/buses.php`):** Full CRUD for coaches (registration numbers, operators, seat count, AC, Wi-Fi, USB charging, maintenance dates).
* **Route Management (`admin/routes.php`):** Configure pickup/drop terminals, intermediate stops, distance (km), duration, and fare tables.
* **Trip Scheduling & Dispatch (`admin/schedules.php`):** Dispatch daily trips with status tracking (*Scheduled*, *Boarding*, *On Route*, *Arrived*, *Cancelled*).
* **Master Booking Register (`admin/bookings.php`):** Search by passenger name, phone, or reference code; manage booking statuses.
* **Driver Roster (`admin/drivers.php`):** Track driver licensing, heavy-vehicle experience, and assigned buses.
* **Fleet Maintenance (`admin/maintenance.php`):** Service logs, repair costs, and inspection reminders.
* **Financial Analytics & CSV Export (`admin/reports.php`):** Date-range revenue reports with live `.csv` export.
* **User & Role Management (`admin/users.php`):** Role-based permission controls (Admin, Staff, Driver, Customer) and account status toggles.

### 3. Station Staff Portal (`/staff`)
* **Live Station Departures:** View daily schedules and platform boarding statuses.
* **Passenger Manifest:** Real-time boarding lists and one-click passenger check-in.
* **Ticket Validation:** Quick reference lookup to verify genuine tickets.

### 4. Driver Operations Portal (`/driver`)
* **Assigned Bus Telemetry:** View assigned vehicle health, registration, and seating capacity.
* **Duty Schedule:** View assigned trips and update travel progress.
* **Passenger Headcount:** Passenger manifest for the assigned coach.

### 5. REST API Layer (`/api`)
* `GET /api/routes.php` – Query routes and available seat counts in JSON.
* `GET /api/buses.php` – Retrieve fleet specifications and amenities.
* `GET /api/seats.php?bus_id=1` – Real-time seat layout status map.
* `GET /api/verify_ticket.php?ref=BMB-2026-0001` – Validate ticket authenticity and boarding eligibility.

---

## Technologies Used

* **PHP** (8.0+ recommended) – Procedural & modular server-side backend.
* **MySQL** (InnoDB) – Relational database with foreign keys and row-level locking.
* **HTML5 & CSS3** – Responsive "Sunset Highway" custom design system.
* **JavaScript (ES6+)** – Vanilla JS for interactive seat selection, SVG route animation, and forms.
* **Bootstrap 5 (v5.3.3)** – Grid system and responsive layout foundation.
* **Bootstrap Icons (v1.11.3)** – Icon set.
* **Chart.js** – Interactive admin analytics dashboard charts.
* **Leaflet.js & OpenStreetMap** – Interactive geospatial route mapping (`routes.php`).
* **XAMPP / Apache** – Local web server and runtime environment.

---

## Project Structure

```text
bus-reservation-system/
├── admin/                     # Administrator Portal
│   ├── bookings.php           # Master booking list & status management
│   ├── buses.php              # Fleet CRUD operations
│   ├── drivers.php            # Driver licensing & assignments
│   ├── index.php              # Executive KPI dashboard & analytics
│   ├── maintenance.php        # Vehicle maintenance logs
│   ├── reports.php            # Revenue analytics & CSV export
│   ├── routes.php             # Route & terminal configuration
│   ├── schedules.php          # Trip dispatcher & scheduling
│   └── users.php              # Role-based user administration
├── api/                       # REST API Endpoints (JSON)
│   ├── buses.php              # Fleet endpoint
│   ├── routes.php             # Route query endpoint
│   ├── seats.php              # Live seat status endpoint
│   └── verify_ticket.php      # Ticket validation endpoint
├── driver/                    # Driver Operations Portal
│   └── index.php              # Duty roster & trip status updater
├── includes/                  # Shared Application Components
│   ├── auth.php               # Sessions, RBAC guards, CSRF, helper utilities
│   ├── footer.php             # Shared responsive footer
│   └── header.php             # Shared responsive navbar & flash messages
├── staff/                     # Station Staff Portal
│   └── index.php              # Passenger manifest & boarding validation
├── .gitignore                 # Excludes local configs, logs, OS files
├── about.php                  # Company vision & fleet narrative
├── book.php                   # Interactive 2x2 seat selection page
├── config.example.php         # Safe configuration template for developers
├── confirm.php                # Booking checkout & ACID transaction processor
├── contact.php                # Passenger contact & message submission
├── db.php                     # Database connection handler (loads config)
├── destinations.php           # Sri Lankan destination directory
├── faq.php                    # Passenger help center & FAQs
├── index.php                  # Homepage with hero, search, & popular routes
├── LICENSE                    # MIT License
├── login.php                  # Authentication gateway (multi-role redirect)
├── logout.php                 # Session termination
├── my_bookings.php            # Passenger booking history & cancellation
├── profile.php                # User profile & password management
├── README.md                  # Project documentation
├── register.php               # Passenger account registration
├── routes.php                 # Route directory & Leaflet map
├── schedules.php              # Daily departure schedules
├── schema.sql                 # Complete database schema & seed data
├── style.css                  # Custom design system & UI tokens
├── ticket.php                 # Digital e-ticket boarding pass with QR
└── verify_ticket.php          # Public ticket verification interface
```

---

## Requirements

* **Operating System:** Windows, macOS, or Linux.
* **Local Web Server:** [XAMPP](https://www.apachefriends.org/) (Apache 2.4+, PHP 8.0+, MariaDB/MySQL 10.4+).
* **Web Browser:** Any modern browser (Google Chrome, Mozilla Firefox, Microsoft Edge, Safari).

---

## Installation / Setup Guide

Follow these steps to set up and run the project locally on XAMPP:

### Step 1: Install XAMPP
Download and install XAMPP with **Apache**, **PHP**, and **MySQL** from [apachefriends.org](https://www.apachefriends.org/).

### Step 2: Clone or Place Project Files
Clone this repository into your XAMPP `htdocs` directory:
```bash
cd C:/xampp/htdocs
git clone https://github.com/aflal77/bookmybus-lk.git "bus reservation system"
```
*(Or extract the ZIP file directly into `C:/xampp/htdocs/bus reservation system/`)*.

### Step 3: Start Apache & MySQL
Open the **XAMPP Control Panel** and click **Start** next to both **Apache** and **MySQL**.

### Step 4: Import the Database
1. Open your web browser and navigate to **phpMyAdmin**:
   ```text
   http://localhost/phpmyadmin/
   ```
2. Click the **Import** tab in the top navigation bar.
3. Click **Choose File** and select [`schema.sql`](schema.sql) from the project directory.
4. Click **Import** (or **Go** at the bottom).
   *This automatically creates the `quickseat` database, 13 tables, relationships, and demo seed data.*

### Step 5: Configure Local Database Settings
1. In the project root folder, make a copy of `config.example.php` and name it `config.local.php`:
   ```bash
   cp config.example.php config.local.php
   ```
2. Open `config.local.php` and verify your local database settings (defaults for XAMPP):
   ```php
   return [
       'db_host' => 'localhost',
       'db_user' => 'root',
       'db_pass' => '',
       'db_name' => 'quickseat',
       'db_port' => 3306,
   ];
   ```
   > **Note:** `config.local.php` is listed in `.gitignore` and will never be committed to Git.

### Step 6: Open the Application
Navigate to the following URL in your browser:
```text
http://localhost/bus%20reservation%20system/
```

---

## Database Setup Details

The provided [`schema.sql`](schema.sql) file includes:
* **Table Structures:** `roles`, `users`, `buses`, `routes`, `seats`, `schedules`, `bookings`, `payments`, `drivers`, `maintenance`, `reviews`, `notifications`, `contact_messages`.
* **Foreign Key Constraints:** Cascading deletions and referential integrity between buses, routes, seats, bookings, and payments.
* **Sample Data:** Seed records for 6 coaches, Sri Lankan expressway routes, 180 seats, sample schedules, and demo users.

---

## Configuration

The application uses a decoupled configuration pattern:
* [`config.example.php`](config.example.php) is tracked by Git as a clean template.
* `config.local.php` holds private local/live credentials and is **ignored** by Git.
* [`db.php`](db.php) checks for `config.local.php`, falls back to environment variables (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`), or defaults to standard local XAMPP settings.

---

## Running the Application

| Page | URL Path | Access Level |
| :--- | :--- | :--- |
| **Homepage** | `/index.php` | Public |
| **Route Map** | `/routes.php` | Public |
| **Schedules** | `/schedules.php` | Public |
| **Seat Booking** | `/book.php?route_id=1` | Public / Customer |
| **Ticket Verification** | `/verify_ticket.php` | Public / Conductor |
| **My Bookings** | `/my_bookings.php` | Customer (Login required) |
| **User Profile** | `/profile.php` | Registered users |
| **Admin Dashboard** | `/admin/index.php` | Admin only |
| **Staff Portal** | `/staff/index.php` | Staff / Admin |
| **Driver Portal** | `/driver/index.php` | Driver / Admin |

---

## Security Notes

* **No Hardcoded Secrets:** Private credentials and passwords are not stored in tracked repository files.
* **Password Hashing:** User passwords are encrypted using PHP's native BCrypt algorithm (`password_hash` with `PASSWORD_BCRYPT`).
* **Prepared Statements:** All SQL queries involving dynamic user input utilize parameterized prepared statements (`mysqli_prepare` / `mysqli_stmt_bind_param`) to protect against SQL injection.
* **CSRF Mitigation:** State-modifying POST forms require cryptographic session tokens verified with `hash_equals()`.
* **Output Escaping:** User-supplied strings are escaped via `htmlspecialchars()` to protect against Cross-Site Scripting (XSS).
* **Session Security:** Role-based access controls strictly redirect unauthorized users away from privileged sections.

---

## Development Notes

### Demo Seed Accounts
The demo database created by `schema.sql` contains pre-seeded accounts for testing each user role:

| Role | Email | Password | Access Area |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@bookmybus.lk` | `Admin@123` | `/admin/index.php` |
| **Station Staff** | `staff@bookmybus.lk` | `Staff@123` | `/staff/index.php` |
| **Fleet Driver** | `driver@bookmybus.lk` | `Driver@123` | `/driver/index.php` |
| **Passenger** | `customer@bookmybus.lk` | `Customer@123` | `/my_bookings.php` |

*(All sample personal names, registration plates, and contact details in seed records are fictitious).*

---

## License

This project is licensed under the [MIT License](LICENSE).
