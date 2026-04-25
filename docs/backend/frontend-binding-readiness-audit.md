# Backend Readiness Audit for Frontend Binding

**Audit date:** 2026-04-25

## Scope

This audit reviews whether the backend is ready for API/UI integration work across Auth, Admin, Teacher, and Student front-end surfaces.

## Verdict

**Status: Conditionally ready (Go with guardrails).**

The core API surface for frontend binding exists and is route-addressable, with RBAC guards and JSON response helpers in place. However, there are a few pre-binding risks that should be handled before broad frontend integration starts in shared environments.

## What is ready now

1. **Route coverage for frontend-facing modules is present.**
   - Admin API exposes dashboard, analytics, roles/permissions, settings, questions summary, pending grading, import, export, and logs routes.
   - Teacher API exposes question repository CRUD/import and manual grading endpoints.
   - Student API exposes dashboard/results, exam lifecycle (fetch/save/submit), proctoring events, autosave, and resume-state.

2. **Authorization model is consistently applied for protected APIs.**
   - Role-based authorization wraps API service methods through `RouteAuthorizer` + auth/role middleware checks.

3. **Frontend-critical workflows are implemented server-side.**
   - Optimistic locking for question updates and manual grading saves.
   - Session token verification for student answer save, submit, autosave, and resume-state.
   - 2FA challenge lifecycle is implemented (create/verify/resend with expiry/attempt/cooldown constraints).

4. **Production baseline protections are present in front controller.**
   - Security headers and optional HTTPS enforcement are configured.
   - Global API rate limiting and web CSRF middleware are wired.

## Risks to address before broad frontend binding

### P1 — Plugin API routing can be disabled by default in `routes/api.php`

`routes/api.php` registers plugin API routes only when `APP_PLUGIN_ROUTING === 'true'`, but defaults that check to `'false'`. This can make `/api/admin/*`, `/api/teacher/*`, and `/api/student/*` appear missing if env setup is incomplete.

**Impact on frontend binding:** frequent 404s during integration and environment drift between local/staging.

**Recommendation:** make plugin API routing default to enabled, or fail fast with startup diagnostics if disabled in an environment expected to serve frontend-bound API modules.

### P1 — 2FA delivery is explicitly mock-only

Auth login/2FA responses label delivery as `mock`, with challenge management kept in session state.

**Impact on frontend binding:** UI flow can be built, but real verification delivery channels (SMS/email/TOTP provider) are not production-ready.

**Recommendation:** keep mock mode for local/testing, but add a provider abstraction and an environment-gated non-mock implementation before UAT.

### P1 — Automated test execution is blocked in this environment

Unit/integration tests could not be executed because dependencies are not installed and package download is blocked (GitHub 403 from this runtime).

**Impact on frontend binding:** readiness confidence is based on static/code audit instead of runtime validation.

**Recommendation:** run `composer install` and `phpunit` in CI or a network-enabled dev runner before binding freeze.

### P2 — API response envelope contract should be confirmed with frontend team

`ApiResponse::json()` wraps payloads under `data` and includes `status` at top-level. This is consistent server-side but should be explicitly mirrored in frontend API client normalization.

**Impact on frontend binding:** mild; can cause parsing bugs if UI expects bare payloads.

**Recommendation:** publish one small API contract doc (or OpenAPI stub) for the envelope + error shape.

## Recommended binding sequence

1. **Environment sanity first**
   - Ensure `APP_PLUGIN_ROUTING=true` and auth secrets exist (`JWT_SECRET`, plus integration secrets if needed).
2. **Bind low-risk read surfaces first**
   - Admin dashboard/analytics, teacher repository list, student dashboard/results.
3. **Bind write flows with concurrency handling**
   - Teacher question update + grading save (both need optimistic-lock handling in UI).
4. **Bind exam runtime flows last**
   - Student answer save/submit/autosave/resume + proctoring events.
5. **Run pre-release smoke suite**
   - auth → 2FA → role dashboards → question CRUD → grading save → exam session submit.

## Go/No-Go checklist for frontend start

- [ ] `APP_PLUGIN_ROUTING=true` enforced in all frontend-bound envs.
- [ ] Frontend `ApiClient` expects `{ data, status }` envelope and structured errors.
- [ ] 2FA scope decision made: mock-only (dev) vs provider-backed (UAT/prod).
- [ ] CI green on PHPUnit in a network-enabled runner.
- [ ] Shared endpoint matrix signed off by FE + BE leads.

## Evidence reviewed

- API route files and API service implementations for Admin/Teacher/Student/Auth.
- Front controller middleware/security wiring.
- Existing frontend convergence checklist and integration tests for route dispatch.
