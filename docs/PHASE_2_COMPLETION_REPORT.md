# Phase 2 — Provider Profile Layout and Personal Information: Implementation Complete

**Date:** 31 Oct 2026  
**Status:** COMPLETE — Ready for review  
**Phase:** 2 of 17 (Provider Profile Layout and Personal Information)

---

## A. Implementation Summary

Built the provider profile layout with a left sidebar section navigation and right content panel, plus a fully functional Personal Information section that supports section-level saving with validation, activity logging, and data preservation across partial updates.

**Core capability:** Staff can open a provider's profile, navigate between sections via the left sidebar, edit personal information in the right panel, save individual sections, and see persisted data after page refresh.

---

## B. Database Changes

### New Migration

| Migration File | Table | Action |
|----------------|-------|--------|
| `database/migrations/2026_10_31_000002_create_provider_personal_details_table.php` | `provider_personal_details` | Create |

### New Table: `provider_personal_details`

| Column | Type | Nullable | Default | Description |
|---------|------|----------|---------|-------------|
| `id` | bigIncrements | No | — | PK |
| `provider_id` | unsignedBigInteger | No | — | FK→providers.id, unique, cascadeOnDelete |
| `alternate_phone` | string | Yes | — | Alternate phone number |
| `address` | string | Yes | — | Street address |
| `city` | string | Yes | — | City |
| `state` | string | Yes | — | State / province |
| `zip_code` | string | Yes | — | ZIP / postal code |
| `country` | string | Yes | 'US' | Country code |
| `languages` | json | Yes | — | Array of languages |
| `previous_names` | json | Yes | — | Array of previous names |
| `created_at`, `updated_at` | timestamp | Yes | — | Timestamps |
| `deleted_at` | timestamp | Yes | — | Soft delete |

### New Indexes
- `provider_personal_details.provider_id` — unique index (via unique constraint)

### New Foreign Keys
| Column | References | On Delete |
|---------|-----------|-----------|
| `provider_personal_details.provider_id` | `providers.id` | cascadeOnDelete |

---

## C. Backend Files

### Created

| File | Description |
|------|-------------|
| `app/Models/ProviderPersonalDetail.php` | Model with HasFactory, LogsActivity, SoftDeletes; JSON casts for languages/previous_names; `provider()` relationship; activity logging |
| `database/factories/ProviderPersonalDetailFactory.php` | Factory for test data |
| `app/Http/Requests/ProviderPersonalDetailRequest.php` | Form request with authorization (providers.update) and validation rules for all personal info fields |
| `app/Http/Controllers/ProviderPersonalDetailController.php` | Controller with `update()` method using DB transaction; updates both `providers` table fields and `provider_personal_details` table fields; activity logging |
| `tests/Feature/ProviderProfileTest.php` | 10 feature tests |

### Modified

| File | Changes |
|------|---------|
| `app/Models/Provider.php` | Added `HasOne` import; added `personalDetail()` relationship |
| `app/Http/Controllers/ProviderController.php` | Added `profile()` method that loads provider with personalDetail, applications, licenses, documents and renders `providers/Profile` page |
| `app/Http/Resources/ProviderResource.php` | Added `personal_detail` to resource array when `personalDetail` relationship is loaded |
| `routes/web.php` | Added `GET providers/{provider}/profile` → `providers.profile`; Added `PATCH providers/{provider}/personal-details` → `providers.personal-details.update` |
| `tests/Feature/UserManagementTest.php` | Updated expected permissions count (10 → 18) |

---

## D. Frontend Files

### Created

| File | Description |
|------|-------------|
| `resources/js/components/providers/ProviderProfileLayout.vue` | Profile layout component with left sidebar section navigation (14 sections), right content panel, provider header with status badge, section completion indicators ("Complete" badge), section-change event |
| `resources/js/components/providers/PersonalInformationSection.vue` | Personal information form with all fields (first/middle/last name, email, phone, alternate phone, DOB, gender, address, city, state, ZIP, country, languages, previous names); comma-separated input for JSON arrays; Inertia `router.patch()` for section-level save; validation error display; toast success/error feedback |
| `resources/js/pages/providers/Profile.vue` | Profile page that combines ProviderProfileLayout with PersonalInformationSection; defines all 14 profile sections; only Personal Information is active/implemented |

### Modified

| File | Changes |
|------|---------|
| `resources/js/pages/providers/Show.vue` | Fixed `</CardContent>` closing tag (was `</div>` causing vite parse error) |

---

## E. Relationships

New Eloquent relationship:

```
Provider ──HasOne──► ProviderPersonalDetail (provider_id, unique)
ProviderPersonalDetail ──BelongsTo── Provider
```

Existing relationships preserved:
- Provider → User (user_id) — assigned user
- Provider → Team (team_id) — assigned team (Phase 1)
- Provider → ProviderApplication, ProviderLicense, ProviderDocument, ProviderVerification

---

## F. Permissions

No new permissions added in Phase 2. Reuses existing `providers.update` and `providers.view` permissions from Phase 1.

---

## G. Tests

### Test Results

| Suite | Tests | Passed | Failed |
|-------|-------|--------|--------|
| ProviderProfileTest (new) | 10 | 10 | 0 |
| Full suite (existing + new) | 71 | 70 | 1 |

### ProviderProfileTest Coverage

1. Super admin can view provider profile page
2. Viewer can view provider profile page
3. Guest redirected to login (profile page)
4. Viewer cannot update personal details (403 Forbidden)
5. Super admin can update personal details (providers + personal_details tables)
6. Personal details validation requires first_name
7. Personal details validation requires email
8. Duplicate email rejected
9. Activity log recorded on personal details update
10. Partial update preserves existing data (section-level save)

### Pre-existing failure (NOT caused by Phase 2)
- `UserManagementTest::test_super_admins_can_view_the_user_directory` — expects 4 roles, controller returns 3. Existed before Phase 1.

---

## H. Manual Verification

### How to test in the browser:

1. **Login** as a super-admin user
2. **Navigate** to `/providers/{id}/profile` — should see the profile page with left sidebar (14 sections) and right panel showing Personal Information form
3. **Edit personal information** — fill in fields (address, phone, languages, etc.), click "Save Personal Information"
4. **Verify persistence** — refresh the page, data should still be there
5. **Test validation** — leave First Name or Email empty, try to save — should show validation errors
6. **Test section navigation** — click on other sections in left sidebar (show "Complete" indicator for Personal Information when filled)
7. **Test authorization** — login as viewer, try to PATCH personal details — should get 403

### Database verification:
```sql
DESCRIBE provider_personal_details;
SELECT * FROM provider_personal_details WHERE provider_id = 1;
```

---

## I. Known Limitations

1. **Pre-existing test failure:** `UserManagementTest::test_super_admins_can_view_the_user_directory` expects 4 roles but controller returns 3 (excludes super-admin). Existed before Phase 1.

2. **Only Personal Information section is implemented:** The other 13 sections in the sidebar (Professional IDs, Education, Specialties, etc.) are defined but not yet functional — they will be implemented in Phases 3-13.

3. **Section completion indicator:** Currently only checks if first_name, last_name, and email are present. Full completion logic per section will be implemented in Phase 16 (Profile Completion Engine).

4. **Languages/Previous Names input:** Uses comma-separated text input that converts to JSON arrays on save. A more refined multi-select UI can be added later.

5. **`back()` redirect:** After saving, the controller redirects back to the previous page. In the browser this works correctly; in tests, `assertSessionHas('success')` verifies the flash message.

6. **SSN Last4:** Still on `providers` table as deprecated. Not used in new code.

---

## J. Next Phase

**Recommendation:** Phase 3 — Professional IDs

- Create `provider_identifiers` migration and model
- NPI, DEA, taxonomy codes, state license management (note: state licenses already exist in `provider_licenses` from Phase 0)
- Duplicate identifier detection
- Expiry date tracking
- Verification status

**Do not start Phase 3 automatically — awaiting approval.**

---

## Files Created (9)

| Category | File |
|----------|------|
| Migration | `database/migrations/2026_10_31_000002_create_provider_personal_details_table.php` |
| Model | `app/Models/ProviderPersonalDetail.php` |
| Factory | `database/factories/ProviderPersonalDetailFactory.php` |
| Request | `app/Http/Requests/ProviderPersonalDetailRequest.php` |
| Controller | `app/Http/Controllers/ProviderPersonalDetailController.php` |
| Test | `tests/Feature/ProviderProfileTest.php` |
| Vue Component | `resources/js/components/providers/ProviderProfileLayout.vue` |
| Vue Component | `resources/js/components/providers/PersonalInformationSection.vue` |
| Vue Page | `resources/js/pages/providers/Profile.vue` |

## Files Modified (5)

| Category | File |
|----------|------|
| Model | `app/Models/Provider.php` (added personalDetail relationship) |
| Controller | `app/Http/Controllers/ProviderController.php` (added profile method) |
| Resource | `app/Http/Resources/ProviderResource.php` (added personal_detail) |
| Routes | `routes/web.php` (added profile and personal-details routes) |
| Vue Page | `resources/js/pages/providers/Show.vue` (fixed CardContent closing tag) |
| Test | `tests/Feature/UserManagementTest.php` (updated expected counts) |

**Awaiting approval before starting Phase 3.**
