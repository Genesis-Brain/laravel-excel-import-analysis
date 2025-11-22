# Contributing

Thank you for considering contributing to **Gbrain Excel Imports**!
This project is open-source, and we welcome high-quality and thoughtful contributions.

To help maintain a clean, stable, and predictable codebase, please follow the guidelines below.

## 🛠 Project Setup

Clone the repository and install dependencies:

```bash
git clone <repo-url>
cd excel-imports
composer install
```

Run the test suite to ensure everything works:

```bash
composer test
```

## 🎨 Coding Standards

This project uses **Laravel Pint** for all code formatting.

### ✔ Required before submitting any PR

Run Pint:

```bash
composer pint
```

### ✔ Naming guidelines
- Variables must use camelCase
- Classes / Traits / Interfaces / Enums follow PSR-12 / Laravel conventions
- Methods should also follow camelCase
- Constants are SCREAMING_SNAKE_CASE

### ✔ Type declarations
- Every file must include: declare(strict_types=1);
- Use strict types everywhere possible — no mixed, no untyped arrays unless absolutely required.

## 🔧 Tests

All pull requests must include tests relevant to the change.

Run tests:

```bash
composer test
```

Requirements:
- All existing tests must pass.
- New features require unit and/or feature tests.
- Bug fixes require regression tests.

This project uses Pest.

## 🌿 Branching & Pull Requests

### ✔ Branching model (simple & clean)

1. Create a feature branch:
   git checkout -b feature/my-change

2. Push the branch and open a Pull Request into main.

3. PR description must include:
   - What was changed
   - Why it was changed
   - Any relevant context or examples
   - Whether tests were added or updated

4. Ensure CI passes (Pint + tests).

5. After review, the PR will be merged.

6. A maintainer will tag a new version as needed.

## ✔ Commit messages

No strict commit format is required, but commits must be meaningful and descriptive.

Good examples:
- Add PositiveQuantityRule
- Refactor level comparison logic
- Fix row index off-by-one error
- Improve documentation for withAnalysis()

Bad examples:
- fix
- changes
- stuff

## 🔩 Adding New Rules

If you add a new analysis rule:

1. Implement behavior using the abstract ExcelImportAnalysisRule.
2. Ensure the rule is fully typed and documented.
3. Add a matching unit test in tests/Unit/Rules/.
4. If it’s a common rule that others might reuse, add documentation.

## 🧱 Backward Compatibility

This package follows Semantic Versioning (SemVer):

- MAJOR → breaking changes
- MINOR → new features (backwards-compatible)
- PATCH → bug fixes

Avoid breaking public API unless explicitly coordinated with maintainers.

## 📦 Releasing & Tagging

After merging a PR into main, maintainers:

1. Choose a new version: vX.Y.Z
2. Tag the release:
   git tag vX.Y.Z
   git push --tags

3. Packagist will update automatically.

## 🙌 Thank You

Your contribution makes this package better for everyone.
We deeply appreciate your effort and care!
