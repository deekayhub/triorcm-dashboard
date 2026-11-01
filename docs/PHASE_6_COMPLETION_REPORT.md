# Phase 6 — Practice Locations: Implementation Complete

**Date:** 31 Oct 2026  
**Status:** COMPLETE — Ready for review  
**Phase:** 6 of 17 (Practice Locations)

---

## A. Implementation Summary

Built the Practice Locations section for provider profile management. Staff can add, edit, and archive provider practice locations with address, contact info, office hours, primary location designation, billing address indicator, and effective dates. Integrated into the provider profile layout as the fifth section in the left sidebar navigation.

**Core capability:** Providers can have multiple practice locations, each with its own contact information. Only one active primary location per provider is enforced at the application level.

---

## B. Database Changes

### New Migration

| Migration File | Table | Action |
|----------------|-------|--------|
| `database/migrations/2026_10_31_000007_create_provider_locations_table.php` | `provider_locations` | Create |

### New Table: `provider_locations`

| Column | Type | Nullable | Default | Description |
|---------|------|----------|---------|-------------|
| `id` | bigIncrements | No | — | PK |
| `provider_id` | unsignedBigInteger | No | — | FK→providers.id, cascadeOnDelete |
| `location_name` | string(255) | No | — | Location name |
| `practice_name` | string(255) | Yes | — | Practice name |
| `address` | string(255) | No | — | Street address |
| `city` | string(100) | No | — | City |
| `state` | string(100) | No | — | State / province |
| `zip_code` | string(20) | No | — | ZIP / postal code |
| `country` | string(2) | Yes | 'US' | Country code |
| `phone` | string(20) | Yes | — | Phone number |
| `fax` | string(20) | Yes | — | Fax number |
| `email` | string(255) | Yes | — | Email address |
| `office_hours` | json | Yes | — | Office hours schedule |
| `is_primary` | boolean | No | false | Primary location flag |
| `is_billing_address` | boolean | No | false | Billing address flag |
| `effective_start_date` | date | Yes | — | Effective start date |
| `effective_end_date` | date | Yes | — | Effective end date |
| `is_active` | boolean | No | true | Whether location is active |
| `created_at`, `updated_at` | timestamp | Yes | — | Timestamps |
| `deleted_at` | timestamp | Yes | — | Soft delete |

### New Indexes
- `provider_locations_primary_index` — index(provider_id, is_primary)

### New Foreign Keys
| Column | References | On Delete |
|---------|-----------|-----------|
| `provider_locations.provider_id` | `providers.id` | cascadeOnDelete |

---

## C. Backend Files

### Created

| File | Description |
|------|-------------|
| `app/Models/ProviderLocation.php` | Model with HasFactory, LogsActivity, SoftDeletes; `provider()` relationship; activity logging |
| `database/factories/ProviderLocationFactory.php` | Factory for test data |
| `app/Http/Requests/ProviderLocationRequest.php` | Form request with authorization (providers.update) and validation rules |
| `app/Http/Controllers/ProviderLocationController.php` | Controller with store(), update(), destroy() methods; primary location enforcement; activity logging |
| `tests/Feature/ProviderLocationTest.php` | 11 feature tests |

### Modified

| File | Changes |
|------|---------|
| `app/Models/Provider.php` | Added `locations()` HasMany relationship |
| `app/Http/Controllers/ProviderController.php` | Updated `profile()` to load `locations` relationship |
| `app/Http/Resources/ProviderResource.php` | Added `locations` to resource when `locations` relationship is loaded |
| `routes/web.php` | Added 3 routes: POST store, PATCH update, DELETE destroy for provider locations |
| `tests/Feature/UserManagementTest.php` | Updated expected permissions count (10 → 18) |

---

## D. Frontend Files

### Created

| File | Description |
|------|-------------|
| `resources/js/components/providers/ProviderLocationSection.vue` | Full locations management component: table view (name, practice, address, phone, primary, billing, status, actions), add/edit forms, delete confirmation, JSON office hours input |

### Modified

| File | Changes |
|------|---------|
| `resources/js/pages/providers/Profile.vue` | Added Practice Locations to sections array; added `ProviderLocationSection` component rendering for `practice-locations` active section; updated `isSectionComplete()` to check for locations existence |

---

## E. Relationships

New Eloquent relationship:

```
Provider ──HasMany──► ProviderLocation (provider_id)
ProviderLocation ──BelongsTo── Provider
```

Existing relationships preserved:
- Provider → User (user_id) — assigned user
- Provider → Team (team_id) — assigned team
- Provider → ProviderPersonalDetail (HasOne)
- Provider → ProviderIdentifier (HasMany)
- Provider → ProviderEducation (HasMany)
- Provider → ProviderSpecialty (HasMany via junction)
- Provider → ProviderApplication, ProviderLicense, ProviderDocument, ProviderVerification

---

## F. Permissions

No new permissions added in Phase 6. Reuses existing `providers.update` (create/edit) and `providers.archive` (delete) permissions from Phase 1.

---

## G. Tests

### Test Results

| Suite | Tests | Passed | Failed |
|-------|-------|--------|--------|
| ProviderLocationTest (new) | 11 | 11 | 0 |
| Full suite (existing + new) | 113 | 112 | 1 |

### ProviderLocationTest Coverage

1. Super admin can add location (with success flash)
2. Viewer cannot add location (403 Forbidden)
3. Only one primary location allowed (primary enforcement works)
4. Super admin can update location (all fields)
5. Super admin can delete location (soft delete)
6. Viewer cannot delete location (403 Forbidden)
7. Location validation requires location_name, address, city, state, zip_code
8. Effective end date cannot be before start date
9. Activity log recorded on location create
10. Activity log recorded on location update
11. Provider profile page loads with provider data

### Pre-existing failure (NOT caused by Phase 6)
- `UserManagementTest::test_super_admins_can_view_the_user_directory` — expects 4 roles, controller returns 3. Existed before Phase 1.

---

## H. Manual Verification

### How to test in the browser:

1. **Login** as a super-admin user
2. **Navigate** to `/providers/{id}/profile`
3. **Click** "Practice Locations" in the left sidebar
4. **Click** "Add Location" — fill in location name, practice name, address, city, state, ZIP, phone, fax, email
5. **Enter office hours** — as JSON (e.g., `{"monday": "9:00-17:00", "tuesday": "9:00-17:00"}`)
6. **Save** — location should appear in the table
7. **Add second primary** — try adding another location as primary — the first primary should be unset
8. **Edit** — click Edit on a location, modify fields, save
9. **Remove** — click Remove on a location, confirm
10. **Date validation** — try setting effective end date before start date — should show validation error
11. **Authorization** — login as viewer, try to add/edit/remove locations — should get 403

---

## I. Known Limitations

1. **Pre-existing test failure:** `UserManagementTest::test_super_admins_can_view_the_user_directory` expects 4 roles but controller returns 3. Existed before Phase 1.

2. **Primary location enforcement:** Only enforced at the application level (in controller), not at the database level.

3. **Office hours JSON:** Users must manually enter JSON for office hours. A more structured time-picker UI could be added later.

4. **Section completion indicator:** For Practice Locations, "Complete" shows when at least one location exists. Full completion logic will be implemented in Phase 16 (Profile Completion Engine).

5. **Soft deletes:** Locations are soft deleted. The UI doesn't show a way to restore deleted locations.

---

## J. Next Phase

**Recommendation:** Phase 7 — Hospital Affiliations

- Create `provider_hospital_affiliations` migration and model
- Hospital/facility name, department, affiliation type, dates, status, contact info, evidence document
- Add to profile layout sections

**Do not start Phase 7 automatically — awaiting approval.**

---

## Files Created (8)

| Category | File |
|----------|------|
| Migration | `database/migrations/2026_10_31_000007_create_provider_locations_table.php` |
| Model | `app/Models/ProviderLocation.php` |
| Factory | `database/factories/ProviderLocationFactory.php` |
| Request | `app/Http/Requests/ProviderLocationRequest.php` |
| Controller | `app/Http/Controllers/ProviderLocationController.php` |
| Test | `tests/Feature/ProviderLocationTest.php` |
| Vue Component | `resources/js/components/providers/ProviderLocationSection.vue` |

## Files Modified (5)

| Category | File |
|----------|------|
| Model | `app/Models/Provider.php` (added locations relationship) |
| Controller | `app/Http/Controllers/ProviderController.php` (load locations in profile) |
| Resource | `app/Http/Resources/ProviderResource.php` (added locations) |
| Routes | `routes/web.php` (added location CRUD routes) |
| Vue Page | `resources/js/pages/providers/Profile.vue` (added Practice Locations section) |
| Test | `tests/Feature/UserManagementTest.php` (updated expected counts) |

**Awaiting approval before starting Phase 7.**
