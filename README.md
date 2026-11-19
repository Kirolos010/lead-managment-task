# 🚀 Lead Assignment & Follow-up System




\


A CRM module built with **Laravel 12** and **Filament 5** for managing and distributing sales leads efficiently.

The system automatically assigns new leads to active sales agents using a **round-robin algorithm**, respects each agent's daily assignment capacity, tracks all status changes, and automatically escalates leads that remain in `New` status for 30 minutes.

---

## 📌 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [System Roles](#-system-roles)
- [Installation](#-installation)
- [Running the Application](#-running-the-application)
- [Demo Accounts](#-demo-accounts)
- [Running Tests](#-running-tests)
- [Architecture & Design Decisions](#-architecture--design-decisions)
- [Performance & Optimization](#-performance--optimization)

---

## ✨ Features

- **Automatic Lead Assignment:** Round-robin distribution among active agents.
- **Daily Capacity Management:** Prevents agents from exceeding their daily assignment limits.
- **Bulk Assignment:** Assign multiple leads directly from the Filament leads table.
- **Status Audit Log:** Tracks lead status changes without third-party packages.
- **Automatic Escalation:** Escalates leads that remain in `New` status for 30 minutes.
- **Queue-Based Processing:** Delayed jobs with retry-safe and idempotent execution.
- **Role-Based Access Control:** Admin, Sales Manager, and Sales Agent permissions.
- **Advanced Filters:**
  - My Leads
  - Unassigned Leads
  - Overdue Follow-ups
  - High Priority Leads

---

## 🛠 Tech Stack

| Technology    | Version         | Purpose                |
| ------------- | --------------- | ---------------------- |
| PHP           | 8.2+            | Backend Language       |
| Laravel       | 12.x            | Application Framework  |
| Filament      | 5.x             | Admin Panel & UI       |
| MySQL         | 8.0+            | Database & Row Locking |
| Laravel Queue | Database Driver | Background Jobs        |
| PHPUnit       | 11.x            | Automated Testing      |
| Vite          | -               | Frontend Assets        |
| Node.js       | -               | Frontend Tooling       |

---

## 👥 System Roles

The system implements three roles with server-side authorization.

| Role          | Permissions                                    |
| ------------- | ---------------------------------------------- |
| Admin         | Full access to all leads                       |
| Sales Manager | Access to leads assigned to their team members |
| Sales Agent   | Access to their own assigned leads only        |

**Security is enforced through:**

- Laravel Policies.
- Query scopes using `visibleTo()`.
- Service-level authorization checks.
- Unauthorized record URLs return `404`.

---

## ⚙️ Installation

### Requirements

Make sure you have the following installed:

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL 8.0+

### 1. Clone the Repository

```bash
git clone <repo-url>
cd <repo-folder>
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Configure Environment

```bash
cp .env.example .env
```

For Windows:

```bash
copy .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 4. Configure Database

Create a MySQL database and update your `.env` file:

```env
DB_CONNECTION=mysql
DB_DATABASE=nassar
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
```

### 5. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

Optional: Generate demo data.

```bash
php artisan db:seed --class=DemoDataSeeder
```

---

## ▶️ Running the Application

Open separate terminals and run:

**Terminal 1 — Laravel Server**

```bash
php artisan serve
```

**Terminal 2 — Queue Worker**

```bash
php artisan queue:work
```

**Terminal 3 — Vite Development Server**

```bash
npm run dev
```

### Admin Panel

Open:

[http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

> [!IMPORTANT]
> The queue worker must remain running to process delayed `EscalateLeadJob` jobs and manager notifications.
>
> Without an active queue worker, automatic lead escalation will not be processed.

### Failed Jobs Management

List failed jobs:

```bash
php artisan queue:failed
```

Retry all failed jobs:

```bash
php artisan queue:retry all
```

---

## 🔑 Demo Accounts

Use the following credentials to explore the application:

| Role    | Email                                          | Password   |
| ------- | ---------------------------------------------- | ---------- |
| Admin   | [admin@gmail.com](mailto\:admin@gmail.com)     | `12345678` |
| Manager | [manager1@demo.com](mailto\:manager1@demo.com) | `12345678` |
| Agent   | [agent01@demo.com](mailto\:agent01@demo.com)   | `12345678` |

---

## 🧪 Running Tests

**Important:** Tests require MySQL because the application relies on row-level locking using `SELECT ... FOR UPDATE`, which SQLite does not fully support.

Use a separate testing database because `RefreshDatabase` resets the database during testing.

### Configure `.env.testing`

```env
APP_ENV=testing
DB_CONNECTION=mysql
DB_DATABASE=nassar_testing
```

### Run All Tests

```bash
php artisan test
```

### Run Specific Test

```bash
php artisan test --filter=LeadAssignmentTest
php artisan test --filter=LeadBulkAssignmentTest
php artisan test --filter=LeadEscalationTest
php artisan test --filter=LeadAuthorizationTest
php artisan test --filter=LeadConcurrencyTest
```

### Test Coverage

| Test File                | Coverage                                                                    |
| ------------------------ | --------------------------------------------------------------------------- |
| `LeadAssignmentTest`     | Round-robin assignment, inactive agents, daily capacity                     |
| `LeadBulkAssignmentTest` | Bulk assignment, capacity restrictions, manager team permissions            |
| `LeadEscalationTest`     | Job dispatching, status re-checking, duplicate prevention                   |
| `LeadAuthorizationTest`  | Agent access restrictions                                                   |
| `LeadConcurrencyTest`    | Capacity protection, duplicate assignment prevention, row-lock verification |

> [!NOTE]
> Concurrency tests execute within a single PHP process. They verify the assignment logic and the presence of `FOR UPDATE` locks, rather than simulating truly parallel database transactions.

---

## 🏗 Architecture & Design Decisions

### 1. Round-Robin Assignment

The `LeadService` handles lead assignment inside a database transaction.

Workflow:

1. Lock the lead record.
2. Retrieve eligible active agents in a fixed order.
3. Lock agent records.
4. Check each agent's daily assignment capacity.
5. Assign the lead to the first eligible agent.
6. Keep the lead unassigned if no agent has available capacity.

**Important:** Assignment does not automatically change the lead's status.

### 2. Concurrency Protection

Database transactions and `lockForUpdate()` are used to prevent race conditions.

Both lead and agent records are locked during assignment.

When multiple requests attempt to assign leads simultaneously, the database serializes conflicting operations so subsequent transactions read the updated state.

### 3. Authorization & Data Visibility

Access control is enforced at multiple levels:

- Policies for authorization.
- `visibleTo()` query scopes for record visibility.
- Service-level permission checks.
- Unauthorized record URLs return `404`.

### 4. Queue Idempotency & Escalation

The escalation job is designed to be safe for retries.

Workflow:

1. Lock the lead record.
2. Re-check that its status is still `New`.
3. Create the escalation record using `firstOrCreate()`.
4. Enforce uniqueness through a unique `lead_id` constraint.
5. Notify the manager only when a new escalation record is created.

This prevents duplicate escalations and notifications when jobs are retried.

### 5. Business Rules

- Only active agents receive leads.
- Daily assignment capacity is always respected.
- Assignment does not change lead status.
- Leads with no eligible agent remain unassigned.
- Overdue leads are those whose follow-up time has passed while the lead is still open.
- Unassigned leads are not automatically retried; administrators can review and bulk assign them.

---

## ⚡ Performance & Optimization

The application is designed with scalability in mind, targeting efficient handling of up to **500,000 leads**.

### Database Indexing

The following composite indexes are used:

| Index                         | Purpose                                     |
| ----------------------------- | ------------------------------------------- |
| `(assigned_to, assigned_at)`  | Agent assignment and daily capacity queries |
| `(assigned_to, status)`       | Filtering leads by agent and status         |
| `(status, next_follow_up_at)` | Overdue follow-up queries                   |
| `(priority, status)`          | Priority-based filtering                    |

### Query Optimization

- Eager loading of `assignedUser`.
- Server-side pagination through Filament.
- Search restricted to lead name and phone.
- Indexed filtering for frequently accessed lead data.


---


---
