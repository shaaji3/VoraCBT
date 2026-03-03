# Page/View Gap Audit (Updated)

## Backend-first resolution completed

The web rendering pipeline now serves pages from `resources/views` (canonical source), not `UI Template` passthrough.

### What was changed
- Plugin web services now render `resources/views/**/*.php` directly.
- Missing route-backed views were added for:
  - `auth/2fa`
  - `admin/roles-permissions`
  - `admin/questions/index`
  - `admin/questions/import`
  - `admin/grading/manual`
- Teacher grading route now maps to `teacher/grading/manual.php` (with the existing essay view retained as implementation content).

## Current route-to-view parity

### Auth
- `/` -> `resources/views/auth/login.php`
- `/login` -> `resources/views/auth/login.php`
- `/login/2fa` -> `resources/views/auth/2fa.php`

### Student
- `/student/dashboard` -> `resources/views/student/dashboard.php`
- `/student/exam` -> `resources/views/student/exam.php`
- `/student/exams/{sessionId}` -> `resources/views/student/exam.php`
- `/student/results` -> `resources/views/student/results.php`

### Teacher
- `/teacher/questions` -> `resources/views/teacher/questions/index.php`
- `/teacher/questions/create-mcq` -> `resources/views/teacher/questions/create-mcq.php`
- `/teacher/questions/create-fill-blank` -> `resources/views/teacher/questions/create-fill-blank.php`
- `/teacher/questions/create-matching` -> `resources/views/teacher/questions/create-matching.php`
- `/teacher/questions/create-passage` -> `resources/views/teacher/questions/create-passage.php`
- `/teacher/questions/import` -> `resources/views/teacher/questions/import.php`
- `/teacher/grading` -> `resources/views/teacher/grading/manual.php`

### Admin
- `/admin/dashboard` -> `resources/views/admin/dashboard.php`
- `/admin/exams/create` -> `resources/views/admin/exams/create.php`
- `/admin/analytics` -> `resources/views/admin/analytics.php`
- `/admin/roles-permissions` -> `resources/views/admin/roles-permissions.php`
- `/admin/settings` -> `resources/views/admin/settings.php`
- `/admin/questions` -> `resources/views/admin/questions/index.php`
- `/admin/questions/import` -> `resources/views/admin/questions/import.php`
- `/admin/grading/manual` -> `resources/views/admin/grading/manual.php`

## Remaining frontend work

1. Add dedicated page-controller modules for all role pages that still rely on static markup patterns.
2. Normalize shared table/form/modal behavior into reusable frontend components.
3. Complete API wiring for roles/permissions, import progress, grading actions, analytics filters, and results drill-down.
4. Add end-to-end tests for role-critical flows after full API binding.
