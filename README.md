# 📘 PRODUCT REQUIREMENTS DOCUMENT (PRD)

# WeTu Integrated Procurement & Budget Control System

**Platform:** Laravel + Blade + MySQL  
**Version:** 1.0  
**Architecture:** Monolithic Web Application  
**UI Theme:** Minimal, Modern, Black & White

---

## 1️⃣ Executive Summary

WeTu operates across multiple hubs and manages:

* Donor-funded projects
* Internal departmental operational budgets

Current procurement and budget tracking processes are fragmented and manually controlled.

This system will provide:

* Project-based procurement control
* Department-based internal budget control
* Tiered approval workflows
* Asset & stock traceability
* Full audit logging
* Donor compliance enforcement

The system will function as:

> A Financial Control & Governance Platform for WeTu

---

## 🚀 Quick Start: Creating a Procurement Workspace

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Create Project │ ──▶ │  Upload Budget  │ ──▶ │  Confirm Lines  │ ──▶ │ Workspace Ready │
│  (or Dept)      │     │  (CSV/Manual)   │     │  & Activate     │     │ (Start Procure) │
└─────────────────┘     └─────────────────┘     └─────────────────┘     └─────────────────┘
```

**Minimum steps to start procuring:**

1. **Create a Project** or **Department Budget** with basic details
2. **Upload Budget** via CSV or enter budget lines manually
3. **Review & Confirm** budget line allocations
4. **Activate** → System creates your **Procurement Workspace**

Once activated, you can immediately:
- Create requisitions against budget lines
- Track commitments and spending
- Manage the full procurement cycle

---

## 🛠️ Installation & Starting the Application

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL 8.0+

### Installation Steps

```bash
# 1. Clone the repository
git clone <repository-url>
cd project-ark

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies
npm install

# 4. Copy environment file and configure
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Configure your database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_ark
DB_USERNAME=root
DB_PASSWORD=your_password

# 7. Create the database
mysql -u root -p -e "CREATE DATABASE project_ark;"

# 8. Run migrations and seed the database
php artisan migrate --seed

# 9. Build frontend assets
npm run build
```

### Starting the Application

```bash
# Start the Laravel development server
php artisan serve

# In a separate terminal, start Vite for hot-reloading (development)
npm run dev
```

The application will be available at: **http://localhost:8000**

### Running Tests

```bash
# Run all tests
php artisan test

# Or using Pest directly
./vendor/bin/pest
```

---

## 2️⃣ Business Objectives

1. Enforce strict budget discipline
2. Prevent unauthorized spending
3. Provide real-time visibility into commitments and expenditures
4. Separate donor funds from internal operational funds
5. Improve audit readiness
6. Standardize procurement workflows across all hubs

---

## 3️⃣ Core System Concept

### Dual Budget Context Architecture

Every transaction must belong to one of:

* Project Budget Context (Donor Funded)
* Department Budget Context (Internal Budget)

These contexts must remain isolated.

No mixing of funds is permitted.

### Workspace Concept

A **Workspace** is the operational environment created when a budget is activated:

| Budget Type | Creates | Contains |
|-------------|---------|----------|
| Project Budget | Project Workspace | Budget Tracker, Requisitions, POs, Assets, Reports |
| Department Budget | Department Workspace | Budget Tracker, Requisitions, POs, Assets, Reports |

**Workspace Lifecycle:**

```
Draft → Budget Uploaded → Confirmed → Activated (Workspace Created) → Active → Closed
```

**Key Rules:**
- One activated Project = One Project Workspace
- One activated Department Budget (per fiscal year) = One Department Workspace
- All procurement actions occur within a workspace
- Workspace cannot be deleted while transactions exist

---

## 4️⃣ User Roles

### System Administrator

* Manages users, roles, hubs, policies

### Project Manager

* Owns project budget
* Approves requisitions

### Department Head

* Owns department budget
* Approves internal spending

### Finance Officer

* Validates budget compliance
* Confirms expenditures

### Procurement Officer

* Manages RFQs and POs

### Hub Manager

* Confirms receipt of goods
* Asset custody

### Executive Approver

* Approves high-value transactions

---

## 5️⃣ Functional Requirements

---

### MODULE 1: Foundation Management

#### Features

* Manage Hubs
* Manage Departments
* Role-based access control
* Approval matrix configuration

#### Requirements

* Each user belongs to a department
* Users may be assigned to one primary hub
* Roles determine system permissions

---

### MODULE 2: Project Management

#### Create Project Workspace

Steps:

1. **Enter project details** (name, donor, dates, manager)
2. **Upload or manually input budget** (CSV upload or line-by-line entry)
3. **Confirm budget lines** (review allocations, make adjustments)
4. **Activate project** → **Procurement Workspace is created**

> Upon activation, the Project Workspace becomes available with:
> - Budget Tracker dashboard
> - Requisition creation
> - Full procurement workflow access

#### Project Fields

* Project Name
* Donor
* Department
* Assigned Hubs
* Start/End Date
* Project Manager

---

### MODULE 3: Department Budget Management

#### Create Department Workspace

Steps:

1. **Select department** (from Foundation setup)
2. **Define fiscal year** (e.g., FY2026)
3. **Upload budget lines** (CSV upload or line-by-line entry)
4. **Confirm & activate** → **Procurement Workspace is created**

> Upon activation, the Department Workspace becomes available with:
> - Budget Tracker dashboard
> - Requisition creation for internal spending
> - Full procurement workflow access

#### Budget Line Fields

* Category
* Budget Line Name
* Description
* Allocated Amount

---

### MODULE 4: Budget Engine

Each budget line tracks:

* Allocated
* Committed
* Spent
* Remaining

#### System Rules

* Commitment occurs upon requisition/PO approval
* Expenditure recorded upon receipt confirmation
* Cannot exceed remaining balance
* Cannot close budget with open commitments

---

### MODULE 5: Procurement Workflow

#### Requisition

User must:

* Select budget context (Project OR Department)
* Select budget line
* Enter items and cost estimate

Status Flow:
Draft → Submitted → Approved → Procurement

---

#### RFQ (Optional for Department)

* Create from approved requisition
* Select suppliers
* Upload quotes

---

#### Quote Analysis

* Compare supplier quotations
* Select recommended vendor
* Attach evaluation notes

---

#### Purchase Order

Generated from approved quote.

Fields:

* Supplier
* Amount
* Delivery terms
* Linked budget line

Status:
Draft → Approved → Issued → Delivered

---

#### Goods Receipt

Upon delivery:

* Confirm quantities
* Upload delivery note
* Assign hub/store
* Create stock batch or asset

System:

* Convert committed → spent
* Update remaining budget

---

### MODULE 6: Asset & Inventory Management

#### Assets

* Asset tag generation
* Assign to hub or user
* Transfer workflow
* Maintenance logging
* Disposal tracking

#### Stock

* Stock batch creation
* Stock issue to department/project
* Low-stock alerts

---

### MODULE 7: Budget Tracker

Within each workspace:

Display:

| Budget Line | Allocated | Committed | Spent | Remaining |
|-------------|-----------|-----------|-------|-----------|

Include:

* Progress bars (monochrome)
* Real-time updates

---

### MODULE 8: Reporting & Dashboard

#### Executive Dashboard

Displays:

* Total donor budgets
* Total internal budgets
* Total commitments
* Total expenditures
* Budget utilization rates

Filters:

* By context type
* By hub
* By department
* By fiscal year

---

### MODULE 9: Approval Engine

Two separate approval matrices:

1. Project Approval Matrix
2. Department Approval Matrix

Approval based on:

* Amount thresholds
* Role hierarchy
* Context type

Approval history must be permanently logged.

---

### MODULE 10: Audit & Governance

System must log:

* Who created request
* Who approved
* Changes made
* Date/time stamps

Audit logs must be immutable.

---

## 6️⃣ Non-Functional Requirements

### Performance

* Page load under 2 seconds
* Optimized database queries

### Security

* Role-based middleware
* CSRF protection
* Encrypted passwords
* Secure file uploads

### Availability

* Daily backups
* Error logging
* Graceful error handling

---

## 7️⃣ UI & Design Requirements

### Theme

Black & White Minimal

No bright colors.

### Layout

* Clean top navigation
* Sidebar for workspaces
* Soft borders
* Generous whitespace

### Buttons

Primary: Solid Black  
Secondary: Outline Black  
Danger: Minimal red accent only

### Animations

Subtle transitions only.

System should feel:

* Professional
* Institutional
* Calm
* Structured

---

## 8️⃣ Acceptance Criteria

The system is considered complete when:

* All procurement tied to a budget context
* Overspending is impossible
* Approval workflow enforced
* Assets traceable to source
* Department and Project budgets fully separated
* Reports reconcile with budget totals

---

## 9️⃣ Implementation Plan

### Sprint 1: Foundation & Layout (Current)

- [x] **1.1** Database migrations (users, hubs, departments, roles)
- [x] **1.2** Models & relationships (User, Hub, Department, Role)
- [x] **1.3** Authentication (Laravel Breeze/Fortify)
- [x] **1.4** Master layout (app.blade.php) - Black & White theme
- [x] **1.5** Main dashboard layout (Procure home)
- [x] **1.6** Seeder for initial data (roles, sample hub/dept)

### Sprint 2: Project & Department Budgets

- [x] **2.1** Project model & migrations (projects, budget_lines)
- [x] **2.2** Department Budget model & migrations
- [x] **2.3** Create Project form (multi-step wizard)
- [x] **2.4** Budget upload (CSV import + manual entry)
- [x] **2.5** Budget confirmation & activation flow
- [x] **2.6** Project cards on dashboard (Active/Draft/Closed states)
- [x] **2.7** Department Budget cards on dashboard

### Sprint 3: Project Workspace (Canvas)

- [x] **3.1** Workspace layout (sidebar navigation)
- [x] **3.2** Overview page (stats cards, recent activity)
- [x] **3.3** Budget Tracker page (budget lines table with progress)
- [x] **3.4** Workspace routing (/projects/{project}/...)

### Sprint 4: Requisitions & Approvals

- [x] **4.1** Requisition model & migrations
- [x] **4.2** Create requisition (select budget line, items, estimate)
- [x] **4.3** Requisition listing & detail pages
- [x] **4.4** Approval workflow (status transitions)
- [x] **4.5** Budget commitment on approval

### Sprint 5: RFQ & Quote Analysis

- [x] **5.1** RFQ model & migrations
- [x] **5.2** Supplier model & migrations
- [x] **5.3** Create RFQ from requisition
- [x] **5.4** Quote submission & comparison
- [x] **5.5** Quote analysis & vendor selection

### Sprint 6: Purchase Orders & Receipts

- [x] **6.1** Purchase Order model & migrations
- [x] **6.2** Generate PO from approved quote
- [x] **6.3** PO approval workflow
- [x] **6.4** Goods Receipt model & migrations
- [x] **6.5** Receipt confirmation (commitment → spent)

### Sprint 7: Assets & Inventory

- [x] **7.1** Asset model & migrations
- [x] **7.2** Stock model & migrations
- [x] **7.3** Asset registration from receipt
- [x] **7.4** Stock batch creation
- [x] **7.5** Asset/Stock listing in workspace

### Sprint 8: Reporting & Polish

- [x] **8.1** Executive dashboard stats
- [x] **8.2** Budget utilization reports
- [x] **8.3** Audit trail logging
- [x] **8.4** Export functionality (PDF/Excel)
- [x] **8.5** UI polish & responsive design

---

### Current Progress

| Sprint | Status | Completion |
|--------|--------|------------|
| Sprint 1 | ✅ Complete | 100% |
| Sprint 2 | ✅ Complete | 100% |
| Sprint 3 | ✅ Complete | 100% |
| Sprint 4 | ✅ Complete | 100% |
| Sprint 5 | ✅ Complete | 100% |
| Sprint 6 | ✅ Complete | 100% |
| Sprint 7 | ✅ Complete | 100% |
| Sprint 8 | ✅ Complete | 100% |

---

## 🔟 Long-Term Scalability

Future enhancements may include:

* API integrations
* Accounting system sync
* Donor-specific reporting modules
* Multi-entity expansion

---

## Final Vision

This system will serve as:

> The Financial Control Backbone of WeTu

It will enforce discipline, transparency, accountability, and institutional maturity.
#   p r o j e c t - a r k 
 
 