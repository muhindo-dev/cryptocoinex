# ONYX Legal — Implementation Plan

**System:** ONYX Legal  
**Company:** ONYX  
**Framework:** Laravel 12 (PHP 8.2.20 via MAMP)  
**Database:** `onyx`  
**Local URL:** http://localhost:8888/onyx  
**Primary Color:** Coffee Brown (`#5D3A1A`)

---

## Module Overview

| Module | Description |
|---|---|
| Auth & Users | 3-role system: Admin, Officer, Frontdesk |
| Clients | Client registry with contact & ID details |
| Cases | Core legal case management |
| Documents | Document vault — attachable to cases |
| Finance | Transactions, Accounts, Financial Periods |
| Dashboard | Role-specific KPI dashboards |
| Reports | Aggregated stats, case scores, finance summaries |

---

## Database Schema

### `users` (extended)
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| email | varchar unique | |
| password | varchar | |
| role | enum(admin,officer,frontdesk) | default: officer |
| phone | varchar nullable | |
| bio | text nullable | |
| is_active | boolean | default: true |
| is_admin | boolean | legacy compat |
| remember_token | varchar | |
| timestamps | | |

### `clients`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| client_number | varchar unique | CL-0001 |
| first_name | varchar | |
| last_name | varchar | |
| email | varchar nullable | |
| phone | varchar | |
| phone_alt | varchar nullable | |
| gender | enum(male,female,other) | |
| dob | date nullable | |
| id_type | enum(national_id,passport,driving_permit,refugee_id,other) | |
| id_number | varchar nullable | |
| address | text | |
| district | varchar nullable | |
| occupation | varchar nullable | |
| company | varchar nullable | |
| notes | text nullable | |
| created_by | bigint FK users | |
| timestamps | | |

### `legal_cases`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| case_number | varchar unique | LC-0001 |
| title | varchar | |
| description | text | |
| category | enum | See categories below |
| status | enum(pending,active,ongoing,closed,archived) | |
| stage | enum | See stages below |
| priority | enum(low,medium,high,urgent) | default: medium |
| client_id | bigint FK clients | |
| main_officer_id | bigint FK users nullable | |
| filing_date | date | |
| closed_date | date nullable | |
| is_in_court | boolean | default: false |
| court_name | varchar nullable | |
| court_division | varchar nullable | |
| court_case_number | varchar nullable | |
| judge_name | varchar nullable | |
| next_hearing_date | date nullable | |
| is_at_police | boolean | default: false |
| police_station | varchar nullable | |
| police_ref_number | varchar nullable | |
| investigating_officer | varchar nullable | |
| score | tinyint nullable | +1 win, 0 neutral, -1 lost |
| closing_remarks | text nullable | |
| created_by | bigint FK users | |
| timestamps | | |

**Case Categories (Uganda law context):**
- `civil_litigation` — Civil Litigation
- `criminal_defense` — Criminal Defence
- `family_law` — Family & Matrimonial
- `land_property` — Land & Property
- `commercial_corporate` — Commercial & Corporate
- `employment_labour` — Employment & Labour
- `human_rights` — Human Rights
- `constitutional` — Constitutional Law
- `succession_probate` — Succession & Probate
- `debt_recovery` — Debt Recovery
- `immigration` — Immigration & Citizenship
- `other` — Other

**Case Stages:**
- `intake` — Initial Intake
- `investigation` — Investigation & Research
- `pre_trial` — Pre-Trial / Filing
- `mediation` — Mediation / Negotiation
- `trial` — Active Trial
- `appeal` — Appeal
- `settlement` — Settlement
- `enforcement` — Enforcement of Orders
- `closed` — Closed

### `case_officers` (pivot)
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| case_id | bigint FK legal_cases | |
| user_id | bigint FK users | |
| role | enum(main,team) | |
| timestamps | | |

### `case_notes`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| case_id | bigint FK legal_cases | |
| user_id | bigint FK users | |
| note | text | |
| is_private | boolean | default: false |
| timestamps | | |

### `documents`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| doc_number | varchar unique | DOC-0001 |
| title | varchar | |
| category | enum | See doc categories below |
| case_id | bigint FK nullable | optional |
| client_id | bigint FK nullable | optional |
| file_path | varchar | stored path |
| file_name | varchar | original filename |
| file_size | bigint | bytes |
| mime_type | varchar | |
| description | text nullable | |
| is_confidential | boolean | default: false |
| uploaded_by | bigint FK users | |
| timestamps | | |

**Document Categories:**
- `notice_to_sue` — Notice to Sue
- `court_order` — Court Order / Ruling
- `affidavit` — Affidavit
- `power_of_attorney` — Power of Attorney
- `contract_agreement` — Contract / Agreement
- `evidence` — Evidence / Exhibit
- `police_report` — Police Report (OB)
- `correspondence` — Correspondence / Letters
- `legal_opinion` — Legal Opinion / Advice
- `judgment` — Judgment / Decree
- `land_title` — Land Title / Certificate
- `company_docs` — Company / Business Docs
- `id_documents` — ID Documents
- `summons` — Summons / Court Process
- `pleadings` — Pleadings / Submissions
- `other` — Other

### `financial_periods`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar | e.g. "Q1 2026" |
| start_date | date | |
| end_date | date | |
| is_active | boolean | default: false |
| description | text nullable | |
| created_by | bigint FK users | |
| timestamps | | |

### `accounts`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| type | enum(bank,cash,mobile_money) | |
| bank_name | varchar nullable | |
| account_number | varchar nullable | |
| branch | varchar nullable | |
| opening_balance | decimal(15,2) | default: 0 |
| description | text nullable | |
| is_active | boolean | default: true |
| created_by | bigint FK users | |
| timestamps | | |

### `transactions`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| transaction_number | varchar unique | TXN-0001 |
| receipt_number | varchar nullable unique | RCP-0001 (for income) |
| type | enum(income,expense) | |
| amount | decimal(15,2) | |
| description | varchar | |
| details | text nullable | |
| account_id | bigint FK accounts | |
| case_id | bigint FK nullable | |
| client_id | bigint FK nullable | |
| financial_period_id | bigint FK nullable | |
| payment_method | enum(cash,bank_transfer,cheque,mobile_money) | |
| reference_number | varchar nullable | |
| transaction_date | date | |
| approved_by | bigint FK nullable | |
| created_by | bigint FK users | |
| timestamps | | |

---

## Implementation Tasks

### Phase 1: Setup & Branding
- [x] Create this plan document
- [ ] Fix `.env` (APP_NAME="ONYX Legal", APP_URL, DB_DATABASE=onyx)
- [ ] Update `config/app.php` app name
- [ ] Redesign `admin.css` — coffee brown palette
- [ ] Update admin layout blade — new sidebar nav for law firm modules
- [ ] Update admin login page

### Phase 2: Database
- [ ] Migration: update users table (role, phone, bio, is_active)
- [ ] Migration: create clients table
- [ ] Migration: create legal_cases table
- [ ] Migration: create case_officers table
- [ ] Migration: create case_notes table
- [ ] Migration: create documents table
- [ ] Migration: create financial_periods table
- [ ] Migration: create accounts table
- [ ] Migration: create transactions table
- [ ] Run all migrations

### Phase 3: Models
- [ ] Update User model (role helpers: isAdmin, isOfficer, isFrontdesk)
- [ ] Create Client model
- [ ] Create LegalCase model
- [ ] Create CaseOfficer model
- [ ] Create CaseNote model
- [ ] Create Document model
- [ ] Create FinancialPeriod model
- [ ] Create Account model
- [ ] Create Transaction model

### Phase 4: Auth & Middleware
- [ ] Update IsAdmin middleware for multi-role (admin|officer|frontdesk)
- [ ] Create RoleMiddleware (generic role check)
- [ ] Update bootstrap/app.php

### Phase 5: Routes
- [ ] Rewrite routes/web.php — remove old edu routes, add ONYX Legal routes
- [ ] Admin routes: dashboard, clients, cases, documents, finance, users, reports

### Phase 6: Controllers
- [ ] Update DashboardController (role-based stats)
- [ ] Create Admin\ClientController
- [ ] Create Admin\LegalCaseController (with notes sub-resource)
- [ ] Create Admin\DocumentController
- [ ] Create Admin\AccountController
- [ ] Create Admin\FinancialPeriodController
- [ ] Create Admin\TransactionController
- [ ] Create Admin\UserController (user management)
- [ ] Create Admin\ReportController (updated)

### Phase 7: Views
- [ ] Admin layout — updated sidebar (Cases, Clients, Documents, Finance sections)
- [ ] Dashboard — role-based KPI cards + recent activity
- [ ] Clients: index, create, edit, show
- [ ] Cases: index, create, edit, show (with notes tab, officers tab)
- [ ] Documents: index, create, show
- [ ] Finance: accounts (index, create, edit), periods (index, create, edit), transactions (index, create, show)
- [ ] Users: index, create, edit, show
- [ ] Reports: overview, case stats, finance summary

### Phase 8: PDF
- [ ] Install barryvdh/laravel-dompdf
- [ ] Create receipt PDF template
- [ ] Add PDF download route for transactions

### Phase 9: Seeder
- [ ] Create OnyxAdminSeeder (admin, officer, frontdesk demo users)
- [ ] Create demo data seeder (sample clients, cases, transactions)
- [ ] Run migrations and seed

---

## Color Palette

| Token | Hex | Usage |
|---|---|---|
| Primary | `#5D3A1A` | Main actions, sidebar active, buttons |
| Primary Dark | `#4A2D13` | Hover states |
| Primary Light | `#7A4E2D` | Secondary elements |
| Accent | `#C4956A` | Highlights, badges |
| Cream | `#F9F5F1` | Body background |
| Sidebar BG | `#1C120A` | Admin sidebar |
| Text | `#1A0F07` | Primary text |
| Muted | `#7A6555` | Secondary text |
| Border | `#E8DDD4` | Dividers |

---

## User Roles & Permissions

| Feature | Admin | Officer | Frontdesk |
|---|---|---|---|
| Dashboard | Full KPIs | Own cases summary | Intake summary |
| Clients | Full CRUD | View + own | Create + view |
| Cases | Full CRUD | Own cases only | Create + assign |
| Case Notes | All | Own cases | Own cases |
| Documents | Full | Own cases | Upload |
| Transactions | Full | View own | Create income |
| Accounts | Full CRUD | View | — |
| Reports | Full | Own | — |
| Users | Full CRUD | — | — |
| Settings | Full | — | — |

---

*Last updated: 2026-05-30*
