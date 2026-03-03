# In-Page Action Matrix (No Full Reload Policy)

This document implements the agreed UX policy:
- **No full-page reload** for within-page actions.
- **Allow redirect/reload** only for page navigation and security/session boundaries.

## Policy buckets

### A) In-page actions (AJAX / no reload)
- Save/update forms
- Delete/archive rows
- Preview/commit imports
- Mark notifications read
- Inline helper links where pages are not yet available
- Filter/search/sort/paginate updates where possible

### B) Boundary transitions (redirect allowed)
- Login success / 2FA completion
- Logout success
- Unauthorized/session-expired redirect to login
- Explicit route navigation to another page

## Implemented mapping in current frontend

| Area | Action | Behavior |
|---|---|---|
| Question repository | Search | In-page filter + URL query sync (`?q=`), no reload |
| Question repository | Archive question | API delete + table refresh + toast, no reload |
| Teacher question create | Save question | API post + inline status, no reload |
| Teacher question import | Preview/Commit | API multipart + inline status, no reload |
| Admin settings | Save settings | API post + inline status, no reload |
| Manual grading | Save score | API post + inline status/list refresh, no reload |
| Exam template builder | Save/Cancel draft and question-card edits | In-page local draft persistence and card actions, no reload |
| Admin dashboard | Session log action button | In-page modal timeline with filtered log fetch, no reload |
| Shell notifications | Fetch + mark read | Backend-fed in-page list (admin logs / pending grading / student overview) + mark-read reset, no reload |
| Utility links | Privacy/Terms/Forgot/Support | Route to dedicated pages (navigation), no placeholder links |
| Auth success/logout/session expiry | Login/2FA/Logout/401 handler | Redirect (boundary flow) |

## Why this is implemented this way

1. **Task continuity**: teachers/admins keep context while performing repeated operations.
2. **Performance**: reduced unnecessary document reloads for command interactions.
3. **Reliability**: auth/security boundaries still use redirects where session state changes are expected.
4. **Usability**: URL query sync keeps search state shareable and back-button friendly.
