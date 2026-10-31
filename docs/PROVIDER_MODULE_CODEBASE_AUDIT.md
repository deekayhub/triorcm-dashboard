# TrioRCM Provider Module – Codebase Audit

**Date:** 31 Oct 2026  
**Project:** TrioRCM Dashboard (Existing Laravel + Vue 3 Application)  
**Status:** PART A COMPLETE – AUDIT ONLY, NO IMPLEMENTATION YET

---

## 1. Executive Summary

The TrioRCM application already has significant provider-related infrastructure in place. There are **Provider**, **ProviderApplication**, **ProviderLicense**, **ProviderDocument**, **ProviderVerification**, and **CredentialingStep** models with corresponding migrations, controllers, and Vue pages. However, the comprehensive Provider Profile & CAQH Management module described in the implementation prompt is largely missing.

**Key finding:** Do not create a new project. Extend the existing architecture. Many tables, models, controllers, and Vue pages already exist and should be reused/enhanced rather than duplicated.

---

## 2. Existing Database Table Inventory

### 2.1 Core Identity & Auth Tables

| Table | Engine | Description |
|-------|--------|-------------|
| `users` | InnoDB | Application users (id, name, email, password, 2FA columns, timestamps) |
| `password_reset_tokens` | InnoDB | Password reset (email PK, token, created_at) |
| `sessions` | InnoDB | Session storage (id PK, user_id FK→users, payload, last_activity) |
| `passkeys` | InnoDB | WebAuthn passkeys (from Laravel Fortify) |

### 2.2 RBAC Tables (spatie/laravel-permission, teams=false)

| Table | Engine | Description |
|-------|--------|-------------|
| `permissions` | InnoDB | id, name, guard_name, timestamps; unique(name, guard_name) |
| `roles` | InnoDB | id, name, guard_name, timestamps; unique(name, guard_name) [no team_id] |
| `model_has_permissions` | InnoDB | permission_id, model_type, model_id; PK(permission_id, model_id, model_type) |
| `model_has_roles` | InnoDB | role_id, model_type, model_id; PK(role_id, model_id, model_type) |
| `role_has_permissions` | InnoDB | permission_id, role_id; PK(permission_id, role_id) |

**Migration:** `2026_10_27_110406_create_permission_tables.php`  
**Config:** `config/permission.php` (`teams => false`, `team_foreign_key => 'team_id'`)  
**No `teams` table exists.** The `Team` model is `null` in config. Users are NOT associated with teams.

### 2.3 Activity Log (spatie/laravel-activitylog)

| Table | Engine | Description |
|-------|--------|-------------|
| `activity_log` | InnoDB | id, log_name, description, subject_type/subject_id (morphs), causer_type/causer_id (morphs), properties (json), event, batch_uuid, timestamps; index(log_name) |

**Migrations:** `2026_10_28_104154_create_activity_log_table.php`, `..._add_event_column...`, `..._add_batch_uuid_column...`

### 2.4 Notifications

| Table | Engine | Description |
|-------|--------|-------------|
| `notifications` | InnoDB | id (uuid PK), type, notifiable_type/notifiable_id (morphs), data, read_at, timestamps |

**Migration:** `2026_10_29_050501_create_notifications_table.php`

### 2.5 Provider-Related Tables (EXISTING)

| Table | Engine | Description |
|-------|--------|-------------|
| `providers` | InnoDB | id, npi (unique, nullable), first_name, last_name, middle_name (nullable), email (unique), phone, fax, dob, ssn_last4 (nullable), gender, provider_type, specialty, taxonomy_code, user_id (FK→users nullable), status, notes, timestamps, soft_deletes |
| `provider_applications` | InnoDB | id, provider_id (FK→providers cascade), application_number (unique), type, status, submitted_by (FK→users), submitted_at, reviewed_by (FK→users), reviewed_at, review_notes, effective_date, expiration_date, timestamps |
| `provider_licenses` | InnoDB | id, provider_id (FK→providers cascade), license_number, state, license_type, issue_date, expiration_date, status, issuing_board, timestamps; unique(provider_id, license_number, state) |
| `provider_documents` | InnoDB | id, provider_id (FK→providers cascade), provider_application_id (nullable FK→provider_applications), type, name, file_path, file_name, mime_type, file_size, status, reviewed_by (nullable FK→users), reviewed_at, review_notes, uploaded_by (nullable FK→users), timestamps |
| `provider_verifications` | InnoDB | id, provider_id (FK→providers cascade), provider_application_id (nullable FK→provider_applications), type, verification_source, status, requested_at, received_at, verified_at, verified_by (nullable FK→users), notes, findings, timestamps |
| `credentialing_steps` | InnoDB | id, provider_application_id (FK→provider_applications cascade), name, order, status, assigned_to (nullable FK→users), started_at, completed_at, completed_by (nullable FK→users), notes, timestamps |

**Migration files:** `2026_10_30_051549_create_providers_table.php` through `2026_10_30_051614_create_credentialing_steps_table.php`

### 2.6 Tables NOT Present (Missing for Full Module)

- No `provider_personal_details`, `provider_identifiers`, `provider_education`, `provider_specialties`, `provider_locations`, `provider_hospital_affiliations`, `provider_contacts`, `provider_insurances`, `provider_employments`, `provider_references`, `provider_disclosures`, `provider_authorizations`
- No `caqh_profiles`, `caqh_profile_sections`, `caqh_attestations`, `caqh_follow_ups`
- No `teams` table
- No specialty master table
- No disclosure questions master table

---

## 3. Existing Model Inventory

### 3.1 `App\Models\User`

- **Table:** `users`
- **Traits:** `HasFactory`, `HasRoles` (spatie), `LogsActivity` (spatie), `Notifiable`, `PasskeyAuthenticatable`, `TwoFactorAuthenticatable`
- **Fillable:** `name`, `email`, `password`
- **Hidden:** `password`, `two_factor_secret`, `two_factor_recovery_codes`, `remember_token`
- **Casts:** `email_verified_at` → datetime, `password` → hashed, `two_factor_confirmed_at` → datetime
- **Relationships:** None defined directly (roles/permissions via HasRoles trait)
- **Activity log:** Logs `name`, `email` changes only
- **No team relationship** (teams feature disabled)

### 3.2 `App\Models\Provider`

- **Table:** `providers`
- **Traits:** `HasFactory`, `LogsActivity`, `SoftDeletes`
- **Fillable:** `npi`, `first_name`, `last_name`, `middle_name`, `email`, `phone`, `fax`, `dob`, `ssn_last4`, `gender`, `provider_type`, `specialty`, `taxonomy_code`, `user_id`, `status`, `notes`
- **Casts:** `dob` → date
- **Relationships:**
  - `applications()` → HasMany `ProviderApplication`
  - `licenses()` → HasMany `ProviderLicense`
  - `documents()` → HasMany `ProviderDocument`
  - `verifications()` → HasMany `ProviderVerification`
  - `user()` → BelongsTo `User`
- **Missing relationships:** No team, no `created_by`, no `updated_by`
- **Accessors:** `getFullNameAttribute()`
- **Activity log:** Logs `first_name`, `last_name`, `email`, `status`, `npi`

### 3.3 `App\Models\ProviderApplication`

- **Table:** `provider_applications`
- **Traits:** `HasFactory`, `LogsActivity`
- **Relationships:** `provider`, `submittedBy`, `reviewedBy`, `documents`, `verifications`, `steps`

### 3.4 `App\Models\ProviderLicense`

- **Table:** `provider_licenses`
- **Traits:** `HasFactory`
- **Relationships:** `provider`
- **Method:** `isExpired()`

### 3.5 `App\Models\ProviderDocument`

- **Table:** `provider_documents`
- **Traits:** `HasFactory`, `LogsActivity`
- **Relationships:** `provider`, `application`, `reviewedBy`, `uploadedBy`

### 3.6 `App\Models\ProviderVerification`

- **Table:** `provider_verifications`
- **Traits:** `HasFactory`, `LogsActivity`
- **Relationships:** `provider`, `application`, `verifiedBy`

### 3.7 `App\Models\CredentialingStep`

- **Table:** `credentialing_steps`
- **Traits:** `HasFactory`
- **Relationships:** `application`, `assignedTo`, `completedBy`

---

## 4. Existing Model Relationship Map

```
User ──HasMany── Provider (via user_id)
       ├── HasMany ProviderApplication (via submitted_by / reviewed_by)
       ├── HasMany ProviderDocument (via uploaded_by / reviewed_by)
       ├── HasMany ProviderVerification (via verified_by)
       ├── HasMany CredentialingStep (via assigned_to / completed_by)
       └── HasRoles/Permissions (spatie, morphs)

Provider ──HasMany── ProviderApplication
       ├── HasMany ProviderLicense
       ├── HasMany ProviderDocument
       ├── HasMany ProviderVerification
       └── BelongsTo User (assigned user)

ProviderApplication ──HasMany── ProviderDocument
                ├── HasMany── ProviderVerification
                └── HasMany── CredentialingStep

ProviderLicense ──BelongsTo── Provider
ProviderDocument ──BelongsTo── Provider
                └── BelongsTo── ProviderApplication (nullable)
ProviderVerification ──BelongsTo── Provider
                   └── BelongsTo── ProviderApplication (nullable)
CredentialingStep ──BelongsTo── ProviderApplication
```

**No Provider-to-Team relationship exists.** No team table exists.

---

## 5. Existing Route and Controller Structure

### 5.1 Routes (`routes/web.php`)

All routes are within `middleware(['auth', 'verified', 'permission:dashboard.view'])`.

| Route | Controller | Middleware |
|-------|-----------|------------|
| `GET /dashboard` | Inertia Dashboard | permission:dashboard.view |
| `GET /users` | UserController@index | permission:users.view |
| `GET/POST/PUT/PATCH/DELETE /users/{user}` | UserController | role:super-admin |
| `GET/POST /notifications` | NotificationController | (auth) |
| `Route::resource('providers')` | ProviderController | (within auth group, no specific permission middleware) |
| `Route::resource('provider-applications')` | ProviderApplicationController | (within auth group) |
| `Route::resource('provider-licenses')` | ProviderLicenseController | (within auth group) |
| `Route::resource('provider-documents')` | ProviderDocumentController | (within auth group) |
| `Route::resource('provider-verifications')` | ProviderVerificationController | (within auth group) |
| `Route::resource('credentialing-steps')` | CredentialingStepController | (within auth group, stub methods) |
| `GET/PUT/DELETE /roles/{role}` | RoleController | permission:roles.* |

**Observation:** Provider resource routes do NOT have explicit permission middleware. Authorization is not enforced on provider endpoints beyond the global `auth` middleware.

### 5.2 Controllers

| Controller | File | Status |
|------------|------|--------|
| `ProviderController` | `app/Http/Controllers/ProviderController.php` | Full CRUD implemented |
| `ProviderApplicationController` | `app/Http/Controllers/ProviderApplicationController.php` | Full CRUD implemented |
| `ProviderLicenseController` | `app/Http/Controllers/ProviderLicenseController.php` | Full CRUD implemented |
| `ProviderDocumentController` | `app/Http/Controllers/ProviderDocumentController.php` | Full CRUD implemented |
| `ProviderVerificationController` | `app/Http/Controllers/ProviderVerificationController.php` | Full CRUD implemented |
| `CredentialingStepController` | `app/Http/Controllers/CredentialingStepController.php` | **Stub** (empty methods) |
| `RoleController` | `app/Http/Controllers/RoleController.php` | Full CRUD |
| `UserController` | `app/Http/Controllers/UserController.php` | Full CRUD |
| `ActivityLogController` | `app/Http/Controllers/ActivityLogController.php` | Index only (DataTables) |
| `NotificationController` | `app/Http/Controllers/NotificationController.php` | Index/unread/read/markAll |

**No Form Request classes exist for providers** (only `StoreUserRequest`, `UpdateUserRequest` for users). Validation is inline in controllers.

**No Provider Policy exists.** No `app/Policies/` directory found.

### 5.3 API Routes

No `routes/api.php` exists. All routes are web/Inertia routes in `routes/web.php`.

---

## 6. Existing Vue Page and Component Structure

### 6.1 Vue Pages (`resources/js/pages/`)

| Directory | Files | Status |
|-----------|-------|--------|
| `providers/` | `Index.vue`, `Create.vue` | **Missing `Show.vue`, `Edit.vue`** |
| `provider-applications/` | `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue` | Complete |
| `provider-licenses/` | `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue` | Complete |
| `provider-documents/` | `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue` | Complete |
| `provider-verifications/` | `Index.vue`, `Create.vue`, `Edit.vue`, `Show.vue` | Complete |
| `activity-logs/` | `Index.vue` | Complete |
| `notifications/` | `Index.vue` | Complete |
| `roles/` | `Index.vue`, `Create.vue`, `Edit.vue` | Complete |
| `users/` | `Index.vue`, `Create.vue`, `Edit.vue` | Complete |
| `settings/` | `Appearance.vue`, `Profile.vue`, `Security.vue` | Complete |
| `Dashboard.vue` | Dashboard (generic KPIs, charts) | Complete |
| `Welcome.vue` | Welcome page | Complete |
| `auth/` | Login, etc. | Complete |
| `errors/` | Error pages | Complete |

**Observation:** `providers/Show.vue` and `providers/Edit.vue` are referenced by `ProviderController` but do NOT exist. The `Index.vue` uses `props.verifications.data` (a misnamed prop) instead of `props.providers.data`.

### 6.2 Vue Route Definitions (`resources/js/routes/`)

Wayfinder-generated route definitions exist for:
- `providers/` (`index.ts` – full resource routes)
- `provider-applications/` (`index.ts`)
- `provider-licenses/` (`index.ts`)
- `provider-documents/` (`index.ts`)
- `provider-verifications/` (`index.ts`)
- `credentialing-steps/` (`index.ts`)

### 6.3 Layouts

| File | Description |
|------|-------------|
| `resources/js/layouts/AppLayout.vue` | Main app layout |
| `resources/js/layouts/app/AppSidebarLayout.vue` | Sidebar layout (used by provider pages) |
| `resources/js/layouts/app/AppHeaderLayout.vue` | Header layout |
| `resources/js/layouts/AuthLayout.vue` | Auth layout |
| `resources/js/layouts/settings/` | Settings layouts |

### 6.4 Sidebar Navigation (`resources/js/components/AppSidebar.vue`)

Current main nav items: **Dashboard, Providers, Applications**. No CAQH, no profile sections, no documents nav item.

### 6.5 UI Framework

- **Vue 3.5.13** with Inertia.js v3
- **Tailwind CSS v4**
- **shadcn/ui** components in `resources/js/components/ui/` (button, input, card, badge, dialog, sheet, sidebar, select, spinner, sonner toast, skeleton, textarea, etc.)
- **Lucide Vue** icons
- **@vueuse/core**
- **class-variance-authority**, `clsx`, `tailwind-merge`
- **Datatables.net** for server-side tables

### 6.6 Frontend State & Auth

- `usePermissions()` composable reads `page.props.auth.permissions` / `.roles`
- `useToast()` composable (currently a stub that console.logs)
- `cn()` utility in `resources/js/lib/utils.ts`
- No Pinia/Vuex state management
- No Axios service layer (Inertia forms used directly)

---

## 7. Existing RBAC Role and Permission Structure

### 7.1 Configuration (`config/permission.php`)

- `teams => false` (team-based permissions disabled)
- `team_foreign_key => 'team_id'`
- `models.team => null`
- `register_permission_check_method => true`
- Cache: 24 hours, default store

### 7.2 Existing Roles & Permissions (`database/seeders/RolePermissionSeeder.php`)

| Role | Permissions |
|------|-------------|
| `super-admin` | dashboard.view, dashboard.export, reports.view, users.view, users.create, users.update, users.delete, roles.view, roles.manage, settings.manage, activity-logs.view |
| `admin` | Same as super-admin |
| `manager` | dashboard.view, dashboard.export, reports.view, users.view, users.update |
| `viewer` | dashboard.view, reports.view |

**No provider-specific permissions exist.** Need to add:
- providers.viewAny, providers.view, providers.create, providers.update, providers.archive, providers.assign
- providers.viewSensitive
- providers.identifiers.manage
- providers.documents.upload/view/download/review
- providers.authorization.manage
- providers.caqh.view/manage/attestation.record/followups.manage
- providers.reports.view/export

### 7.3 Gate Configuration (`app/Providers/AppServiceProvider.php`)

- `Gate::before` grants all access to `super-admin` role

---

## 8. Existing Document Storage Implementation

### 8.1 Filesystem (`config/filesystems.php`)

| Disk | Driver | Root | Public |
|------|--------|------|--------|
| `local` (default) | local | `storage/app/private` | No |
| `public` | local | `storage/app/public` | Yes (via `storage:link`) |
| `s3` | s3 | Configurable | Configurable |

**`FILESYSTEM_DISK=local`** in `.env` → files stored in `storage/app/private` (not publicly accessible by default).

### 8.2 Existing `provider_documents` Table

Stores: `file_path`, `file_name`, `mime_type`, `file_size`, `status` (pending/approved/rejected), `reviewed_by`, `reviewed_at`, `review_notes`, `uploaded_by`.

**No file download controller route exists.** No document download/middleware for secure access. No file type validation at upload (only string validation on `file_path`). No malware scanning. No versioning. No expiry tracking.

### 8.3 Storage Gaps

- No dedicated "providers" disk
- No signed/download URLs for private documents
- No file size validation in controller
- No MIME type validation
- No virus/malware scanning
- No document version history
- No document expiry reminders

---

## 9. Existing Activity Log Implementation

### 9.1 Package: `spatie/laravel-activitylog`

- **Table:** `activity_log` (configurable table name)
- **Columns:** id, log_name, description, subject (morphs), causer (morphs), properties (json), event, batch_uuid, timestamps
- **Models using LogsActivity:** `User`, `Provider`, `ProviderApplication`, `ProviderDocument`, `ProviderVerification`
- **Controllers using `activity()` helper:** ProviderController, ProviderLicenseController, ProviderDocumentController, ProviderVerificationController, ProviderApplicationController, UserController

### 9.2 Activity Log Gaps

- No activity logging on provider profile sections (personal details, education, specialties, etc.) – these tables don't exist yet
- No activity logging on CAQH profile changes, attestations, follow-ups
- No activity logging on document download/view events
- `ssn_last4` is logged potentially (it's in Provider fillable but not in logOnly – currently only `first_name`, `last_name`, `email`, `status`, `npi` are logged, which is good)

---

## 10. Existing Notification Implementation

### 10.1 Table: `notifications`

Standard Laravel notifications table (morphs notifiable).

### 10.2 Notification Classes

| Class | File | Purpose |
|-------|------|---------|
| `TeamMemberCreated` | `app/Notifications/TeamMemberCreated.php` | New user created |
| `TeamMemberUpdated` | `app/Notifications/TeamMemberUpdated.php` | User updated |
| `TeamMemberDeleted` | `app/Notifications/TeamMemberDeleted.php` | User deleted |

### 10.3 Notification Controller

`NotificationController` – index, unread, markAsRead, markAllAsRead.

### 10.4 Notification Gaps

- No provider-specific notifications (assignment, expiry, CAQH due, document review, etc.)
- No notification preferences model
- No reminder deduplication logic
- No queued notification jobs

---

## 11. Missing Functionality (Gap Analysis)

| Category | Missing Items |
|----------|---------------|
| **Database Tables** | provider_personal_details, provider_identifiers, provider_education, provider_specialties, specialty master, provider_locations, provider_hospital_affiliations, provider_contacts, provider_insurances, provider_employments, provider_references, provider_disclosures, disclosure_questions master, provider_authorizations, caqh_profiles, caqh_profile_sections, caqh_attestations, caqh_follow_ups |
| **Provider Table Columns** | `team_id`, `created_by`, `updated_by`, `display_name` (missing); `ssn_last4` should be removed/moved to sensitive storage |
| **Models** | No models for any of the new profile section tables; no CAQH models |
| **Controllers** | No controllers for new profile sections; no CAQH controller |
| **Policies** | No `app/Policies/` directory exists; no ProviderPolicy |
| **Form Requests** | No provider-specific form requests |
| **API Resources** | No API resource classes |
| **Services/Actions** | No service layer for providers |
| **Vue Pages** | `providers/Show.vue`, `providers/Edit.vue` missing; no `ProviderProfileLayout.vue`; no section-specific pages/components |
| **Permissions** | No provider-specific permissions seeded |
| **RBAC** | Provider permissions not mapped to any roles |
| **Notifications** | No provider/CAQH/document expiry notifications |
| **Activity Log** | No logging for profile section updates, CAQH changes, attestations |
| **Document Security** | No secure download route, no file validation, no versioning, no expiry tracking |
| **CAQH Module** | Entirely missing (profiles, sections, attestations, follow-ups, dashboard, reminders) |
| **Scheduler/Jobs** | No scheduled jobs for reminders; no queued jobs |
| **Completion Engine** | No profile completeness calculation |

---

## 12. Recommended New Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `provider_personal_details` | Extended personal info (address, languages, previous names) | provider_id FK, address, city, state, zip, country, languages (json), previous_names (json) |
| `provider_identifiers` | NPI, DEA, taxonomy, other IDs | provider_id FK, identifier_type, identifier_value, state, issue_date, expiration_date, verification_status, verification_notes |
| `provider_education` | Education & training records | provider_id FK, institution, degree, education_type, specialty, start_date, completion_date, country, state, city, verification_status, document_id FK |
| `provider_specialties` | Provider specialties | provider_id FK, specialty_id FK (master), is_primary, subspecialty, board_certified, board_certification_date, is_active |
| `specialties` | Specialty master data | id, name, code, description, is_active |
| `provider_locations` | Practice locations | provider_id FK, location_name, practice_name, address, city, state, zip, country, phone, fax, email, office_hours (json), is_primary, is_billing, effective_start, effective_end, is_active |
| `provider_hospital_affiliations` | Hospital affiliations | provider_id FK, facility_name, department, affiliation_type, start_date, end_date, status, contact_info, evidence_document_id |
| `provider_contacts` | Credentialing contacts | provider_id FK, contact_name, job_title, organization, email, phone, contact_type, preferred_method, notes |
| `provider_insurances` | Malpractice insurance | provider_id FK, carrier, policy_number, coverage_type, per_claim_amount, aggregate_amount, effective_date, expiration_date, agent_contact, document_id, verification_status |
| `provider_employments` | Employment history | provider_id FK, employer, position, employment_type, address, start_date, end_date, is_current, gap_explanation, verification_status |
| `provider_references` | Professional references | provider_id FK, reference_name, organization, designation, relationship, email, phone, reference_date, verification_status, notes |
| `provider_disclosures` | Disclosure responses | provider_id FK, disclosure_question_id FK, response (yes/no/n/a), explanation, review_status, reviewed_by, reviewed_at |
| `disclosure_questions` | Configurable disclosure questionnaire | id, category, question, response_type, is_required, is_active, order |
| `provider_authorizations` | Authorization evidence | provider_id FK, authorization_type, status, signed_date, effective_date, expiration_date, revocation_date, document_id, reviewer, review_date, notes |
| `caqh_profiles` | CAQH tracking | provider_id FK, caqh_id, status, assigned_to, last_checked, last_attested, next_attestation_due, last_follow_up, next_follow_up, notes |
| `caqh_profile_sections` | CAQH section checklist | caqh_profile_id FK, section_name, status, assigned_user, completion_date, notes, evidence_document_id |
| `caqh_attestations` | Attestation history | caqh_profile_id FK, attestation_date, recorded_by, evidence, notes, previous_due_date, next_due_date, verification_source |
| `caqh_follow_ups` | CAQH follow-ups | caqh_profile_id FK, follow_up_date, assigned_user, contact_method, outcome, next_follow_up_date, notes, related_task |

---

## 13. Recommended Changes to Existing Tables

### 13.1 `providers` table modifications

**Add:**
- `team_id` (unsignedBigInteger, nullable, index) – assignment to existing team structure (note: teams table does not exist yet; teams feature is disabled in permission config. A `teams` table and migration would need to be created, OR use a simpler approach: store team reference differently)
- `created_by` (unsignedBigInteger, nullable, FK→users)
- `updated_by` (unsignedBigInteger, nullable, FK→users)
- `display_name` (string, nullable)

**Remove / Relocate:**
- `ssn_last4` – sensitive PII should not be in the main providers table; move to an encrypted `provider_personal_details` or a separate secure table

**Note on teams:** The `spatie/laravel-permission` config has `teams => false` and no `teams` table exists. Two options:
1. Enable teams in spatie config + create teams table + add team_id to providers
2. Add a simple `team_id` FK to a new `teams` table without enabling spatie teams

**Recommendation:** Create a `teams` table (simple), enable `teams => true` in spatie config, add `team_id` to providers. This follows existing RBAC patterns.

---

## 14. Potential Duplicate Structures to Avoid

1. **Do NOT create a new `users` or authentication system.** Use existing `User` model.
2. **Do NOT create a new RBAC system.** Use existing `spatie/laravel-permission`.
3. **Do NOT create a new activity log table.** Use existing `activity_log` table.
4. **Do NOT create a new notifications table.** Use existing `notifications` table.
5. **Do NOT create a new document storage system.** Reuse `provider_documents` table and `storage/app/private` disk.
6. **Do NOT duplicate `provider_licenses`** for identifiers – extend or add separate `provider_identifiers` table for DEA, taxonomy, etc.
7. **Do NOT create a new `teams` table if one already exists** – confirmed none exists, so one is needed.
8. **Do NOT create separate storage disks per document type** – use the existing `local` (private) disk with a `providers/` subdirectory.

---

## 15. Implementation Risks

| Risk | Severity | Mitigation |
|------|----------|------------|
| `providers` table already has data; adding `NOT NULL` columns could break | High | All new columns `nullable`, use default values |
| `ssn_last4` removal could break existing code | Medium | Keep column but mark as deprecated; move to encrypted storage in new table |
| No `teams` table exists; adding `team_id` FK to non-existent table will fail | High | Create `teams` migration first, or make `team_id` nullable without FK initially |
| `ProviderController::store`/`update` do not use Form Requests | Low | Create ProviderStoreRequest / ProviderUpdateRequest |
| No `providers/Show.vue` or `providers/Edit.vue` pages | High | Controller `show()` and `edit()` will 500; must create pages |
| `Index.vue` uses `props.verifications.data` (wrong prop name) | Medium | Fix prop name to `providers` |
| Wayfinder route files are auto-generated; manual edits may be overwritten | Medium | Run `php artisan wayfinder:generate` after adding routes |
| No ProviderPolicy – authorization not enforced on provider endpoints | High | Create policy and add middleware to routes |
| `credentialing-steps` routes exist but controller is stubbed | Low | Not related to new module; leave as-is |
| CAQH module must not claim integration with actual CAQH portal | High | Clearly label as internal tracking only |

---

## 16. Final Proposed Database Relationship Design

```
┌─────────────────┐      ┌──────────────────┐
│      users      │      │      teams       │
│ (id, name, ...) │      │  (id, name, ...) │
└────────┬────────┘      └────────┬─────────┘
         │                        │
         │ 1:N                    │ 1:N
         ▼                        ▼
┌─────────────────────────────────────────────┐
│             providers                       │
│ id, npi, first_name, last_name, email,      │
│ user_id FK→users, team_id FK→teams,         │
│ created_by FK→users, updated_by FK→users,   │
│ status, soft_deletes                        │
└────────┬────────┬────────┬────────┬─────────┘
         │        │        │        │
    1:N    1:N    1:N      1:N      1:N
         ▼        ▼        ▼        ▼
 ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
 │provider_ │ │provider_ │ │provider_ │ │provider_ │
 │personal_ │ │identifiers│ │education │ │specialties│
 │details   │ │           │ │           │ │           │
 │provider_ │ │provider_ │ │provider_ │ │provider_ │
 │id FK     │ │id FK     │ │id FK     │ │id FK     │
 │...       │ │...       │ │...       │ │specialty_ │
 └──────────┘ │...       │ └──────────┘ │_id FK→    │
              └──────────┘              │specialties│
                                        └──────────┘

 ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
 │provider_ │ │provider_ │ │provider_ │ │provider_ │
 │locations │ │hospital_ │ │contacts  │ │insurances│
 │provider_ │ │affiliations│ │          │ │          │
 │id FK     │ │provider_ │ │provider_ │ │provider_ │
 │is_primary│ │id FK     │ │id FK     │ │id FK     │
 └──────────┘ └──────────┘ └──────────┘ └──────────┘

 ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
 │provider_ │ │provider_ │ │provider_ │ │provider_ │
 │employments│ │references│ │disclosures│ │authoriza-│
 │provider_ │ │provider_ │ │provider_ │ │tions     │
 │id FK     │ │id FK     │ │id FK     │ │provider_ │
 └──────────┘ └──────────┘ └──────────┘ │id FK     │
                                        └──────────┘

 ┌──────────┐
 │provider_ │
 │documents │ (EXISTING – reuse)
 │provider_ │
 │id FK     │
 └──────────┘

 ┌──────────┐
 │  caqh_   │
 │profiles  │
 │provider_ │
 │id FK     │
 └────┬─────┘
      │ 1:N
      ▼
 ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
 │caqh_profile_│ │caqh_     │ │caqh_     │ │caqh_     │
 │sections     │ │attestations│ │follow_ups│ │          │
 │caqh_profile_│ │caqh_profile_│ │caqh_profile_│ │          │
 │id FK        │ │id FK      │ │id FK      │ │          │
 └─────────────┘ └───────────┘ └───────────┘ └──────────┘

Additional:
- specialties (master): id, name, code
- disclosure_questions: id, category, question
```

---

## 17. Exact Implementation Order

### Phase 1 – Provider Management Foundation
1. Create `teams` migration + `Team` model
2. Modify `providers` table: add `team_id`, `created_by`, `updated_by`, `display_name`; deprecate `ssn_last4`
3. Enable `teams => true` in `config/permission.php`
4. Create Provider policies (`app/Policies/ProviderPolicy.php`)
5. Create provider permissions + seed them
6. Create `ProviderStoreRequest`, `ProviderUpdateRequest`
7. Create `ProviderResource` API resource
8. Enhance `ProviderController` (authorization, team/created_by/updated_by)
9. Create `providers/Show.vue`, `providers/Edit.vue` pages
10. Fix `providers/Index.vue` prop name and add search/filter/pagination
11. Add provider nav items to sidebar
12. Add provider permission middleware to routes
13. Run migrations, tests, verify

### Phase 2 – Provider Profile Layout and Personal Information
1. Create `provider_personal_details` migration + model
2. Create `ProviderProfileLayout.vue` (left nav + right content)
3. Create personal information section component
4. Add section save/update endpoints
5. Activity logging for section updates
6. Completion indicators

### Phase 3 – Professional IDs
1. Create `provider_identifiers` migration + model
2. NPI, DEA, taxonomy, state license management
3. Duplicate detection, expiry tracking, verification status

### Phase 4 – Education and Professional Training
1. Create `provider_education` migration + model
2. Education types (Medical School, Residency, Fellowship, Internship, Other)
3. Verification status, supporting document

### Phase 5 – Specialties
1. Create `specialties` master table + model
2. Create `provider_specialties` migration + model
3. Primary/secondary/subspecialty, board certification

### Phase 6 – Practice Locations
1. Create `provider_locations` migration + model
2. Primary location validation, billing address, effective dates

### Phase 7 – Hospital Affiliations
1. Create `provider_hospital_affiliations` migration + model

### Phase 8 – Credentialing Contacts
1. Create `provider_contacts` migration + model

### Phase 9 – Professional Liability Insurance
1. Create `provider_insurances` migration + model
2. Expiry reminders

### Phase 10 – Employment Information
1. Create `provider_employments` migration + model

### Phase 11 – Professional References
1. Create `provider_references` migration + model

### Phase 12 – Disclosure
1. Create `disclosure_questions` + `provider_disclosures` tables + models
2. Configurable questionnaire, Yes/No/N/A, review workflow

### Phase 13 – Documents Management
1. Enhance `provider_documents` (file validation, size, MIME, versioning, expiry)
2. Secure download route with signed URLs
3. Review/approval workflow
4. Audit logs for upload/view/download/replace/archive

### Phase 14 – Authorization Management
1. Create `provider_authorizations` migration + model
2. Upload evidence, review decisions, expiration tracking

### Phase 15 – CAQH Profile Tracking
1. Create `caqh_profiles`, `caqh_profile_sections`, `caqh_attestations`, `caqh_follow_ups` tables + models
2. CAQH dashboard, statuses, checklist
3. Attestation history
4. Follow-up management

### Phase 16 – Profile Completion Engine
1. Completion percentage calculation
2. Mandatory/optional sections
3. Expired/missing document identification

### Phase 17 – Provider Dashboard
1. Provider operational overview KPIs
2. CAQH due/overdue, expiring licenses/insurance
3. Search, filters, team/staff filters

---

## 18. STOP CONDITION

This audit is complete. **No migrations, models, controllers, Vue pages, or database tables have been created or modified.**

The findings above present:
1. ✅ What already exists (detailed inventory)
2. ✅ What can be reused (Provider model, documents, activity log, notifications, RBAC, file storage)
3. ✅ What is missing (profile sections, CAQH, policies, permissions, Show/Edit pages, secure document download)
4. ✅ Which tables need modification (`providers` table)
5. ✅ Which new tables are required (17 new tables)
6. ✅ The final proposed relationship structure
7. ✅ The exact implementation order (17 phases)

**Awaiting approval before starting Phase 1.**
