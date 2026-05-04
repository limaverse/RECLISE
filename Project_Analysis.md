# RecLise — Project Analysis Report
> **Platform:** ELISSA Support Platform — MESRS (Ministère de l'Enseignement Supérieur et de la Recherche Scientifique, Tunisia)
> **Project Type:** Web-based Support Ticket & Correspondence Management System
> **Stack:** PHP 8 · MySQL · Bootstrap 5 · Vanilla JS · HTML5 · CSS3

---

## 1. Project Overview

**RecLise** is a government-facing internal support platform built for MESRS staff interacting with **ELISSA** (an official ministry management system). It allows employees to submit and track requests and complaints, while support teams and administrators manage, escalate, and resolve them.

### Core Objectives
- Centralize support requests and complaints for ELISSA users
- Give the support team tools to manage, respond, and escalate tickets
- Give administrators full control over users, settings, and audit trails
- Support official correspondence and distribution management
- Provide training session scheduling for ELISSA users
- Offer a knowledge base (assist guides) for self-service help
- Be multilingual (French, English, Arabic with RTL)
- Dark/light theme with a "Cyber-State Government" glassmorphism aesthetic

---

## 2. User Roles & Access Control

The system has **3 roles**, enforced at both routing and API level:

| Role | Email (demo) | Password | Access Level |
|---|---|---|---|
| **user** | user@mesrs.tn | user | Submit requests, track status, messages, training, assist |
| **support** | support@mesrs.tn | support | Manage requests, escalate, training, correspondences, users |
| **admin** | admin@mesrs.tn | admin | Everything + audit logs, statistics, system settings, referentials |

Authentication is session-based (PHP `$_SESSION`). Passwords are hashed with **SHA-256**. Every page calls `Auth::requireRole()` to redirect unauthorized users back to `login.php`.

---

## 3. Repository Structure

```
RECLISE/
│
├── Project_Analysis.md          ← This file
├── sqlpfe.sql                   ← Full MySQL schema + seed data
│
│── HTML Prototypes (static mockups)
│   ├── login_reclise.html       (~17 KB)
│   ├── user_reclise.html        (~141 KB)
│   ├── admin_reclise.html       (~131 KB)
│   └── support_reclise.html     (~168 KB)
│
│── Screenshot Mockups (per role × theme × language)
│   ├── login/Dark/{FR,EN,AR}-Dark/
│   ├── admin/Dark/{FR,EN,AR}-Dark/    ← 11 pages + modals
│   ├── support/Dark/{FR,EN,AR}-Dark/
│   └── user/Dark/{FR,EN,AR}-Dark/
│
│── PHP Production App (primary)
│   └── ++PFE-PHP++/
│
└── PHP Production App (alternate/newer version)
    └── PhtP/
```

> **Two PHP folders exist:** `++PFE-PHP++` and `PhtP`. `PhtP` is the more complete version — it adds `index.php`, `check_passwords.php`, and `check_roles.php` which are absent in `++PFE-PHP++`. Both share the same structure.

---

## 4. Two Parallel Development Tracks

### Track A — HTML Prototypes (`*_reclise.html`)
- 4 self-contained monolithic HTML files (one per role + login)
- 100% frontend — no server, no database
- All data hardcoded / simulated in JavaScript objects
- Used as **visual design references** and stakeholder demos
- Include animated particle canvas background, glassmorphism cards, dark/light toggle, language switcher

### Track B — PHP Production App (`++PFE-PHP++/` / `PhtP/`)
- Full-stack PHP + MySQL implementation
- Mirrors the exact same UI/UX as the HTML prototypes
- Real database queries, sessions, file uploads, AJAX
- Modular: each page/view is a separate PHP file
- Single centralized AJAX endpoint (`ajax/api.php`)

---

## 5. PHP App — Internal Structure

```
++PFE-PHP++/
├── login.php                   ← Entry point (auth + redirect by role)
├── logout.php                  ← Destroys session, redirects to login
├── index.php                   ← Root redirect (PhtP only)
│
├── config/
│   ├── db.php                  ← PDO singleton → reclise_db @ localhost
│   └── auth.php                ← Config-level auth wrapper
│
├── includes/
│   ├── auth.php                ← Auth class (start/check/require/login/logout/redirectByRole)
│   ├── functions.php           ← Helpers: is_logged_in, log_audit, sanitize, to_camel, to_camel_all
│   ├── i18n.php                ← All UI strings in FR/EN/AR (~748 lines, ~240 keys each)
│   ├── header.php              ← HTML head + Bootstrap 5.3 + FontAwesome 6.4 + styles.css
│   ├── topbar.php              ← Top navigation: user avatar, theme toggle, language switcher
│   ├── sidebar.php             ← Role-based sidebar: renders nav items per role from PHP array
│   └── footer.php              ← Closing HTML + JS scripts
│
├── ajax/
│   └── api.php                 ← Single REST-like endpoint (~550 lines), all AJAX actions
│
├── admin/                      ← 19 PHP page files for admin role
├── support/                    ← 14 PHP page files for support role
├── user/                       ← 7 PHP page files for user role
│
├── assets/
│   ├── css/styles.css          ← Global design system (glassmorphism, neon, dark/light)
│   ├── js/                     ← Frontend JS (RecLise namespace, AJAX calls)
│   ├── img/                    ← Logos (logo-light.svg, logo-dark.svg, logo-light.png, logo-dark.png)
│   └── images/
│
└── uploads/                    ← Ticket file attachments (runtime)
```

---

## 6. Page Inventory Per Role

### 👤 User Pages (`user/`)
| File | Purpose |
|---|---|
| `dashboard.php` | Personal overview: my requests summary, quick actions |
| `submit-request.php` | New ticket form (type, category, priority, file upload) |
| `track-status.php` | Filter & search my tickets by status |
| `messages.php` | Conversation thread with support for a selected ticket |
| `training.php` | Browse training sessions + register/unregister |
| `assist.php` | Knowledge base / help guides (self-service) |
| `history.php` | Full history of all my past requests |

### 🛠️ Support Pages (`support/`)
| File | Purpose |
|---|---|
| `dashboard.php` | Overview: all request stats, recent activity |
| `requests.php` | Full request list with reply, status update, escalate |
| `incoming-requests.php` | New/unread requests only |
| `escalation-history.php` | Requests that have been escalated |
| `training.php` | Create/edit/delete training sessions, view registrations |
| `assist.php` | Manage knowledge base guides |
| `history.php` | Full action history |
| `users.php` | View and manage user accounts |
| `technical-issues.php` | Track and resolve platform-level issues |
| `distribution-boxes.php` | Manage named correspondence mailboxes |
| `correspondences.php` | Manage official correspondence records |
| `analytics.php` | Charts: requests by status/category/priority |
| `customize-dashboards.php` | Customize dashboard widget visibility per user |
| `messages.php` | Message thread view |

### 🔧 Admin Pages (`admin/`)
| File | Purpose |
|---|---|
| `dashboard.php` | Full platform stats + recent audit activity |
| `users.php` | Full user CRUD + role delegation + password reset |
| `user-management.php` | Review pending user registration requests |
| `requests.php` | All tickets with full controls |
| `escalated-requests.php` | Admin-escalated tickets requiring intervention |
| `training.php` | Training session management |
| `assist.php` | Knowledge base management |
| `history.php` | Full history log |
| `audit-logs.php` | Complete audit trail (all actions logged) |
| `statistics.php` | Platform-wide analytics and charts |
| `technical-issues.php` | Technical problem tracking and resolution |
| `distribution-boxes.php` | Mailbox management (CRUD + members) |
| `correspondences.php` | Correspondence record management |
| `references.php` / `referentials.php` | Manage sub-tasks and closure motives |
| `system-settings.php` | Platform config: SLA hours, session timeout, language, email notifs |
| `customization.php` | Per-user dashboard widget customization |
| `common-customization.php` | Global column display + workflow customization |

---

## 7. Database Schema (`sqlpfe.sql`)

**Database:** `reclise_db` · Engine: InnoDB · Charset: utf8mb4

### Core Tables

| Table | Key Columns | Purpose |
|---|---|---|
| `users` | id, email, password_hash, full_name, role, phone, department, status, avatar | All platform accounts |
| `requests` | id, user_id, type, title, description, category, priority, status, assigned_to, escalated_to_admin | Central ticket table |
| `request_attachments` | id, request_id, file_name, file_path | Files attached to tickets |
| `messages` | id, request_id, sender_type, sender_id, body | Conversation threads per ticket |
| `audit_logs` | id, action, user_id, details, created_at | Full action audit trail |
| `training_sessions` | id, title, description, date, duration, created_by | ELISSA training events |
| `training_registrations` | id, user_id, session_id, status | Users registered to sessions |
| `guides_coordinators` | id, full_name, email, type | Knowledge base guides |
| `assist_guides` | id, title, content, category | Help articles |
| `correspondences` | id, name, type, content, assignee, created_by | Official correspondence records |
| `referentials` | id, type, value | Predefined values (sub-tasks, closure motives) |
| `ref_closure_motives` | id, label | Ticket closure reasons |
| `distribution_boxes` | id, name, location | Named mailboxes |
| `distribution_box_members` | id, box_id, user_id, member_name | Mailbox membership |
| `technical_issues` | id, title, description, priority, status, created_by | Platform tech problems |
| `system_settings` | id, setting_key, setting_value | Key-value platform config |
| `user_registrations` | id, full_name, email, role, status, reviewed_by | Pending account requests |
| `user_customizations` | id, user_id, widget_* flags | Per-user dashboard preferences |
| `notifications` | id, user_id, title, body, is_read | In-app notification inbox |

### Ticket Status Flow
```
new → in_progress → resolved → closed
           ↓
        escalated  (→ admin intervention → resolved/closed)
```

### Enums & Field Constraints
- `users.role`: `user | support | admin`
- `users.status`: `active | inactive`
- `requests.type`: `request | complaint`
- `requests.category`: `technical | access | training`
- `requests.priority`: `low | medium | high`
- `requests.status`: `new | in_progress | resolved | escalated | closed`
- `messages.sender_type`: `user | support | admin | system`

### Database Views
| View | Purpose |
|---|---|
| `v_request_stats` | Aggregate counts + resolution rate for dashboards |
| `v_requests_detail` | Tickets joined with requester + assignee names |
| `v_requests_by_department` | Ticket counts grouped by department |
| `v_training_summary` | Training sessions with registration counts |

---

## 8. Authentication & Session Flow

```
1. User visits login.php
   └── Already logged in? → Auth::redirectByRole()

2. POST email + password
   └── SHA-256(password) compared to users.password_hash
   └── user.status must be 'active'
   └── On success: Auth::login() sets $_SESSION:
       { user_id, email, full_name, role, lang }
   └── INSERT audit_logs: action='LOGIN'
   └── Auth::redirectByRole():
       admin   → /admin/dashboard.php
       support → /support/dashboard.php
       user    → /user/dashboard.php

3. Every protected page
   └── includes/header.php → requires auth.php + functions.php
   └── Auth::requireRole($role) or Auth::require()
   └── If session invalid → redirect to login.php

4. Logout (logout.php)
   └── Auth::logout(): clear session + destroy cookie
   └── Redirect to login.php
```

---

## 9. AJAX API (`ajax/api.php`)

Single PHP endpoint. All frontend dynamic interactions POST to:
`/ajax/api.php?action=<actionName>`

Returns JSON. Always validates session + role before acting.

### API Actions by Category

**Session / Preferences**
| Action | Role | Description |
|---|---|---|
| `setTheme` | All | Save dark/light theme to session |
| `setLanguage` | All | Save language (fr/en/ar) to session |
| `getData` | All | Load current user's data + requests |

**Ticket Management**
| Action | Role | Description |
|---|---|---|
| `createRequest` | user | Create new ticket + file upload, auto-assign to support |
| `updateRequestStatus` | support/admin | Change status + optional reply message |
| `replyToRequest` | All | Post a message into the thread |
| `escalateRequest` | support | Escalate ticket to admin, insert system message |

**User Management**
| Action | Role | Description |
|---|---|---|
| `addUser` | admin | Create new user with SHA-256 hashed password |
| `updateUser` | admin | Update user details (+ optional new password) |
| `getUser` | admin | Fetch single user by ID |
| `deleteUser` | admin | Delete (cannot delete self) |
| `resetPassword` | admin | Reset a user's password |
| `delegateRole` | admin | Change a user's role |
| `approveRegistration` | support/admin | Approve pending user registration |
| `rejectRegistration` | support/admin | Reject pending user registration |

**Training**
| Action | Role | Description |
|---|---|---|
| `registerTraining` | user | Toggle registration for a session |
| `addTrainingSession` | support/admin | Create a training session |
| `getTrainingSession` | All | Fetch single session |
| `updateTrainingSession` | support/admin | Edit session details |
| `deleteTrainingSession` | support/admin | Delete session + all registrations |
| `getTrainingRegistrations` | support/admin | List registrants for a session |

**Knowledge Base (Assist)**
| Action | Role | Description |
|---|---|---|
| `addGuide` | support/admin | Create a guide |
| `getGuide` | All | Fetch guide by ID |
| `editGuide` | support/admin | Update guide |
| `deleteGuide` | support/admin | Remove guide |

**Correspondences & Mailboxes**
| Action | Role | Description |
|---|---|---|
| `addCorrespondence` | admin/support | Create correspondence record |
| `deleteCorrespondence` | admin/support | Remove correspondence |
| `addBox` | admin/support | Create distribution mailbox |
| `deleteBox` | admin/support | Remove mailbox |

**Technical Issues**
| Action | Role | Description |
|---|---|---|
| `addTechIssue` / `addTechnicalIssue` | admin/support | Log a technical problem |
| `resolveTechIssue` / `resolveTechnicalIssue` | admin/support | Mark as resolved |
| `deleteTechIssue` / `deleteTechnicalIssue` | admin/support | Delete issue |

**Referentials & Settings**
| Action | Role | Description |
|---|---|---|
| `addSubTask` / `deleteSubTask` | admin | Manage sub-task referentials |
| `addClosureMotive` / `deleteClosureMotive` | admin | Manage closure motive referentials |
| `addReferential` / `removeReferential` | admin | Generic referential management |
| `updateSystemSetting` | admin | Update key-value platform config |
| `saveWidgetCustomization` | All | Save dashboard widget visibility |
| `saveCommonCustomization` | admin | Save global column layout |
| `saveWorkflowCustomization` | admin | Save workflow step configuration |

---

## 10. Internationalization (i18n)

**File:** `includes/i18n.php` (~748 lines)

- **Languages:** French (`fr`), English (`en`), Arabic (`ar`)
- **Key count:** ~240 translation keys per language
- **Usage:** `t('key')` function → resolves from `$_SESSION['lang']`
- **RTL support:** `dir="rtl"` applied to `<html>` when `lang === 'ar'`
- **Switching:** Via `?lang=fr|en|ar` URL param or language dropdown
- **Persistence:** Language stored in `$_SESSION['app_lang']`

---

## 11. Design System & Frontend

### Visual Identity
- **Style:** Cyber-State Government Aesthetic
- **Theme:** Dark mode default (togglable to light)
- **UI Pattern:** Glassmorphism (backdrop-blur, semi-transparent cards)
- **Accent:** Neon cyan/blue palette (`#00D4FF`, `#025C84`)
- **Typography:** Relies on Bootstrap 5 defaults + custom overrides

### Frontend Libraries (CDN)
| Library | Version | Purpose |
|---|---|---|
| Bootstrap | 5.3.2 | Layout, components, utilities |
| FontAwesome | 6.4.0 | Icons throughout the UI |
| Chart.js (optional) | — | Statistics/analytics charts |

### Key UI Components
- **Sidebar:** Role-aware nav, collapses on mobile, logo switches with theme
- **Topbar:** User info, theme toggle (moon/sun icon), language dropdown
- **Stat cards:** Glass cards with icon + metric value + label
- **Tables:** `.table-glass` — glassmorphism-styled data tables
- **Status pills:** Color-coded `.status-pill.status-{new|in_progress|resolved|escalated|closed}`
- **Modals:** Bootstrap modals for Add/Edit/Delete/Detail actions
- **Toast notifications:** Custom JS toast system (top-right, auto-dismiss 3.5s)
- **Particle canvas:** Animated node-link background on the login page
- **RecLise JS namespace:** `RecLise.renderContent()`, `RecLise.handleLogout()` etc.

### Page Template Pattern
Every page follows this exact PHP include chain:
```php
$currentView = 'dashboard';          // Sets active sidebar item
require_once '../includes/header.php'; // Auth check + HTML head + sidebar + topbar
// ... page-specific HTML + PHP queries ...
require_once '../includes/footer.php'; // Closing tags + JS
```

---

## 12. Active Workflow (Request Lifecycle)

```
[User] Submits ticket via submit-request.php
    → api.php?action=createRequest
    → INSERT into requests (status='new', auto-assigned to support)
    → Optional file → uploads/ + request_attachments
    → audit_log: CREATE_REQUEST

[Support] Views in requests.php / incoming-requests.php
    → Replies via api.php?action=updateRequestStatus (status='in_progress')
    → Can add message via api.php?action=replyToRequest
    → OR escalates via api.php?action=escalateRequest
        → status='escalated', escalated_to_admin=1
        → System message inserted into thread
        → audit_log: ESCALATE

[Admin] Reviews escalated-requests.php
    → Can resolve, reassign, or close
    → api.php?action=updateRequestStatus (status='resolved'|'closed')
    → audit_log: RESOLVE / UPDATE_STATUS

[User] Tracks status via track-status.php or messages.php
    → Can reply to open thread
    → Sees status history
```

---

## 13. Known Issues & Technical Notes

1. **Duplicate `case` blocks in `api.php`:** `addGuide`, `deleteGuide`, `addCorrespondence`, `deleteCorrespondence`, `addBox`, `deleteBox`, `resetPassword`, `registerTraining` each appear twice with slightly different implementations. PHP `switch` will only execute the first match — the second blocks are dead code.

2. **SHA-256 password hashing:** Used instead of `bcrypt`/`password_hash()`. Functional but not recommended for production security.

3. **Base href hardcoded:** `login.php` sets `<base href="/pfeeeee/++PFE-PHP++/">` — this must match the actual server deployment path.

4. **Two PHP app folders:** `++PFE-PHP++` and `PhtP` are parallel copies. `PhtP` is likely the most current version (has extra utility files). Both should be synchronized or one deprecated.

5. **No CSRF protection:** The AJAX API does not implement CSRF tokens.

6. **`distribution_box_members` table defined twice** in `sqlpfe.sql` — the second definition will fail on a fresh install (duplicate table error). The SQL should use `IF NOT EXISTS` consistently or remove the duplicate.

---

## 14. Deployment Information

| Parameter | Value |
|---|---|
| Server | Apache/Nginx + PHP 8+ |
| Database | MySQL (localhost) |
| DB Name | `reclise_db` |
| DB User | `root` |
| DB Password | _(empty)_ |
| Base URL | `http://localhost/pfeeeee/++PFE-PHP++/` |
| Upload dir | `++PFE-PHP++/uploads/` |
| Session | PHP native sessions |

**To initialize:** Import `sqlpfe.sql` into MySQL, place the PHP app under the Apache/Nginx web root at `/pfeeeee/`, and access via `http://localhost/pfeeeee/++PFE-PHP++/login.php`.

---

## 15. File Count Summary

| Area | Files |
|---|---|
| HTML Prototypes | 4 |
| SQL Schema | 1 |
| PHP Pages (admin) | 19 |
| PHP Pages (support) | 14 |
| PHP Pages (user) | 7 |
| PHP Includes | 7 |
| PHP Config | 2 |
| AJAX API | 1 |
| Python builder scripts | 3 |
| Screenshot mockups | ~100+ HTML/PNG files across all role/lang/theme combos |

---

*Report generated: 2026-05-04 — Conversation ba63666a*
