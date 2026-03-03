# Frontend Page Inventory & Consistency Audit

## Scope
Route-backed pages in current web plugins:
- Auth
- Admin
- Teacher
- Student

## Page inventory

### Auth pages
1. `/` -> login home (`resources/views/auth/login.php`)
2. `/login` -> login (`resources/views/auth/login.php`)
3. `/login/2fa` -> 2FA (`resources/views/auth/2fa.php`)

### Admin pages
1. `/admin/dashboard` -> `resources/views/admin/dashboard.php`
2. `/admin/exams/create` -> `resources/views/admin/exams/create.php`
3. `/admin/analytics` -> `resources/views/admin/analytics.php`
4. `/admin/roles-permissions` -> `resources/views/admin/roles-permissions.php`
5. `/admin/settings` -> `resources/views/admin/settings.php`
6. `/admin/questions` -> `resources/views/admin/questions/index.php`
7. `/admin/questions/import` -> `resources/views/admin/questions/import.php`
8. `/admin/grading/manual` -> `resources/views/admin/grading/manual.php`

### Teacher pages
1. `/teacher/questions` -> `resources/views/teacher/questions/index.php`
2. `/teacher/questions/create-mcq` -> `resources/views/teacher/questions/create-mcq.php`
3. `/teacher/questions/create-fill-blank` -> `resources/views/teacher/questions/create-fill-blank.php`
4. `/teacher/questions/create-true-false` -> `resources/views/teacher/questions/create-true-false.php`
5. `/teacher/questions/create-drag-drop` -> `resources/views/teacher/questions/create-drag-drop.php`
6. `/teacher/questions/create-case-study` -> `resources/views/teacher/questions/create-case-study.php`
7. `/teacher/questions/create-matching` -> `resources/views/teacher/questions/create-matching.php`
8. `/teacher/questions/create-passage` -> `resources/views/teacher/questions/create-passage.php`
9. `/teacher/questions/import` -> `resources/views/teacher/questions/import.php`
10. `/teacher/grading` -> `resources/views/teacher/grading/manual.php`

### Student pages
1. `/student/dashboard` -> `resources/views/student/dashboard.php`
2. `/student/exam` and `/student/exams/{sessionId}` -> `resources/views/student/exam.php`
3. `/student/results` -> `resources/views/student/results.php`

## UX consistency updates implemented

1. **Unified shell interactions**
   - Added shared script `public/assets/js/pages/ui-shell.js` for:
     - active nav highlighting
     - notification “mark all as read” behavior
     - logout confirmation

2. **Topbar consistency**
   - Admin/teacher topbar now includes:
     - notification dropdown panel
     - profile dropdown with quick actions
     - clear action affordances

3. **Student header consistency**
   - Student header now mirrors key shell controls:
     - notification dropdown
     - profile dropdown

4. **Sidebar consistency**
   - Added route prefix metadata for deterministic active-state highlighting.
   - Standardized logout entry using shared confirmation flow.

5. **Feedback affordance**
   - Added shell-level toast in app/student layouts for lightweight feedback.

## Remaining follow-ups
- Add page-level QA screenshots after full runtime environment (vendor dependencies) is available.
