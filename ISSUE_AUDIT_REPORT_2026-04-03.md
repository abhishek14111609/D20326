# DUOS Admin Panel Issue Audit Report
Date: 2026-04-03
Scope: Investigation only (no code changes applied)

## Summary
All 9 reported issues were reviewed against current Laravel routes, controllers, and Blade files. Most failures are caused by route/method mismatches, placeholder frontend code, and malformed/unfinished form implementations.

---

## 1) Profile section: last login time is wrong
Status: Confirmed (likely timezone/display mismatch)

Evidence:
- `config/app.php` sets application timezone to `UTC`.
- `resources/views/admin/profile.blade.php` displays raw `$admin->last_login_at->format('M d, Y H:i:s')`.
- `app/Http/Controllers/Admin/AuthController.php` updates `last_login_at` with `now()` on login.

Why this can appear wrong:
- If admins expect local time (e.g., IST), storing/displaying in UTC without explicit conversion will show a shifted timestamp.

Recommendation:
- Display timestamps in intended business/user timezone (or set app timezone accordingly).
- Keep DB in UTC if preferred, but convert at display time.

---

## 2) Create new user (Users tab): MethodNotAllowed exception
Status: Confirmed

Evidence:
- `resources/views/admin/users/create.blade.php` form uses `action="#"` with `method="POST"`.
- Current URL becomes `/admin/users/create`, so submit attempts `POST /admin/users/create`.
- In `routes/web.php`, `users` group has `GET /create`, `GET /{user}`, `PUT /{user}`, `DELETE /{user}`; there is no `POST /admin/users/create` and no `users.store` route.

Root cause:
- Form posts to the create page itself; route only allows GET there.

Recommendation:
- Add a dedicated `store` route and controller method; submit form to that route.

---

## 3) Edit Competition returns error
Status: Confirmed

Evidence:
- `app/Http/Controllers/Admin/CompetitionController.php` in `edit()` calls:
  `->withPivot(['status', 'type'])`
- `app/Models/Competition.php` defines `participants()` as `hasMany(CompetitionParticipant::class)`.

Root cause:
- `withPivot()` is valid for `belongsToMany`, not `hasMany`.
- This matches the observed runtime error: `Call to undefined method ...HasMany::withPivot()`.

Recommendation:
- Either:
  - Change relation strategy to `belongsToMany(User::class, 'competition_participants', ...)` where pivot data is needed, or
  - Keep `hasMany` and remove `withPivot()`, selecting columns directly from `competition_participants`.

---

## 4) Cancel Challenge button returns MethodNotAllowed
Status: Confirmed

Evidence:
- `resources/views/admin/challenges/index.blade.php` cancel form submits to `route('admin.challenges.cancel', $challenge)` and includes `@method('PATCH')`.
- `routes/web.php` defines cancel route as `POST /{challenge}/cancel`.
- `app/Http/Controllers/Admin/ChallengeController.php` has no `cancel()` method implemented.

Root cause:
- Method mismatch (`PATCH` request sent to `POST` route).
- Even after method fix, controller action is currently missing.

Recommendation:
- Align HTTP method between form and route, and implement `cancel()` in controller.

---

## 5) My Profile > Edit profile submit button not working
Status: Confirmed

Evidence:
- `resources/views/admin/profile.blade.php` edit modal form:
  `form id="editUserForm" ... onsubmit="return false"`
- No `action` URL and no JS submit handler to send data.

Root cause:
- Form is intentionally blocked from submitting and has no backend wiring.

Recommendation:
- Connect form to a real update endpoint (action + method), or add AJAX handler and backend API.

---

## 6) Change password popup closes/disappears when clicking fields
Status: Confirmed

Evidence:
- `resources/views/admin/profile.blade.php` has malformed HTML in change-password modal:
  `<div class="modal-body` (missing closing quote and `>`).
- Same form also has `onsubmit="return false"` and no submission wiring.

Root cause:
- Broken modal DOM structure can cause unstable modal behavior/focus handling.

Recommendation:
- Fix modal markup structure first, then wire submit to password update endpoint.

---

## 7) Add new plan from membership returns error (`features.0 must be a string`)
Status: Confirmed

Evidence:
- `app/Http/Controllers/Admin/MembershipController.php` validation rules:
  - `'features' => 'nullable|array'`
  - `'features.*' => 'string|max:255'`
- `resources/views/admin/pages/membership-plans/create.blade.php` always renders at least one `features[]` input.

Root cause:
- Empty `features[]` inputs are converted to `null` by Laravel request middleware; `features.*` then fails strict `string` validation.

Recommendation:
- Make `features.*` nullable or pre-filter empty/null feature items before validation.

---

## 8) Report > Export PDF functionality missing
Status: Confirmed

Evidence:
- `resources/views/admin/reports/users.blade.php` export button JS only shows:
  `alert('Export to PDF functionality would be implemented here');`
- `routes/web.php` report routes only include `/users` and `/system`; no export endpoint exists.

Root cause:
- Feature is a placeholder in UI; backend endpoint/service not implemented.

Recommendation:
- Add export route + controller action (PDF generation) and connect button to actual download flow.

---

## 9) Create new admin from Account section returns MethodNotAllowed
Status: Confirmed

Evidence:
- `resources/views/admin/layouts/sidebar.blade.php` “Add Admin” links to `route('admin.users.create')`.
- That page is the same `resources/views/admin/users/create.blade.php` form posting to `#` (`POST /admin/users/create`).

Root cause:
- Same underlying issue as #2 (no proper store route/action for create form).

Recommendation:
- Reuse/fix user/admin creation flow with valid store route and submission target.

---

## Consolidated Priority
High priority:
1. Fix user/admin create flow routing and form action (issues #2 and #9).
2. Fix competition edit relationship mismatch (`withPivot` on `hasMany`) (issue #3).
3. Fix challenge cancel flow (method mismatch + missing controller method) (issue #4).
4. Fix malformed profile modal HTML and non-functional submit wiring (issues #5 and #6).

Medium priority:
5. Membership features validation handling for empty feature inputs (issue #7).
6. Implement report PDF export endpoint and frontend integration (issue #8).

Low/Configuration priority:
7. Standardize timezone/display strategy for profile last login (issue #1).

---

## Notes
- This report is based on source inspection and the provided screenshots.
- No application code was modified in this task.
