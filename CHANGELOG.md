# Changelog

## v1.0.0
- Initial MVP release.
## v1.0.2
- Composer packages updated
- Require maatwebsite/excel ^4.0 (was ^3.1)
## v1.0.3
- Fix: imports now fail when a rule reports a level **at or above** the minimal report level. Previously only results exactly at the threshold failed the import, so e.g. a `CRITICAL` result with an `ERROR` threshold was silently imported.
- Tests: Feature tests now boot a Laravel app via Orchestra Testbench with the Laravel Excel service provider; added integration tests that drive a real `.xlsx` through `Excel::import()`.
- Tests: removed unused root `pest.php` (Pest only loads `tests/Pest.php`).
- Docs: README threshold wording aligned with behavior.
