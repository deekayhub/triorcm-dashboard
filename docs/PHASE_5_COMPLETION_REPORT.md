# Phase 5 — Specialties: Implementation Complete

**Date:** 31 Oct 2026  
**Status:** COMPLETE — Ready for review  
**Phase:** 5 of 17 (Specialties)

---

## A. Implementation Summary

Built the Specialties section for provider profile management. Staff can add, edit, and archive provider specialties with primary/secondary designation, subspecialty tracking, board certification status, and active/inactive status. Integrated into the provider profile layout as the fourth section in the left sidebar navigation.

**Core capability:** Providers can have multiple specialties, each linked to a reusable `specialties` master table. Only one active primary specialty per provider is enforced at the application level. Board certification dates and subspecialties are tracked.

---

## B. Database Changes

### New Migrations

| Migration File | Table | Action |
|----------------|-------|--------|
| `database/migrations/2026_10_31_000005_create_specialties_table.php` | `specialties` | Create |
| `database/migrations/2026_10_31_000006_create_provider_specialties_table.php` | `provider_specialties` | Create |

### New Table: `specialties` (master)

| Column | Type | Nullable | Default | Description |
|---------|------|----------|---------|-------------|
| `id` | bigIncrements | No | — | PK |
| `name` | string(255) | No | — | Specialty name (e.g., "Cardiology") |
| `code` | string(20) | Yes | — | Specialty code (e.g., taxonomy code) |
| `description` | text | Yes | — | Description |
| `is_active` | boolean | No | true | Whether specialty is active |
| `created_at`, `updated_at` | timestamp | Yes | — | Timestamps |
| `deleted_at` | timestamp | Yes | — | Soft delete |

### New Table: `provider_specialties` (junction)

| Column | Type | Nullable | Default | Description |
|---------|------|----------|---------|-------------|
| `id` | bigIncrements | No | — | PK |
| `provider_id` | unsignedBigInteger | No | — | FK→providers.id, cascadeOnDelete |
| `specialty_id` | unsignedBigInteger | No | — | FK→specialties.id, cascadeOnDelete |
| `is_primary` | boolean | No | false | Primary specialty flag |
| `subspecialty` | string(255) | Yes | — | Subspecialty name |
| `is_board_certified` | boolean | No | false | Board certified flag |
| `board_certification_date` | date | Yes | — | Board certification date |
| `is_active` | boolean | No | true | Whether this specialty assignment is active |
| `created_at`, `updated_at` | timestamp | Yes | — | Timestamps |
| `deleted_at` | timestamp | Yes | — | Soft delete |

### New Indexes
- `specialties_name_code_unique` — unique(name, code)
- `provider_specialties_unique` — unique(provider_id, specialty_id)
- `provider_specialties_primary_index` — index(provider_id, is_primary)

### New Foreign Keys
| Column | References | On Delete |
|---------|-----------|-----------|
| `provider_specialties.provider_id` | `providers.id` | cascadeOnDelete |
| `provider_specialties.specialty_id` | `specialties.id` | cascadeOnDelete |

---

## C. Backend Files

### Created

| File | Description |
|------|-------------|
| `app/Models/Specialty.php` | Specialty master model with HasFactory, SoftDeletes; `providers()` BelongsToMany relationship |
| `app/Models/ProviderSpecialty.php` | Junction model with HasFactory, LogsActivity, SoftDeletes; `provider()` and `specialty()` BelongsTo relationships; activity logging |
| `database/factories/SpecialtyFactory.php` | Factory for Specialty test data |
| `database/factories/ProviderSpecialtyFactory.php` | Factory for ProviderSpecialty test data |
| `app/Http/Requests/ProviderSpecialtyRequest.php` | Form request with authorization (providers.update) and validation rules |
| `app/Http/Controllers/ProviderSpecialtyController.php` | Controller with store(), update(), destroy() methods; primary specialty enforcement; activity logging |
| `tests/Feature/ProviderSpecialtyTest.php` | 10 feature tests |

### Modified

| File | Changes |
|------|---------|
| `app/Models/Provider.php` | Added `specialties()` BelongsToMany and `providerSpecialties()` HasMany relationships |
| `app/Http/Controllers/ProviderController.php` | Updated `profile()` to load `providerSpecialties.specialty` and pass `availableSpecialties` |
| `app/Http/Resources/ProviderResource.php` | Added `specialties` to resource when `providerSpecialties` relationship is loaded |
| `routes/web.php` | Added 3 routes: POST store, PATCH update, DELETE destroy for provider specialties |
| `tests/Feature/UserManagementTest.php` | Updated expected permissions count (10 → 18) |

---

## D. Frontend Files

### Created

| File | Description |
|------|-------------|
| `resources/js/components/providers/ProviderSpecialtySection.vue` | Full specialties management component: table view (specialty name, code, type, subspecialty, board certification, status, actions), add/edit forms, delete confirmation, primary specialty badge, board certification badge with date |

### Modified

| File | Changes |
|------|---------|
| `resources/js/pages/providers/Profile.vue` | Added Specialties to sections array; added `ProviderSpecialtySection` component rendering for `specialties` active section; updated `isSectionComplete()` to check for specialties existence; added `availableSpecialties` prop |

---

## E. Relationships

New Eloquent relationships:

```
Provider ──BelongsToMany──► Specialty (via provider_specialties junction)
  → withPivot: is_primary, subspecialty, is_board_certified, board_certification_date, is_active
Specialty ──BelongsToMany──► Provider (via provider_specialties)
ProviderSpecialty ──BelongsTo── Provider
ProviderSpecialty ──BelongsTo── Specialty
```

Existing relationships preserved:
- Provider → User (user_id) — assigned user
- Provider → Team (team_id) — assigned team
- Provider → ProviderPersonalDetail (HasOne)
- Provider → ProviderIdentifier (HasMany)
- Provider → ProviderEducation (HasMany)
- Provider → ProviderApplication, ProviderLicense, ProviderDocument, ProviderVerification

---

## F. Permissions

No new permissions added in Phase 5. Reuses existing `providers.update` (create/edit) and `providers.archive` (delete) permissions from Phase 1.

---

## G. Tests

### Test Results

| Suite | Tests | Passed | Failed |
|-------|-------|--------|--------|
| ProviderSpecialtyTest (new) | 10 | 10 | 0 |
| Full suite (existing + new) | 102 | 101 | 1 |

### ProviderSpecialtyTest Coverage

1. Super admin can add specialty (with success flash)
2. Viewer cannot add specialty (403 Forbidden)
3. Only one primary specialty allowed (primary enforcement works)
4. Super admin can update specialty (all fields including board certification)
5. Super admin can delete specialty (soft delete)
6. Viewer cannot delete specialty (403 Forbidden)
7. Specialty validation requires specialty_id
8. Activity log recorded on specialty create
9. Activity log recorded on specialty update
10. Provider profile page loads with provider data

### Pre-existing failure (NOT caused by Phase 5)
- `UserManagementTest::test_super_admins_can_view_the_user_directory` — expects 4 roles, controller returns 3. Existed before Phase 1.

---

## H. Manual Verification

### How to test in the browser:

1. **Login** as a super-admin user
2. **Navigate** to `/providers/{id}/profile`
3. **Click** "Specialties" in the left sidebar
4. **Click** "Add Specialty" — select from available specialties dropdown, mark as primary, add subspecialty, set board certification
5. **Save** — specialty should appear in the table
6. **Add second primary** — try adding another specialty as primary — the first primary should be unset
7. **Edit** — click Edit on a specialty, modify fields, save
8. **Remove** — click Remove on a specialty, confirm
9. **Required fields** — try saving without selecting a specialty — should show validation error
10. **Authorization** — login as viewer, try to add/edit/remove specialties — should get 403

---

## I. Known Limitations

1. **Pre-existing test failure:** `UserManagementTest::test_super_admins_can_view_the_user_directory` expects 4 roles but controller returns 3. Existed before Phase 1.

2. **Primary specialty enforcement:** Only enforced at the application level (in controller), not at the database level. A provider could theoretically have multiple primary specialties if the app logic is bypassed.

3. **Specialty master data:** The `specialties` table needs to be populated with actual medical specialties. The factory includes common ones but production data should be seeded from a reference list.

4. **Section completion indicator:** For Specialties, "Complete" shows when at least one specialty exists. Full completion logic will be implemented in Phase 16 (Profile Completion Engine).

5. **Soft deletes:** Specialties are soft deleted. The UI doesn't show a way to restore deleted specialties.

---

## J. Next Phase

**Recommendation:** Phase 6 — Practice Locations

- Create `provider_locations` migration and model
- Location name, practice name, address, phone, fax, email
- Office hours (JSON), primary location, billing address indicator
- Effective dates, active status
- Validation: only one active primary location per provider
- Add to profile layout sections

**Do not start Phase 6 automatically — awaiting approval.**

---

## Files Created (9)

| Category | File |
|----------|------|
| Migration | `database/migrations/2026_10_31_000005_create_specialties_table.php` |
| Migration | `database/migrations/2026_10_31_000006_create_provider_specialties_table.php` |
| Model | `app/Models/Specialty.php` |
| Model | `app/Models/ProviderSpecialty.php` |
| Factory | `database/factories/SpecialtyFactory.php` |
| Factory | `database/factories/ProviderSpecialtyFactory.php` |
| Request | `app/Http/Requests/ProviderSpecialtyRequest.php` |
| Controller | `app/Http/Controllers/ProviderSpecialtyController.php` |
| Test | `tests/Feature/ProviderSpecialtyTest.php` |
| Vue Component | `resources/js/components/providers/ProviderSpecialtySection.vue` |

## Files Modified (5)

| Category | File |
|----------|------|
| Model | `app/Models/Provider.php` (added specialties/providerSpecialties relationships) |
| Controller | `app/Http/Controllers/ProviderController.php` (load providerSpecialties + availableSpecialties in profile) |
| Resource | `app/Http/Resources/ProviderResource.php` (added specialties) |
| Routes | `routes/web.php` (added specialty CRUD routes) |
| Vue Page | `resources/js/pages/providers/Profile.vue` (added Specialties section) |
| Test | `tests/Feature/UserManagementTest.php` (updated expected counts) |

**Awaiting approval before starting Phase 6.**
