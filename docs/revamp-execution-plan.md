# VoraCBT Complete Revamp Execution Plan

This plan is based on a full repository walkthrough (application, views, assets, routes, migrations, infrastructure scripts, and tests) and aligns implementation with the `design.md` target architecture while replacing the current UI with the provided `UI Template/` assets.

## 1) Current-State Audit Summary

### Architecture and backend
- The project already has a custom core (`app/Core`) with HTTP, container, DB, and service foundations.
- Domain logic is present but split across multiple patterns:
  - `app/Domain/*` (Exam, Grading, Proctoring, Monitoring, Identity, Integration)
  - Legacy/question-specific stack in `app/Question/*`
  - Transport/controllers in `app/Http/Controllers/*`
- Routing is centralized in `routes/web.php` and `routes/api.php`, and bootstrapped by `public/index.php`.
- Infra and operations are present (`scripts/ops`, `deploy/*`, `docs/operations/*`).

### UI and presentation layer
- Existing UI is PHP include-based in `resources/views/*` with legacy layouts/partials.
- Existing static assets live under `public/assets/css` and `public/assets/js`.
- New target UI exists in `UI Template/` with:
  - Shared stylesheet system (`tokens.css`, `components.css`, `layout.css`, `cbt.css`)
  - Page templates for login, dashboard, exam workflows, question workflows, analytics, grading, settings.

### Data and quality coverage
- Database has substantial migration coverage and seeders.
- Test suite covers domain logic and middleware broadly (`tests/Domain`, `tests/Http`, `tests/Integration`, `tests/Unit`, `tests/Question`).

---

## 2) Target Architecture to Enforce (from `design.md`)

We will migrate to strict modular plugin architecture:

- Introduce `app/Plugins/*` as first-class feature modules.
- Keep `app/Core` as framework engine and define/expand contract-driven boundaries.
- Move feature routes into plugin-local route files.
- Move UI templates into plugin-scoped views plus shared global layout/components.
- Scope plugin assets under `public/assets/plugins/{PluginName}/`.

### Initial plugin map for VoraCBT
- `app/Plugins/Auth`
- `app/Plugins/Exam`
- `app/Plugins/QuestionBank`
- `app/Plugins/Grading`
- `app/Plugins/Monitoring`
- `app/Plugins/Identity`
- `app/Plugins/Settings`
- `app/Plugins/Integration`

---

## 3) Full-System Revamp Strategy

## Phase 0 — Baseline freeze and branch hygiene
1. Freeze feature changes on current branch.
2. Capture baseline test results and key user journeys (login, dashboard, exam start, submit, results).
3. Introduce a revamp feature flag (`APP_REVAMP_UI`, `APP_PLUGIN_ROUTING`) to allow safe rollout.

## Phase 1 — Foundation restructuring (no behavior change)
1. Create `app/Plugins` skeleton and module manifests.
2. Add plugin boot/registration in core bootstrap.
3. Introduce plugin service providers and route discovery.
4. Keep old routes active while plugin routes are mirrored.

Deliverable: app runs with old behavior while plugin container/registration exists.

## Phase 2 — UI replacement with `UI Template`
1. Promote template shared assets:
   - Copy from `UI Template/css/*` to new canonical path (`public/assets/css/revamp/*` or plugin-scoped directories).
   - Move `UI Template/js/dark-mode.js` to shared asset pipeline.
2. Replace legacy layouts in `resources/views/layouts/*` with revamp shells mapped from template structure.
3. Rebuild each existing page to template equivalents:
   - login → `index.html`
   - admin dashboard → `pages/admin-dashboard.html`
   - exam flow → `pages/exam-selection.html`, `pages/active-exam.html`
   - results → `pages/student-results.html`
   - question creation/repository/import pages → respective `create-*`, `question-repository`, `bulk-upload`
   - grading/analytics/settings/roles pages → corresponding templates.
4. Remove old custom styling conflicts (`public/assets/css/custom.css`) progressively after page parity.

Deliverable: all visible screens follow the new template look/interaction model.

## Phase 3 — Move features into plugins
1. Migrate controllers/services from legacy locations into plugin modules.
2. Create per-plugin route files and service providers.
3. Introduce plugin-level view folders and plugin-scoped assets.
4. Keep compatibility adapters for temporary namespace bridging to avoid big-bang breaks.

Deliverable: functional parity with pluginized internals.

## Phase 4 — Domain consolidation and cleanup
1. Merge overlapping question systems (`app/Question` and `app/Domain/Question`) into single QuestionBank plugin domain.
2. Normalize service boundaries:
   - Controller orchestration only
   - Business logic in services
   - DTOs/events clearly typed
3. Remove dead code, obsolete layouts/partials, and duplicate JS components.

Deliverable: single authoritative codepath per capability.

## Phase 5 — Hardening and release
1. Update/add tests for new routes/views and module integration.
2. Run regression suite and add smoke checks for every high-risk workflow.
3. Update deployment docs and runbook for plugin loading and asset paths.
4. Roll out in stages (internal → staging → production), with feature-flag fallback.

Deliverable: production-ready revamp with rollback controls.

---

## 4) UI Template Adoption Matrix

- Auth/Login: `UI Template/index.html`, `UI Template/pages/2fa.html`
- Admin: `admin-dashboard.html`, `exam-analytics.html`, `roles-permissions.html`, `system-settings.html`
- Exam operations: `exam-template.html`, `exam-selection.html`, `active-exam.html`
- Questioning: `question-repository.html`, `create-mcq.html`, `create-fill-blank.html`, `create-matching.html`, `create-passage.html`, `bulk-upload.html`
- Grading/Results: `manual-grading.html`, `student-results.html`

Rule: all legacy view files should either be migrated to template-compliant implementations or removed.

---

## 5) Detailed Work Breakdown (Recommended Order)

1. **Scaffold plugins + registration**
2. **Introduce shared revamp layout and asset loading**
3. **Migrate login/auth pages**
4. **Migrate admin dashboard and analytics**
5. **Migrate exam runner and student dashboard/results**
6. **Migrate question workflows**
7. **Migrate grading/settings/roles pages**
8. **Switch routing to plugin routes as default**
9. **Delete legacy UI and stale JS/CSS**
10. **Final full regression + release**

---

## 6) Risks and Mitigations

- **Risk: route breakage during migration**
  - Mitigation: dual routing layer and feature flags.
- **Risk: CSS collisions between legacy and new UI**
  - Mitigation: namespaced revamp assets and staged page cutover.
- **Risk: duplicated logic during plugin transition**
  - Mitigation: adapter interfaces and deadline-based deprecation removal.
- **Risk: incomplete test coverage for UI transitions**
  - Mitigation: add HTTP smoke tests per migrated route and manual screenshot checklist.

---

## 7) Definition of Done for “Complete Revamp”

- All user-facing pages are rendered from `UI Template`-aligned views.
- No legacy layout/partial pipeline remains in active routes.
- Primary business capabilities are organized under `app/Plugins/*`.
- Route ownership is plugin-local, with clean web/API separation.
- Existing critical tests pass, with new tests for plugin routing and key UX flows.
- Deployment/ops docs reflect new structure and asset conventions.
