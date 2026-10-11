# Acceptance checks in Codespaces

Use disposable local accounts and polls. The system must run with MySQL, not a simulated browser database.

1. Run `find app public scripts -name '*.php' -exec php -l {} \;`. Every PHP file should report no syntax errors.
2. Run `php scripts/install.php` twice. Both runs should succeed without erasing records.
3. Create an admin and optional sample polls. Check that active/upcoming/closed cards appear and counts match real data (no sample votes).
4. Register a voter. Sign out, then sign in with the same password. An incorrect password must fail. Registering the same email again must fail.
5. While signed out, a poll must ask you to sign in to vote. A voter opening `?page=admin` must not get administrative controls.
6. As admin, create an active poll with at least two options. A deadline before the start time, duplicate options or one option must be rejected. Form values should survive a validation failure.
7. Edit an unvoted poll. Changes should persist. Vote as a voter; confirm the success state and a total of one vote. Refresh and repeat the submission: total must remain one.
8. Try voting from two tabs on the same poll. Only the first submission may succeed. An option belonging to a different poll must be rejected server-side.
9. Before closure, voters and visitors must not see the per-option results. Admin must not be able to edit the question after a vote. Close the poll; bars, counts and percentages should match. New votes must be rejected.
10. Make an upcoming poll and a poll ending in a few minutes. The first must reject voting until its start; the second must reject voting after its deadline even if the page was loaded earlier.
11. Search/filter the dashboard, including a term with no matches. Check My votes and Results & history.
12. Disable a voter as admin. That voter must lose access to voting on the next request. Re-enable the voter; existing votes remain counted. Administrators must not be disableable through this action.
13. Submit a form with the CSRF token removed or altered: reject without changing data. Enter `<script>alert(1)</script>` as a poll description: it must render as text, never execute.
14. Resize to phone width. Navigation must remain reachable, forms fit the screen and each choice be usable with keyboard and touch.
15. Restart services and confirm accounts, polls and votes remain in MySQL. Do not remove the volume.
