# Frontend & Backend Convergence Checklist

## A) Canonical Rendering and Route Parity
- [x] Serve web pages from `resources/views` via `TemplateRenderService`.
- [x] Map all Auth/Admin/Teacher/Student web routes to view files.
- [x] Add missing route-backed views (`auth/2fa`, admin roles/questions/import/manual).
- [x] Replace admin include shims with concrete admin page implementations.

## B) Backend APIs (complete before frontend binding)
- [x] Add roles/permissions matrix read endpoint (`GET /api/admin/roles-permissions`).
- [x] Add roles/permissions save endpoint (`POST /api/admin/roles-permissions`).
- [x] Add settings read endpoint (`GET /api/admin/settings`).
- [x] Add settings save endpoint (`POST /api/admin/settings`).
- [x] Add manual grading score endpoint (`POST /api/teacher/grading/{answerId}/score`).
- [x] Add student results endpoint (`GET /api/student/results`).
- [x] Add 2FA verify/resend endpoints (`POST /auth/2fa/verify`, `POST /auth/2fa/resend`).
- [x] Convert analytics to filterable API-backed summary (`GET /api/admin/analytics/summary?from=&to=`).
- [x] Add teacher question CRUD endpoints (`GET/POST/PUT/DELETE /api/teacher/questions...`).

## C) Frontend Page Controllers
- [x] Add 2FA page module (`two-factor.js`) with challenge-aware verify/resend.
- [x] Add student results page module (`student-results.js`).
- [x] Add roles-permissions page module (`roles-permissions.js`).
- [x] Add settings page module (`system-settings.js`).
- [x] Add manual grading page module (`manual-grading.js`) with optimistic lock token.
- [x] Add bulk upload page module (`bulk-upload.js`).
- [x] Add analytics page module (`exam-analytics.js`).
- [x] Add question create page module (`question-create.js`) and repository manager wiring.
- [x] Add Authorization bearer token support to shared `ApiClient`.

## D) Workflow hardening
- [x] Add 2FA challenge lifecycle with generated code, expiration, attempt cap, and resend cooldown (mock delivery metadata).
- [x] Add optimistic locking + moderation payload workflow for grading edits (`expected_updated_at`, moderation fields).

## E) Automation and quality gates
- [x] Add E2E flow script scaffold for login -> 2FA -> role pages (`scripts/e2e/checklist-flow.spec.mjs`).
- [x] Add visual snapshot script scaffold for principal dashboards (`scripts/e2e/visual-snapshots.mjs`).
- [x] Execute E2E and visual scripts in CI after dependencies/bootstrap are available.
