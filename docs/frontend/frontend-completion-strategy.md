# Frontend Completion Strategy (Production-Grade)

## 1) Executive Assessment

Current frontend delivery is **partially implemented** and split across two systems:

1. **Runtime UI currently served from static templates** (`UI Template/pages/*.html`) through `TemplateRenderService`.
2. **A parallel PHP view system** exists in `resources/views/*`, but is not consistently the runtime source of truth.

This creates duplicated UI definitions, inconsistent behavior, and high risk of drift.

---

## 2) What Exists Today (Observed)

### Routing and page entry points

- Auth pages: `/`, `/login`, `/login/2fa`
- Admin pages: `/admin/dashboard`, `/admin/exams/create`, `/admin/analytics`, `/admin/roles-permissions`, `/admin/settings`, `/admin/questions`, `/admin/questions/import`, `/admin/grading/manual`
- Student pages: `/student/dashboard`, `/student/exam`, `/student/exams/{sessionId}`, `/student/results`
- Teacher pages: `/teacher/questions`, question create pages, import page, grading page

All of the above are currently rendered via static template passthrough in plugin web services.

### Frontend logic already present

- JS page modules exist (`public/assets/js/pages/*`) for admin dashboard, student dashboard, exam runtime, question manager, login.
- Utility/components exist for API client, timer, question rendering, charts, proctoring.

### Backend APIs already present for UI consumption

- Student overview and exam APIs exist.
- Admin summary/log APIs exist.
- Teacher repository/grading read APIs exist.
- Identity/results integration APIs are present for dual-mode operations.

---

## 3) Gap Analysis (Missing / Partial)

## 3.1 Architecture gaps

1. **Dual view stack conflict**
   - `UI Template/pages` and `resources/views` both define UI, causing implementation ambiguity.
2. **No explicit page bootstrap contract**
   - Static templates mostly include CDN scripts but do not consistently wire module page controllers.
3. **Weak state/session integration in UI**
   - Some pages depend on `localStorage` (`user_id`) rather than authenticated server/user context.
4. **No unified design system governance**
   - Tokens exist, but no component-level contracts/versioning.

## 3.2 Page-level completion gaps

### Auth
- Login flow exists but needs robust token/session UX, error states, lockout messaging, and 2FA page behavior wiring.

### Student
- Dashboard: partial API integration; lacks complete empty/loading/retry patterns and stronger route/state handling.
- Active exam: core runtime exists; requires full resilience coverage (resume, autosave conflicts, network drop handling UX).
- Results: template appears mostly static; missing complete dynamic binding and drill-down interactions.

### Teacher
- Question repository: search/filter/pagination/actions are visually present but functionally partial.
- Question create pages: forms mostly static; validation, save workflow, media upload, and draft/publish lifecycle incomplete.
- Manual grading: partial UI; rubric scoring, keyboard workflows, moderation/review actions not fully productized.

### Admin
- Dashboard: currently maps to log feed, but broader KPI cards/charts/actions are template-heavy and partially dynamic.
- Analytics: chart and breakdown views need real API binding and filter controls.
- Roles/permissions: largely static page; CRUD and matrix editing workflows missing.
- Settings: controls visible but not fully integrated to persistence and audit confirmation flows.
- Bulk upload/import: UI exists, but full preview/commit progress/errors/download reporting needs complete integration.

## 3.3 Component-level gaps

- Table component behavior is duplicated and ad hoc (sorting, filtering, pagination, row actions).
- Form validation and submission patterns are inconsistent by page.
- Notification/toast/error handling is not centralized.
- Modal/dialog patterns and accessibility behavior are inconsistent.
- Chart lifecycle and empty/error/loading states are not standardized.

## 3.4 UX + Accessibility gaps

- Placeholder/dead-link cleanup has started, but remaining pages still need a full audit pass for complete removal.
- Missing keyboard flow guarantees for exam-critical surfaces and grading workflows.
- Incomplete ARIA semantics and focus-management consistency across modals/alerts/navigation.

## 3.5 Production-readiness frontend gaps

- No visual regression suite.
- No explicit E2E critical-path suite.
- No performance budgets or frontend observability hooks (error tracking/web vitals).
- Inconsistent API error contract handling and retry semantics.

---

## 4) Complete Build Strategy (No Surface Left Behind)

## Phase 0 — Convergence Decision (Mandatory)

1. **Choose one rendering system as canonical**:
   - Option A: migrate fully to `resources/views` with shared layouts/partials.
   - Option B: keep `UI Template/pages` but formalize build/bootstrap pipeline.
2. Freeze net-new UI development until canonical path is selected.

**Recommendation:** choose `resources/views` as canonical for maintainability and server-rendered auth/session context.

## Phase 1 — Frontend foundation hardening

1. Implement a **page bootstrap registry**:
   - Route/page key -> JS controller mapping.
2. Standardize shared shell components:
   - header/sidebar/breadcrumb/alerts/loading overlays.
3. Introduce a **unified API response adapter**:
   - normalize success/error payloads, auth failures, validation errors.
4. Add centralized telemetry hooks:
   - JS error capture + API failure metrics + page load timings.

## Phase 2 — Complete core user journeys

### Student (highest risk/priority)
- Dashboard list states + robust routing.
- Exam journey hardening:
  - start/resume,
  - autosave and debounce,
  - reconnect/retry UX,
  - submission confirmation and irreversible action guardrails,
  - proctoring event UX clarity.
- Results page full dynamic rendering and detail drill-down.

### Teacher
- Repository full CRUD action wiring.
- Create flows for all question types with shared validation schema.
- Bulk upload end-to-end: upload -> preview -> validation report -> commit -> audit output.
- Manual grading complete rubric + feedback + submit flow.

### Admin
- Dashboard and analytics fully API-driven.
- Roles/permissions matrix editor with save, diff, rollback messages.
- Settings forms with optimistic/pessimistic save and audit confirmation.

## Phase 3 — Design system + componentization

1. Build reusable primitives:
   - `DataTable`, `FilterBar`, `FormField`, `StatusBadge`, `EmptyState`, `ErrorState`, `ConfirmDialog`, `ToastCenter`.
2. Replace per-page bespoke patterns with shared components.
3. Create UI behavior guidelines and interaction states matrix.

## Phase 4 — Quality gates and production checks

1. Add E2E tests for critical flows:
   - login/logout,
   - student exam submit path,
   - teacher question create/import,
   - admin analytics/settings.
2. Add visual regression snapshots for principal pages.
3. Add performance budgets and bundle/page metrics checks.
4. Enforce accessibility checks (keyboard + automated axe scans).

---

## 5) Delivery Plan (Suggested)

## Sprint 1–2
- Canonical frontend decision + migration scaffolding.
- Shared shell, bootstrap registry, API adapter, telemetry base.

## Sprint 3–4
- Student journey full completion + tests.

## Sprint 5–6
- Teacher journey full completion + tests.

## Sprint 7–8
- Admin journey full completion + tests.

## Sprint 9
- Component consolidation + accessibility/performance hardening.

## Sprint 10
- Stabilization, bug bash, UAT sign-off, go-live checklist.

---

## 6) Definition of Done (Frontend)

A page/view/component is done only if all are true:

1. Fully bound to real API or server state (no placeholder actions).
2. Loading/empty/error/retry states implemented.
3. Accessibility baseline passes (keyboard navigation, ARIA, focus flow).
4. Telemetry events/logging integrated.
5. Covered by unit/integration/E2E tests as applicable.
6. Included in role-based navigation and permission checks.
7. Meets visual consistency against design tokens/components.

---

## 7) Immediate Next Actions (Actionable)

1. Approve canonical rendering path (`resources/views` recommended).
2. Produce page-by-page implementation tracker with owners and ETAs.
3. Start with Student critical path hardening (exam runtime + results).
4. Run weekly architecture review to eliminate duplicate UI surfaces during migration.
