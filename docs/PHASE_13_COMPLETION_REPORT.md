# Phase 13 — Documents Management: Implementation Complete

**Date:** 1 Nov 2026  
**Status:** COMPLETE — Ready for review  
**Phase:** 13 of 17 (Documents Management)

---

## A. Implementation Summary

Built the Documents Management section for provider credentialing. Providers can upload, version, review, and archive credentialing documents (CV, license, DEA, malpractice, education, certification, other). Each document carries file metadata (path, name, MIME, size, SHA-256 checksum), issue/expiration dates, versioning with a version group and `is_current` flag, a review status (`pending` / `approved` / `rejected`) with reviewer and timestamp, and an archive audit trail. Every upload, view, download, replace, review, and archive is written to the activity log.

**Core capability:** A provider document is stored once per version, superseding the prior version on replacement. New uploads default to `pending` review and must be reviewed before they are considered approved. Expired and soon-to-expire documents are surfaced in the UI. Downloads are served through temporary signed URLs.

A `ProviderDocumentTest` was added to match the coverage of the other section phases.

---

## B. Database Changes

### Existing Migration

| Migration File | Table | Action |
|----------------|-------|--------|
| `database/migrations/2026_10_30_051604_create_provider_documents_table.php` | `provider_documents` | Create |

### New Migration

| Migration File | Table | Action |
|----------------|-------|--------|
| `database/migrations/2026_11_01_000000_enhance_provider_documents_table.php` | `provider_documents` | Enhance |

The enhance migration was inspected with `php artisan migrate --pretend`, applied with `php artisan migrate --force` (batch 17), and confirmed via `migrate:status`.

### Enhanced Table: `provider_documents`

Added columns (on top of the existing id, provider_id, provider_application_id, type, name, file_path, file_name, mime_type, file_size, status, reviewed_by, reviewed_at, review_notes, uploaded_by, timestamps):

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `issue_date` | date | Yes | — | Document issue date |
| `expiration_date` | date | Yes | — | Document expiration date |
| `checksum` | string(64) | Yes | — | SHA-256 file checksum |
| `file_disk` | string(50) | No | `local` | Storage disk |
| `version` | unsignedInteger | No | `1` | Document version |
| `version_group` | uuid | Yes | — | Groups versions of the same document |
| `is_current` | boolean | No | `true` | Whether this is the current version |
| `supersedes_id` | unsignedBigInteger | Yes | — | FK→provider_documents.id, nullOnDelete |
| `archived_at` | timestamp | Yes | — | When archived |
| `archived_by` | unsignedBigInteger | Yes | — | FK→users.id, nullOnDelete |

### New Indexes

| Index | Columns |
|-------|---------|
| `provider_documents_provider_current_index` | `provider_id`, `is_current` |
| `provider_documents_version_group_index` | `version_group` |
| `provider_documents_expiration_index` | `expiration_date` |

### New Factory

| File | Description |
|------|-------------|
| `database/factories/ProviderDocumentFactory.php` | Factory with types, dates, checksum, and `approved()` / `rejected()` / `archived()` states |

---

## C. Backend Files

### Existing

| File | Description |
|------|-------------|
| `app/Models/ProviderDocument.php` | Model with HasFactory, LogsActivity, SoftDeletes; `provider()`, `application()`, `reviewedBy()`, `uploadedBy()`, `archivedBy()`, `supersedes()`, `supersededBy()`, `versions()` relations; `scopeActive`/`scopeCurrent`/`scopeExpired`; `isExpired`/`isExpiringSoon`/`fileExists` accessors; `downloadUrl()` signed URL; activity logging |
| `app/Http/Controllers/ProviderDocumentController.php` | `index`, `create`, `store`, `storeForProvider`, `show`, `edit`, `update`, `updateForProvider`, `replace`, `replaceStandalone`, `review`, `reviewStandalone`, `archive`, `archiveStandalone`, `download`; provider-scoping 404 guards; activity logging |
| `app/Http/Requests/ProviderDocumentRequest.php` | Upload/update validation (type, name, file max 10 MB / MIME, dates, expiration >= issue) |
| `app/Http/Requests/ProviderDocumentReviewRequest.php` | Review validation (`approved`/`rejected`), review notes required on rejection, restricted to `providers.viewSensitive` |
| `app/Http/Requests/ProviderDocumentReplaceRequest.php` | Replace validation |
| `app/Http/Resources/ProviderDocumentResource.php` | Exposes document metadata, versions, download URL, reviewer/uploader/archiver |
| `routes/web.php` | `providers.documents.store/update/replace/review/archive/download` and standalone `provider-documents` resource routes |

### Modified

| File | Changes |
|------|---------|
| `app/Models/Provider.php` | `documents()` HasMany (already present) |
| `app/Http/Controllers/ProviderController.php` | Loads `documents` (current, with `reviewedBy`, `uploadedBy`, `versions`) on the profile query |
| `app/Http/Resources/ProviderResource.php` | Exposes `documents` and `documents_count` |
| `routes/web.php` | Document routes (already present) |

### Controller safeguards

- `storeForProvider`/`updateForProvider`/`replace`/`review`/`archive` abort with 404 if the document belongs to another provider in the URL.
- `provider_id` is forced from the route on store; removed from validated data on update.
- A replacement retires the prior version (`is_current = false`), creates a new version in the same version group with `status = pending`, and records `supersedes_id`.
- Review requires `providers.viewSensitive` at both route middleware and form-request layer; rejection requires review notes.
- Archive requires `providers.archive`; sets `archived_at`/`archived_by` and `is_current = false`, retaining the file and audit trail.
- Download requires `providers.view` + a signed URL; verifies the file path is under the provider's private prefix and exists on disk before streaming.
- All mutations are wrapped in activity logging (uploaded / updated / replaced / reviewed / archived / viewed / downloaded).

---

## D. Frontend Files

### Created

| File | Description |
|------|-------------|
| `resources/js/components/providers/ProviderDocumentSection.vue` | Credentialing documents section: upload form (type, name, issue/expiration dates, file), current-documents table (name, type, version, issued, expires, status badge, actions), version history, new-version replacement, review (approve/reject) panel, archive action, pending/expiring/expired summary cards |

### Modified

| File | Changes |
|------|---------|
| `resources/js/pages/providers/Profile.vue` | Added the `documents` section to the sidebar, completion indicator (at least one current document), and `ProviderDocumentSection` rendering |
| `resources/js/pages/provider-documents/{Index,Create,Edit,Show}.vue` | Standalone documents area pages (list, upload, edit, detail) |

### UI / UX notes

- Reuses existing design-system primitives (`Card`, `Button`, `Input`, `Label`, `Select`, `Textarea`, `Badge`, `InputError`) and the shared `useToast` composable; no new packages.
- Responsive: document table wraps in a horizontally scrollable container; upload/review/replace panels stack on small screens.
- Status badges colour-coded (approved emerald, rejected destructive, pending amber); expiry badges flag Expired / Expires soon / Valid / No expiry.
- Unavailable files show an alert indicator; download links only render when the file exists and a signed URL is available.
- Approve/Reject controls render only for `providers.viewSensitive`; Archive only for `providers.archive`; Upload/New version only for `providers.update`.

---

## E. Relationships

```
Provider ──HasMany──► ProviderDocument (provider_id)
ProviderDocument ──BelongsTo── Provider
ProviderDocument ──BelongsTo── ProviderApplication (provider_application_id)
ProviderDocument ──BelongsTo── User (reviewed_by, uploaded_by, archived_by)
ProviderDocument ──BelongsTo── ProviderDocument (supersedes_id)
ProviderDocument ──HasMany──► ProviderDocument (supersedes_id as versions)
```

Existing relationships preserved.

---

## F. Permissions

No new permissions were added in Phase 13. The section reuses:

- `providers.update` — upload, update metadata, replace (new version)
- `providers.archive` — archive a document
- `providers.viewSensitive` — record review decisions (Approve/Reject)
- `providers.view` — view/download a document (download additionally requires a signed URL)

The sensitive permission is enforced twice: route middleware and form-request layer.

---

## G. Tests

### Test Results

| Suite | Tests | Passed | Failed |
|-------|-------|--------|--------|
| ProviderDocumentTest (new) | 16 | 16 | 0 |
| Full suite | 173 | 173 | 0 |

### ProviderDocumentTest Coverage

1. Super admin can add a document (pending review, current version 1)
2. Viewer cannot add a document (403)
3. Validation requires type, name, and file
4. Expiration date cannot be before issue date
5. Super admin can update document metadata
6. Super admin can archive a document (sets archived_at, is_current = false)
7. Viewer cannot archive a document (403)
8. Activity log recorded on create (`Provider document uploaded`)
9. Activity log recorded on update (`Provider document updated`)
10. Document cannot be updated or archived through another provider's URL (404)
11. Super admin can replace a document version (prior version retired, new version pending)
12. User with `providers.viewSensitive` can approve a document (reviewed_by/at populated)
13. Rejecting a document requires review notes (status stays pending)
14. Viewer cannot review a document (403)
15. Unsigned download is rejected (403)
16. Provider profile page loads with document data

### Build / type check

- `php artisan migrate:status` — both document migrations show **Ran** (batch 17 for the enhance).
- `php artisan route:list --name=providers.documents` — 7 routes registered.
- `php artisan test` — **173 passed, 0 failed**.
- `npm run build` — **passes**.
- `npm run types:check` — no new errors introduced; the 26 pre-existing `vue-tsc` errors in unrelated legacy pages remain unchanged and out of scope.
- `vendor/bin/pint --test` — passes on new files.
- `vendor/bin/phpstan analyse` — no new errors on `ProviderDocumentFactory.php`; test file follows the same `missingType.return` convention as existing section tests.

---

## H. Manual Verification

1. **Login** as a super-admin user.
2. **Navigate** to `/providers/{id}/profile`.
3. **Click** "Documents" in the left sidebar.
4. **Empty state** — confirm the "Upload Document" call to action appears.
5. **Upload** — choose type, name, dates, and a file; save; the document appears as Pending review.
6. **Expiration** — set an expiration date in the past; confirm the Expired badge renders.
7. **Replace** — click "New version" on a document; upload a replacement; the prior version is retired and the new one is Pending review.
8. **Review** — as a user with `providers.viewSensitive`, click Approve/Reject; rejecting without notes shows a validation error; approving populates reviewer metadata.
9. **Archive** — click Archive; the document is marked archived and removed from current documents (file and audit retained).
10. **Download** — click Download; a signed, time-limited URL is generated and the file streams.
11. **Authorization** — as a viewer, upload/update/replace/archive/review return 403; download without a signature returns 403.
12. **Scoping** — a request using another provider's document ID returns 404.
13. **Responsive** — at tablet/mobile width, the table scrolls horizontally and panels stack without overflow.

---

## I. Known Limitations

1. **No admin UI for document types:** the type enum is fixed in the migration/request; adding types requires a code change.
2. **No bulk operations:** no bulk upload, bulk review, or bulk archive.
3. **No restore UI:** archived documents are soft-archived (retained) but there is no restore action.
4. **No automated expiry reminders:** expired/expiring-soon documents are flagged in the UI, but no scheduled reminder emails or tasks are implemented.
5. **Single disk:** files are stored on the local disk; no S3/other disk support in the UI.
6. **Legacy type errors:** 26 pre-existing `vue-tsc` errors remain in unrelated legacy pages. They do not block the production build.

---

## J. Next Phase

**Recommendation:** Phase 14 — (per roadmap)

**Do not start Phase 14 automatically — awaiting approval.**

---

## Files Created (4)

| Category | File |
|----------|------|
| Migration | `database/migrations/2026_11_01_000000_enhance_provider_documents_table.php` |
| Factory | `database/factories/ProviderDocumentFactory.php` |
| Test | `tests/Feature/ProviderDocumentTest.php` |
| Report | `docs/PHASE_13_COMPLETION_REPORT.md` |

## Files Modified (4)

| Category | File |
|----------|------|
| Vue Page | `resources/js/pages/providers/Profile.vue` (Documents section + completion) |
| Vue Pages | `resources/js/pages/provider-documents/{Index,Create,Edit,Show}.vue` (standalone documents area) |
| Vue Component | `resources/js/components/providers/ProviderDocumentSection.vue` |

**Awaiting approval before starting Phase 14.**
