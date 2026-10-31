# TrioRCM --- UI/UX Design System & Product Interface Guide

**Version:** 1.0\
**Product:** TrioRCM Provider Credentialing & Enrollment Platform\
**Frontend:** Existing Vue 3 application\
**Backend:** Existing Laravel application

## 1. Design vision

Design TrioRCM as a clean, modern, calm, trustworthy healthcare
operations platform. Help credentialing teams understand provider
status, identify missing information, complete work quickly, and track
deadlines.

The interface should feel like a focused professional SaaS product---not
a generic admin template and not a recreation of the external CAQH
portal.

### Principles

1.  Clarity before decoration.
2.  Consistent patterns across forms, tables, filters, status
    indicators, and actions.
3.  Progressive disclosure: show the most useful information first.
4.  Operational visibility: deadlines, blockers, ownership, and next
    actions must be obvious.
5.  Accessible by default: readable typography, contrast, keyboard
    support, visible focus, and clear validation.
6.  Responsive: optimize desktop staff workflows while keeping tablet
    and mobile usable.
7.  Data integrity: never imply a profile is credentialed, attested,
    verified, or approved unless the corresponding process confirms it.

## 2. Existing-project constraints

This is an existing Laravel + Vue 3 application. Before UI changes,
inspect and reuse:

-   Existing Vue version, build setup, router, and layouts.
-   Existing authentication and permission handling.
-   Existing sidebar, header, and navigation.
-   Existing component library and CSS framework.
-   Existing form, validation, toast, modal, table, and pagination
    components.
-   Existing API client and state management.

**Do not:** - Rebuild authentication, RBAC, teams, notifications, or
activity logs. - Replace Vue or the existing styling system. - Introduce
another component library without approval. - Redesign unrelated
pages. - Duplicate existing components. - Hardcode production data. -
Store sensitive provider data in URLs, browser logs, analytics events,
or unprotected client storage. - Claim CAQH synchronization when records
are manually maintained.

If the application already has a design system, preserve its conventions
and map the visual direction below onto its existing tokens.

## 3. Visual direction

### Overall look

-   Light, neutral application canvas.
-   White or near-white content surfaces.
-   Deep navy/blue for primary navigation and actions.
-   Muted slate supporting text.
-   Restrained semantic status colors.
-   Thin borders and subtle shadows.
-   Medium corner radii.
-   Generous but efficient spacing.
-   Minimal gradients and decoration.
-   Avoid excessive cards, pills, icons, and colored backgrounds.

### Suggested color tokens

These are starting values. Map them to the approved TrioRCM brand
palette and existing CSS variables where appropriate.

  ---------------------------------------------------------------------------
  Token                       Suggested value         Usage
  --------------------------- ----------------------- -----------------------
  `--triorcm-primary`         `#2457A6`               Primary actions, active
                                                      navigation, links

  `--triorcm-primary-hover`   `#1D4789`               Hover

  `--triorcm-primary-soft`    `#EAF1FB`               Selected navigation

  `--triorcm-text`            `#172033`               Main text

  `--triorcm-text-muted`      `#667085`               Supporting text

  `--triorcm-border`          `#E4E7EC`               Borders and dividers

  `--triorcm-surface`         `#FFFFFF`               Cards and forms

  `--triorcm-canvas`          `#F7F8FA`               App background

  `--triorcm-success`         `#16845B`               Completed, active,
                                                      verified

  `--triorcm-success-soft`    `#E8F5EF`               Success background

  `--triorcm-warning`         `#A86408`               Due soon, needs
                                                      attention

  `--triorcm-warning-soft`    `#FFF4DF`               Warning background

  `--triorcm-danger`          `#C43D45`               Overdue, rejected,
                                                      expired

  `--triorcm-danger-soft`     `#FDEBEC`               Error background

  `--triorcm-info`            `#2876B9`               Informational state

  `--triorcm-info-soft`       `#EAF3FB`               Informational
                                                      background
  ---------------------------------------------------------------------------

Use color to communicate meaning, not decoration. Never communicate
status through color alone. Reserve red for genuine errors, overdue
work, expired records, or destructive actions. Profile completeness must
not imply credentialing approval.

## 4. Typography

Use the existing application font if readable and consistent. Otherwise
use:

``` css
font-family: Inter, ui-sans-serif, system-ui, -apple-system,
  BlinkMacSystemFont, "Segoe UI", sans-serif;
```

  Element             Suggested size     Weight
  ----------------- ---------------- ----------
  Page title                24--28px   650--700
  Section title             18--20px        600
  Card title                15--16px        600
  Body                          14px        400
  Form label                13--14px   550--600
  Supporting text           12--13px        400
  Table content             13--14px        400
  KPI value                 26--30px        650

Use sentence case. Avoid all-caps headings.

## 5. Spacing and surfaces

Use a 4px-based scale: `4, 8, 12, 16, 20, 24, 32, 40, 48`.

-   Page padding: 24px desktop, 16px tablet, 12--16px mobile.
-   Use available width for data-heavy pages.
-   Use one or two form columns depending on field length and screen
    width.
-   Group long forms into titled sections.
-   Keep primary actions in a predictable top-right or sticky action
    area.
-   Use 1px neutral borders and subtle shadows only for overlays.
-   Use consistent 8--12px radii.
-   Avoid cards nested inside cards and a separate card for every field.

## 6. Application shell and navigation

Preserve the current shell wherever possible.

### Desktop shell

1.  Sidebar: TrioRCM logo, grouped navigation, active state, role-aware
    links.
2.  Top header: breadcrumbs, global search if available, notifications,
    help if available, user menu.
3.  Main content: page title, short description, primary action,
    filters/tabs, content.

### Recommended navigation

Adapt to existing routes and permissions. Do not show inaccessible links
or active links to unfinished pages.

``` text
Overview
  Dashboard

Provider Management
  All Providers
  Add Provider
  Provider Directory
  Imports

CAQH Management
  CAQH Overview
  Attestation Due
  Follow-ups

Credentialing
  Credentialing Cases
  Verification Queue

Payer Enrollment
  Payer Applications
  Payers
  Enrollment Follow-ups

Documents
  Document Library
  Expiring Documents
  Review Queue

Tasks & Workflows
  My Tasks
  Team Tasks
  Workflow Queue

Reports
  Provider Reports
  CAQH Reports
  Credentialing Reports
  Enrollment Reports

Administration
  Teams
  Users & Roles
  Settings
  Activity Logs
```

## 7. Reusable page patterns

### Listing pages

Use a consistent pattern for providers, CAQH records, documents, tasks,
credentialing cases, and payer applications.

-   Page title, description, and primary action.
-   Search and relevant filters.
-   Clear-filters action.
-   Table with clear headings, sorting where useful, pagination, loading
    and empty states.
-   Consistent row actions.
-   Text plus status badge.

Suggested provider columns: Provider, NPI (permission-controlled),
Specialty, Primary Location, CAQH Status, Profile Completeness, Assigned
To, Provider Status, Updated, Actions.

Do not show every provider field in the table. Put details on the
profile page.

### Detail pages

Use breadcrumbs, entity name and key identifier, current status, primary
action, secondary actions, summary information, tabs/section navigation,
and activity history where relevant.

### Create/edit forms

-   Explicit labels; placeholders are not labels.
-   Clearly mark required fields.
-   Put help text beside the relevant field.
-   Group related fields.
-   Use correct input types and searchable selects for long lists.
-   Show inline validation.
-   Preserve values after validation errors.
-   Prevent duplicate submissions.
-   Indicate saving/saved state.
-   Warn before discarding unsaved changes where feasible.

### Destructive actions

Confirm archive, delete, revoke, and irreversible actions. Explain the
consequence. Prefer archive/deactivate over permanent deletion where
retention is required. Enforce every action on the backend.

## 8. Provider Management interface

### Provider directory

Prioritize finding and triaging providers.

Suggested filters: - Provider status. - Specialty. - Assigned team and
user. - CAQH status. - Profile completeness. - State. - Missing
information. - Expiring credentials.

Suggested row actions: View profile, Edit, Assign, Open CAQH tracking,
More actions.

### Provider detail header

Display provider name, NPI when authorized, provider type, primary
specialty, operational status, assigned team/owner, last updated date,
and primary action.

Do not expose DOB, sensitive identifiers, or private contact information
in the header without a clear need and permission.

### Profile section navigation

Use a left-side section navigation on desktop and compact select/menu on
smaller screens.

1.  Overview
2.  Personal Information
3.  Professional IDs
4.  Education & Training
5.  Specialties
6.  Practice Locations
7.  Hospital Affiliations
8.  Credentialing Contacts
9.  Liability Insurance
10. Employment
11. Professional References
12. Disclosure
13. Documents
14. Authorization
15. CAQH Tracking
16. Activity History

Each section should show: Not started, In progress, Complete, Needs
attention, or Not applicable. Include accessible text, not color alone.

### Provider overview

Show provider identity and specialty, profile completion, missing
information count, documents needing review, upcoming expiries, next
CAQH attestation date, open tasks, and recent activity.

Use visual hierarchy rather than many equal-sized KPI cards.

## 9. Profile section forms

Each section should contain:

-   Section title and short explanation.
-   Completion/attention status.
-   Grouped form fields.
-   Two columns for short related fields on wide screens; one column for
    long fields and narrow screens.
-   Consistent save/cancel actions.
-   Inline validation and save feedback.

For repeating records (licenses, education, specialties, locations,
affiliations, insurance, employment, references), show concise record
summaries with Add/Edit/Archive actions. Use a drawer, modal, or inline
editor according to existing project patterns. Avoid one huge form for
all repeating records.

## 10. CAQH tracking interface

CAQH tracking is an internal operational record unless an authorized
integration is implemented.

### CAQH overview

Show CAQH ID subject to permissions, tracking status, last checked date,
last attested date, next attestation due date, assigned staff member,
last/next follow-up, and outstanding checklist items.

### Attestation countdown

Use clear wording: - Due in 30 days - Due today - Overdue by 5 days - No
due date recorded

Do not assume an attestation cycle unless the business has approved the
rule. Store the due date or use a verified configurable rule.

### Checklist

Each item should show section name, status, last checked date, assigned
user if relevant, issue summary, evidence link if applicable, and update
action.

### Attestation history

Use a chronological timeline or compact table with attestation date,
recorded by, evidence/reference, next due date, and notes. Distinguish
manual verification from an authorized integration event.

### Follow-up queue

Prioritize Overdue, Due today, Due soon, Scheduled, and Completed. Show
owner, due date, last outcome, and next action.

## 11. Documents interface

Suggested columns: Document, Category, Provider, Issue Date, Expiration
Date, Review Status, Uploaded By, Updated, Actions.

Document detail should show metadata, version/review history, expiry
information, related provider and profile section, and authorized
preview/download.

Upload experience: - Show allowed types and maximum size. - Provide
browse; drag-and-drop only if supported. - Show progress and clear
validation errors. - Confirm successful upload. - Never expose storage
paths or permanent public URLs.

Expiration states: Valid, Expiring Soon, Expired, No Expiration Date.
Use configurable warning windows; not every document type expires.

## 12. Dashboard and operational queues

The dashboard should answer: 1. What needs attention today? 2. Which
work is overdue? 3. Who owns it? 4. What is coming due? 5. What is
progressing?

Recommended sections: My Tasks, Overdue Tasks, CAQH Attestations Due,
Expiring Documents, Profiles with Missing Information, Recently Updated
Providers, Team Workload (permission-controlled).

Prefer actionable lists over decorative charts. Metrics should link to
filtered lists where possible. Apply permission-aware data scoping. Do
not show placeholder analytics in production.

## 13. Status design

  Meaning                  Appearance     Example labels
  ------------------------ -------------- ------------------------------
  Complete/verified        Subtle green   Complete, Verified, Attested
  In progress              Subtle blue    In Progress, Processing
  Needs attention          Subtle amber   Needs Information, Due Soon
  Failed/overdue/expired   Subtle red     Overdue, Expired, Rejected
  Inactive/on hold         Gray           Inactive, On Hold
  Informational            Neutral/blue   Pending Review, Submitted

Status names must reflect real business states. "Verified", "Attested",
and "Approved" may only be used after the corresponding event. Profile
completion is separate from credentialing and enrollment decisions.

## 14. Interaction and feedback

-   Primary button: main page action.
-   Secondary button: supporting action.
-   Tertiary/ghost: low-emphasis action.
-   Destructive: harmful or irreversible action.
-   Avoid multiple competing primary buttons.
-   Use existing toast/notification patterns for successful saves,
    uploads, assignments, and status updates.
-   Use inline errors for validation.
-   Use confirmation dialogs for consequential actions.
-   Use skeletons for initial loading if supported.
-   Use button-level loading for submissions.
-   Prevent duplicate submissions.
-   Every list/panel needs a useful empty state explaining what is empty
    and what the user can do next.

## 15. Responsive behavior

### Desktop

-   Full sidebar.
-   Multi-column forms where appropriate.
-   Full data tables.
-   Persistent page actions where useful.

### Tablet

-   Collapsible sidebar.
-   Reduced page padding.
-   One or two form columns based on width.
-   Horizontal table scrolling or prioritized columns.

### Mobile

-   Compact header and collapsed navigation.
-   Single-column forms.
-   Stacked summary information.
-   Deliberate table-to-card or horizontal-scroll strategy.
-   Reachable actions and comfortable touch targets.
-   No horizontal page overflow.

## 16. Accessibility

Target WCAG 2.2 AA practices where feasible.

-   Semantic headings in correct order.
-   Programmatic labels for all fields.
-   Keyboard access to menus, dialogs, tabs, and forms.
-   Visible focus indicators.
-   Sufficient text/control contrast.
-   Status not conveyed by color alone.
-   Correct dialog focus management.
-   Validation errors associated with fields.
-   Meaningful accessible names for buttons.
-   Appropriate table headers.
-   Respect reduced-motion preferences.
-   Avoid tiny text and overly dense controls.

## 17. Privacy and security UX

Provider data may contain sensitive personal and professional
information.

-   Hide sensitive fields without permission.
-   Mask sensitive identifiers where appropriate.
-   Do not reveal sensitive values in notifications or email subjects.
-   Do not put sensitive information in query strings.
-   Do not show storage paths.
-   Use authenticated, authorized document access.
-   Avoid browser-side persistence of sensitive profile data.
-   Distinguish internal notes from provider-visible content if a
    provider portal is added later.
-   Confirm sensitive changes and authorization revocation.
-   Log access and changes according to the approved audit policy.

The UI alone is not proof of HIPAA or other regulatory compliance.
Assess security, contractual, operational, and legal controls
separately.

## 18. Reusable component inventory

Search the repository for equivalents before creating anything.

  Component             Responsibility
  --------------------- -------------------------------------------
  `PageHeader`          Title, description, actions
  `Breadcrumbs`         Page hierarchy
  `DataTable`           Sorting, pagination, empty/loading states
  `FilterBar`           Search and filters
  `StatusBadge`         Consistent status labels
  `SectionNavigation`   Provider section navigation
  `SectionHeader`       Section title, description, status
  `FormField`           Label, help, error
  `DateField`           Date input
  `SearchableSelect`    Long option selection
  `ConfirmDialog`       Consequential action confirmation
  `EmptyState`          No-data state
  `LoadingState`        Loading feedback
  `ActivityTimeline`    Activity presentation
  `DocumentUploader`    Upload interaction
  `DocumentTable`       Document listing
  `ProgressIndicator`   Profile completion
  `ReminderSummary`     Due date/countdown
  `RecordEditor`        Repeating records

Do not create components for every tiny visual element. Favor useful
reusable patterns.

## 19. Suggested Vue structure

Adapt this to the repository's existing conventions. Do not move files
solely to match this example.

``` text
resources/js/
  components/
    common/
      PageHeader.vue
      StatusBadge.vue
      EmptyState.vue
      ConfirmDialog.vue
      FilterBar.vue
    providers/
      ProviderProfileLayout.vue
      ProviderSectionNavigation.vue
      ProviderSummary.vue
      ProviderProgress.vue
      sections/
        PersonalInformationSection.vue
        ProfessionalIdsSection.vue
        EducationSection.vue
        SpecialtiesSection.vue
        PracticeLocationsSection.vue
        HospitalAffiliationsSection.vue
        CredentialingContactsSection.vue
        LiabilityInsuranceSection.vue
        EmploymentSection.vue
        ReferencesSection.vue
        DisclosureSection.vue
        DocumentsSection.vue
        AuthorizationSection.vue
        CaqhTrackingSection.vue
  pages/
    providers/
      ProviderIndex.vue
      ProviderCreate.vue
      ProviderShow.vue
      ProviderEdit.vue
  services/
    providerService.js
```

## 20. Design acceptance checklist

### Visual consistency

-   [ ] Existing application shell is preserved.
-   [ ] Approved brand colors and tokens are used.
-   [ ] Typography and spacing are consistent.
-   [ ] Status colors have consistent meaning.
-   [ ] No unnecessary nested cards or decoration.

### Usability

-   [ ] Page title and purpose are clear.
-   [ ] Primary action is obvious.
-   [ ] Forms have labels and useful validation.
-   [ ] Loading, empty, success, and error states exist.
-   [ ] Navigation is predictable.
-   [ ] Next action is understandable.

### Responsive

-   [ ] Desktop, tablet, and mobile layouts work.
-   [ ] No horizontal page overflow.
-   [ ] Controls are touch-usable.

### Accessibility

-   [ ] Keyboard navigation works.
-   [ ] Focus is visible.
-   [ ] Fields have labels.
-   [ ] Contrast is sufficient.
-   [ ] Status is not communicated by color alone.

### Data and security

-   [ ] UI respects backend permissions.
-   [ ] Sensitive fields are protected.
-   [ ] Documents use authorized routes.
-   [ ] Sensitive data is not logged to the browser console.
-   [ ] Status labels reflect backend state.

## 21. Implementation workflow

Implement the design in small, reviewable steps.

### Step 1 --- UI audit

Inspect and document current design system, layout, navigation,
components, CSS tokens, form/table patterns, responsive breakpoints, and
icon library. Do not change files.

### Step 2 --- Design mapping

Create `docs/UI_DESIGN_AUDIT.md` with reusable components, extensions,
missing components, token mapping, provider navigation, and files likely
to change. Wait for approval.

### Step 3 --- Foundation

Implement only approved shared foundations: tokens if needed, page
header, status badge, form field, and empty/loading states. Do not
redesign the entire application.

### Step 4 --- Provider directory

Implement the listing UI using real backend data and existing table/API
conventions.

### Step 5 --- Provider detail shell

Implement provider header, summary, and section navigation.

### Step 6 --- Profile sections

Implement one section at a time, matching backend phases and acceptance
criteria.

### Step 7 --- CAQH and documents

Implement dedicated operational interfaces after backend contracts are
ready.

### Step 8 --- Responsive and accessibility review

Test at desktop, tablet, and mobile widths.

### Step 9 --- Regression review

Confirm authentication, RBAC, teams, notifications, activity logs, and
unrelated pages still work.

## 22. Prompt for the coding agent

Read `DESIGN.md` completely before making UI changes.

First inspect the existing Vue 3 codebase and create
`docs/UI_DESIGN_AUDIT.md`. Identify the current layout, component
library, CSS framework, design tokens, router, API client, forms,
tables, icons, and responsive patterns.

Do not modify files during the audit.

After the audit, propose how the TrioRCM design direction in `DESIGN.md`
maps to the existing application. Reuse existing components and
conventions wherever possible.

Implement the provider interface incrementally, one approved feature at
a time. Do not redesign the entire application or introduce a new
frontend framework or component library without approval.

Use real API data, backend validation, and existing RBAC. Never use fake
production data or expose sensitive provider information.

After each feature, report changed files, visual behavior, responsive
behavior, accessibility checks, permissions, tests, and remaining work.
Stop and wait for approval before starting the next feature.

Begin with the UI audit only.

## 23. Definition of done

Staff can use a consistent, responsive interface to: - Find and open
providers. - Understand provider status and ownership. - Complete and
update profile sections. - Identify missing information. - Review
documents and expiry dates. - Track CAQH status and attestation
deadlines. - Record follow-ups and next actions. - Access only records
and fields permitted by their roles. - Understand the difference between
profile completeness, CAQH attestation, credentialing verification, and
payer enrollment approval.

**Final principle:** Build a calm, efficient operations workspace. Every
screen should make the user's next action clearer, not simply display
more information.
