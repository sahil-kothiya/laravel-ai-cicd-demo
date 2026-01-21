# 🚀 Quick Start Guide: Testing AI CI/CD Locally

## ⚡ Quick Demo (30 seconds)

```powershell
# Run the interactive demo
.\demo-test-selection.ps1

# OR manually test:
echo "// test change" >> app/Http/Controllers/UserController.php
php artisan ai:select-tests
```

---

## 📝 Step-by-Step: Make a Change and See Tests

### Example 1: Update UserController

```bash
# 1. Make a change
echo "// Added validation logic" >> app/Http/Controllers/UserController.php

# 2. See what tests will run locally
php artisan ai:select-tests

# 3. You'll see:
# 🔴 5 Critical Tests (Always)
# ✅ 3 Change-Based Tests (UserTest, UserComprehensiveTest, ApiTest)
# Total: 8 tests instead of 1000!

# 4. Push to GitHub
git add app/Http/Controllers/UserController.php
git commit -m "feat: Update user validation"
git push origin main

# 5. Check GitHub Actions
# Go to: https://github.com/sahil-kothiya/laravel-ai-cicd-demo/actions
# You'll see the SAME 8 tests running!
```

---

## 🎯 What Happens in GitHub Actions

When you push, GitHub Actions will show:

```
🤖 AI Test Selection
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 Analysis Complete:
   Total Tests: 1000
   Selected Tests: 8
   Reduction: 99.2%

🔴 CRITICAL TESTS (Always Run) - 5 tests ▼
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
These tests ALWAYS run regardless of code changes:

  🔴 Tests\Unit\UserTest
  🔴 Tests\Unit\ProductTest
  🔴 Tests\Unit\OrderTest
  🔴 Tests\Unit\SecurityTest
  🔴 Tests\Unit\IntegrationTest
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✅ CHANGE-BASED TESTS (Selected by AI) - 3 tests ▼
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
These tests were selected based on your code changes:

  ✅ Tests\Unit\UserComprehensiveTest
  ✅ Tests\Unit\UserValidationTest
  ✅ Tests\Unit\ApiTest
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## 🔥 Try Different Scenarios

### Scenario A: Change User Model (More Tests)

```bash
echo "// Added user field" >> app/Models/User.php
php artisan ai:select-tests
# Result: 5 Critical + ~7 Change-Based = 12 tests
```

### Scenario B: Change ProductController

```bash
echo "// Updated products" >> app/Http/Controllers/ProductController.php
php artisan ai:select-tests
# Result: 5 Critical + ~3 Change-Based = 8 tests
```

### Scenario C: Change README (Docs Only)

```bash
echo "# Updated docs" >> README.md
php artisan ai:select-tests
# Result: 5 Critical + 0 Change-Based = 5 tests
```

### Scenario D: Change Service Layer

```bash
echo "// Enhanced service" >> app/Services/UserService.php
php artisan ai:select-tests
# Result: 5 Critical + ~2 Change-Based = 7 tests
```

---

## 📊 File → Test Mapping Reference

| Your Change               | Additional Tests Selected (Beyond 5 Critical)                                                                          |
| ------------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| **UserController.php**    | UserComprehensiveTest, UserValidationTest, ApiTest                                                                     |
| **User.php (Model)**      | UserTest, UserComprehensiveTest, UserValidationTest, SecurityTest, ServiceLayerTest, RepositoryTest, DataIntegrityTest |
| **ProductController.php** | ProductComprehensiveTest, ProductValidationTest, ApiTest                                                               |
| **UserService.php**       | UserTest, ServiceLayerTest                                                                                             |
| **README.md**             | None (only critical tests)                                                                                             |

---

## ✅ Verify It Works

### Local Check:

```bash
php artisan ai:select-tests --json
```

### GitHub Check:

1. Push your changes
2. Go to Actions tab
3. Click latest run
4. Expand "🤖 AI Test Selection"
5. See the color-coded breakdown!

---

## 🎨 What You'll See

**GitHub Annotations (Blue notices at top):**

- 🔴 Critical Tests: X tests will always run
- ✅ Change-Based: Y tests selected
- 📊 Total Reduction: Z%

**Collapsible Sections (in logs):**

- 🔴 Red section = Always-run tests
- ✅ Green section = AI-selected tests

---

## 💡 Pro Tips

1. **Test locally first**: Run `php artisan ai:select-tests` before pushing
2. **Small commits**: Make focused changes to see clearer test selection
3. **Check the breakdown**: Look at critical vs change-based counts
4. **Watch the reduction**: See how much time you're saving!

---

## 🚨 Troubleshooting

**No tests selected?**

- Check: `php artisan ai:select-tests --json`
- Should always show at least 5 critical tests

**Wrong tests selected?**

- Test mappings are in: `app/Services/AI/IntelligentTestSelector.php`
- Check: `$testMappings` array

**Tests not running in GitHub?**

- Check Actions tab for errors
- Verify workflow file: `.github/workflows/ai-pipeline.yml`

---

Ready to test? Run: `.\demo-test-selection.ps1` 🚀
