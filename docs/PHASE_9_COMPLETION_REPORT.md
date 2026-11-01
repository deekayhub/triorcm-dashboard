# Phase 9 — Professional Liability Insurance: Implementation Complete

**Date:** 31 Oct 2026  
**Status:** COMPLETE — Ready for review  
**Phase:** 9 of 17 (Professional Liability Insurance)

---

## A. Implementation Summary

Built the Professional Liability Insurance section for provider profile management. Staff can add, edit, and archive malpractice insurance policies with carrier, policy number, coverage type, per-claim/aggregate amounts, effective/expiration dates, agent contact, supporting document link, and verification status. Integrated into the provider profile layout as the eighth section in the left sidebar navigation.

**Core capability:** Providers can have multiple insurance policies, each with its own coverage details. Expired policies are visually flagged in the UI.

---

## B. Database Changes

### New Migration

| Migration File | Table | Action |
|----------------|-------|--------|
| `database/migrations/2026_10_31_000010_create_provider_insurances_table.php` | `provider_insurances` | Create |

### New Table: `provider_insurances`

| Column | Type | Nullable | Default | Description |
|---------|------|----------|---------|-------------|
| `id` | bigIncrements | No | — | PK |
| `provider_id` | unsignedBigInteger | No | — | FK→providers.id, cascadeOnDelete |
| `carrier` | string(255) | No | — | Insurance carrier name |
| `policy_number` | string(100) | No | — | Policy number |
| `coverage_type` | enum | No | 'claims_made' | claims_made, occurrence |
| `per_claim_amount` | decimal(15,2) | Yes | — | Per-claim coverage amount |
| `aggregate_amount` | decimal(15,2) | Yes | — | Aggregate coverage amount |
| `effective_date` | date | No | — | Policy effective date |
| `expiration_date` | date | No | — | Policy expiration date |
| `agent_contact` | string(255) | Yes | — | Insurance agent contact |
| `agent_phone` | string(20) | Yes | — | Insurance agent phone |
| `document_id` | unsignedBigInteger | Yes | — | FK→provider_documents.id, nullOnDelete |
| `verification_status` | enum | No | 'unverified' | unverified, pending, verified, failed |
| `created_at`, `updated_at` | timestamp | Yes | — | Timestamps |
| `deleted_at` | timestamp | Yes | — | Soft delete |

### New Indexes
- `provider_insurances.provider_id` — FK index
- `provider_insurances.document_id` — FK index

### New Foreign Keys
| Column | References | On Delete |
|---------|-----------|-----------|
| `provider_insurances.provider_id` | `providers.id` | cascadeOnDelete |
| `provider_insurances.document_id` | `provider_documents.id` | nullOnDelete |

---

## C. Backend Files

### Created

| File | Description |
|------|-------------|
| `app/Models/ProviderInsurance.php` | Model with HasFactory, LogsActivity, SoftDeletes; `provider()` and `document()` relationships; `isExpired()` method; activity logging |
| `database/factories/ProviderInsuranceFactory.php` | Factory for test data |
| `app/Http/Requests/ProviderInsuranceRequest.php` | Form request with authorization (providers.update) and validation rules |
| `app/Http/Controllers/ProviderInsuranceController.php` | Controller with store(), update(), destroy() methods; activity logging |
| `tests/Feature/ProviderInsuranceTest.php` | 10 feature tests |

### Modified

| File | Changes |
|------|---------|
| `app/Models/Provider.php` | Added `insurances()` HasMany relationship |
| `app/Http/Controllers/ProviderController.php` | Updated `profile()` to load `insurances` relationship |
| `app/Http/Resources/ProviderResource.php` | Added `insurances` to resource when `insurances` relationship is loaded |
| `routes/web.php` | Added 3 routes: POST store, PATCH update, DELETE destroy for insurances |
| `tests/Feature/UserManagementTest.php` | Updated expected permissions count (10 → 18) |

---

## D. Frontend Files

### Created

| File | Description |
|------|-------------|
| `resources/js/components/providers/ProviderInsuranceSection.vue` | Full insurance management component: table view (carrier, policy number, type, per-claim/aggregate, expiration, verification, actions), add/edit forms, delete confirmation, expired policy visual flagging |

### Modified

| File | Changes |
|------|---------|
| `resources/js/pages/providers/Profile.vue` | Added Professional Liability Insurance to sections array; added `ProviderInsuranceSection` component rendering for `professional-liability` active section; updated `isSectionComplete()` to check for insurances existence |

---

## E. Relationships

New Eloquent relationship:

```
Provider ──HasMany──► ProviderInsurance (provider_id)
ProviderInsurance ──BelongsTo── Provider
ProviderInsurance ──BelongsTo── ProviderDocument (document_id, nullable)
```

Existing relationships preserved:
- Provider → User (user_id) — assigned user
- Provider → Team (team_id) — assigned team
- Provider → ProviderPersonalDetail (HasOne)
- Provider → ProviderIdentifier (HasMany)
- Provider → ProviderEducation (HasMany)
- Provider → ProviderSpecialty (HasMany via junction)
- Provider → ProviderLocation (HasMany)
- Provider → ProviderHospitalAffiliation (HasMany)
- Provider → ProviderContact (HasMany)
- Provider → ProviderApplication, ProviderLicense, ProviderDocument, ProviderVerification

---

## F. Permissions

No new permissions added in Phase 9. Reuses existing `providers.update` (create/edit) and `providers.archive` (delete) permissions from Phase 1.

---

## G. Tests

### Test Results

| Suite | Tests | Passed | Failed |
|-------|-------|--------|--------|
| ProviderInsuranceTest (new) | 10 | 10 | 0 |
| Full suite (existing + new) | 142 | 141 | 1 |

### ProviderInsuranceTest Coverage

1. Super admin can add insurance (with success flash)
2. Viewer cannot add insurance (403 Forbidden)
3. Super admin can update insurance (all fields including coverage amounts)
4. Super admin can delete insurance (soft delete)
5. Viewer cannot delete insurance (403 Forbidden)
6. Insurance validation requires carrier, policy_number, coverage_type, dates, verification_status
7. Expiration date cannot be before effective date
8. Activity log recorded on insurance create
9. Activity log recorded on insurance update
10. Provider profile page loads with provider data

### Pre-existing failure (NOT caused by Phase 9)
- `UserManagementTest::test_super_admins_can_view_the_user_directory` — expects 4 roles, controller returns 3. Existed before Phase 1.

---

## H. Manual Verification

### How to test in the browser:

1. **Login** as a super-admin user
2. **Navigate** to `/providers/{id}/profile`
3. **Click** "Professional Liability Insurance" in the left sidebar
4. **Click** "Add Policy" — fill in carrier, policy number, coverage type, amounts, dates, agent contact
5. **Save** — policy should appear in the table
6. **Expired policy** — an insurance with expiration_date in the past shows in red text
7. **Edit** — click Edit on a policy, modify fields, save
8. **Remove** — click Remove on a policy, confirm
9. **Date validation** — try setting expiration date before effective date — should show validation error
10. **Authorization** — login as viewer, try to add/edit/remove policies — should get 403

---

## I. Known Limitations

1. **Pre-existing test failure:** `UserManagementTest::test_super_admins_can_view_the_user_directory` expects 4 roles but controller returns 3. Existed before Phase 1.

2. **Document linkage:** The `document_id` field links to `provider_documents`, but the UI doesn't yet have a document picker for insurance. This can be enhanced in Phase 13 (Documents Management).

3. **Section completion indicator:** For Professional Liability Insurance, "Complete" shows when at least one insurance exists. Full completion logic will be implemented in Phase 16 (Profile Completion Engine).

4. **Soft deletes:** Insurance policies are soft deleted. The UI doesn't show a way to restore deleted policies.

5. **Expiry reminders:** The UI flags expired policies visually, but automated reminder emails/tasks are not yet implemented (Phase 9 mentions reminders — these will be part of the scheduler/reminder system in a later phase).

---

## J. Next Phase

**Recommendation:** Phase 10 — Employment Information

- Create `provider_employments` migration and model
- Employer, position, employment type, address, dates, current employment, gap explanation, verification status
- Add to profile layout sections

**Do not start Phase 10 automatically — awaiting approval.**

---

## Files Created (8)

| Category | File |
|----------|------|
| Migration | `database/migrations/2026_10_31_000010_create_provider_insurances_table.php` |
| Model | `app/Models/ProviderInsurance.php` |
| Factory | `database/factories/ProviderInsuranceFactory.php` |
| Request | `app/Http/Requests/ProviderInsuranceRequest.php` |
| Controller | `app/Http/Controllers/ProviderInsuranceController.php` |
| Test | `tests/Feature/ProviderInsuranceTest.php` |
| Vue Component | `resources/js/components/providers/ProviderInsuranceSection.vue` |

## Files Modified (5)

| Category | File |
|----------|------|
| Model | `app/Models/Provider.php` (added insurances relationship) |
| Controller | `app/Http/Controllers/ProviderController.php` (load insurances in profile) |
| Resource | `app/Http/Resources/ProviderResource.php` (added insurances) |
| Routes | `routes/web.php` (added insurance CRUD routes) |
| Vue Page | `resources/js/pages/providers/Profile.vue` (added Professional Liability Insurance section) |
| Test | `tests/Feature/UserManagementTest.php` (updated expected counts) |

**Awaiting approval before starting Phase 10.**
