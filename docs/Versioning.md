# Versioning Guidelines

This library follows **Semantic Versioning (SemVer)** to provide predictable, stable updates for users.

## 🧩 SemVer Overview

Semantic Versioning uses the format:

```
MAJOR.MINOR.PATCH
```

Each segment has a clear meaning:

### **MAJOR**
- Breaking changes  
- Removal or modification of public APIs  
- Changes that require users to modify their code  
- Large redesigns or behavioral changes in core components  
- Example: `1.x.x → 2.0.0`

### **MINOR**
- New features that are fully backwards‑compatible  
- Additions to public APIs (new methods, new rules, new DTO fields with defaults)  
- Internal improvements that do not break existing behavior  
- Example: `1.4.x → 1.5.0`

### **PATCH**
- Bug fixes  
- Documentation updates  
- Performance improvements  
- Non-breaking refactors  
- Example: `1.4.1 → 1.4.2`

---

## 📁 What Counts as a Public API in This Package?

Anything intended for users of the package:

### **Public API Includes:**
- `AnalyzesData` trait and its public methods  
- DTOs (`ExcelImportAnalysisResultDto`)  
- Enum: `ExcelImportAnalysisLevelEnum`  
- Rule contracts (`ExcelImportAnalysisRuleInterface`)  
- Repository contracts  
- Exception classes  
- Expected behavior of:
  - `withAnalysis()`
  - `withoutAnalysis()`
  - analysis-only mode with `withAnalysis(true)`
  - minimal report level logic  
- Base rule abstract class  
- Namespace structure  

### **Internal API (safe to change in MINOR/PATCH):**
- Code inside `Abstracts` *unless marked final*  
- Internal helper traits and private methods  
- Test utilities  
- Structure of example rules  
- Internal repository logic as long as contract behavior stays the same  

---

## 🔒 Backward Compatibility Rules

The following changes **must trigger a MAJOR version**:

- Removing or renaming public methods  
- Changing the behavior of:
  - `withAnalysis()`  
  - `withAnalysis(true)`  
  - `withAnalysis(false)`  
  - `withoutAnalysis()`  
- Changing how severity comparison works  
- Modifying the DTO constructor in a breaking way  
- Altering the meaning of severity levels  
- Changing rule validation flow  
- Modifying exception behavior  

---

## 🧪 Stability Guarantees

Within the same **MINOR** version:

- Rule execution order is stable  
- Analysis collection format is stable  
- DTO shape is stable  
- Severity comparison logic is stable  
- Exceptions remain consistent  

Within the same **PATCH** version:

- Only bugfixes, no new features  
- No new public methods  
- No changes affecting test expectations  

---

## 🔖 Release Workflow

After merging into `main`:

1. Determine version type: MAJOR / MINOR / PATCH  
2. Tag the release:

```bash
git tag vX.Y.Z
git push --tags
```

3. Packagist will update automatically  
4. (Optional) Write release notes summarizing changes  

---

## 🔮 Roadmap Toward 1.0.0

Before declaring the package stable:

- Finalize public API semantics  
- Ensure consistent behavior of analysis modes  
- Add coverage for all rule flows  
- Validate extension points (new rules, repositories)  
- Document all guarantee surfaces  

---

## 🧡 Thank You

Clear versioning helps everyone trust the package.  
Thank you for helping maintain stability and high quality!

