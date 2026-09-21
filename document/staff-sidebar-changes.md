# Staff sidebar

- The Working as selector displays only available staff roles. Administrator retains the existing committee/deputy dean/dean permissions; supervision and evaluator assignments determine the other roles.
- Dashboard remains visible. The chosen role is remembered per staff ID in browser storage. Direct module URLs select the matching role when it can be identified.
- Pending badges appear on actionable modules and their collapsed parent menus. Role totals and shortcuts expose work in other roles. Zero counts are omitted.
- Counts cover all semesters and exclude work awaiting another approval stage or already signed by the current user. Evaluation approval totals count student activities, matching their overview rows; examiner/chairman totals count evaluation records.
- Examiner/chairman editing remains subject to the existing current-semester restriction. Locked historical evaluation records are not presented as actionable badge counts. Historical submission, nomination, correction and evaluation approvals remain eligible for counting.
- Clicking a counted module clears the destination's default semester/faculty/programme/status filters where available, so older work is visible. This shows the existing module, not a separate work queue.
- Counts load with the sidebar and refresh after successful AJAX changes. They are not continuously polled. A calculation failure is reported visibly without breaking navigation.

Implementation: `app/Services/StaffWorkCounts.php`, `resources/views/staff/layouts/sidebar-new.blade.php`, `public/assets/css/staff-sidebar.css`, `public/assets/js/staff-sidebar.js`, and the authenticated `staff-sidebar-work-counts` route.

Verification: isolated synthetic data checked ten module counts, all four role options, historical approval inclusion and signed-work exclusion; read-only calculation succeeded for all 12 existing staff accounts. Browser checks covered role isolation, remembered selection, parent badges, shortcuts and submenu toggles. The existing page-render check and PHP/JavaScript syntax checks passed. Temporary checks and preview files were removed.

No database migration or live record changes are required.

Design refinement: unified neutral/blue palette, consistent typography and icon sizing, aligned count badges and chevrons, explicit submenu indentation overriding legacy theme rules, quieter role shortcuts, keyboard focus styling, and compact spacing on shorter screens. Business logic is unchanged. Verified Blade rendering, JavaScript syntax, expanded-menu appearance and role switching in a temporary browser preview with synthetic counts; no browser console errors. Temporary preview files removed.
