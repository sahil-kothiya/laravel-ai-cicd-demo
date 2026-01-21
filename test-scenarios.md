# AI CI/CD Test Scenarios

## How to Test Locally Before Pushing to GitHub

### Scenario 1: Change UserController

```bash
# Make a change
echo "// Updated method" >> app/Http/Controllers/UserController.php

# See which tests will run
php artisan ai:select-tests

# Expected Result:
# - 5 Critical Tests (Always Run)
# - UserTest, UserComprehensiveTest, UserValidationTest, ApiTest (AI Selected)
# Total: ~9 tests
```

### Scenario 2: Change User Model

```bash
# Make a change
echo "// Added new field" >> app/Models/User.php

# See which tests will run
php artisan ai:select-tests

# Expected Result:
# - 5 Critical Tests (Always Run)
# - UserTest, UserComprehensiveTest, UserValidationTest, SecurityTest,
#   ServiceLayerTest, RepositoryTest, DataIntegrityTest (AI Selected)
# Total: ~12 tests
```

### Scenario 3: Change ProductController

```bash
# Make a change
echo "// Updated product logic" >> app/Http/Controllers/ProductController.php

# See which tests will run
php artisan ai:select-tests

# Expected Result:
# - 5 Critical Tests (Always Run)
# - ProductComprehensiveTest, ProductValidationTest, ApiTest (AI Selected)
# Total: ~8 tests
```

### Scenario 4: Change Multiple Files

```bash
# Make multiple changes
echo "// Update" >> app/Models/User.php
echo "// Update" >> app/Models/Product.php

# See which tests will run
php artisan ai:select-tests

# Expected Result:
# - 5 Critical Tests (Always Run)
# - UserTest, ProductTest, UserComprehensiveTest, ProductComprehensiveTest,
#   ServiceLayerTest, RepositoryTest, DataIntegrityTest (AI Selected)
# Total: ~15 tests
```

### Scenario 5: Documentation Only Changes

```bash
# Make doc changes
echo "# Updated docs" >> README.md

# See which tests will run
php artisan ai:select-tests

# Expected Result:
# - 5 Critical Tests (Always Run)
# - No additional tests
# Total: 5 tests
```

---

## How to Test in GitHub Actions

### Step 1: Make Changes Locally

```bash
# Example: Update UserController
echo "// New feature" >> app/Http/Controllers/UserController.php
```

### Step 2: Commit and Push

```bash
git add app/Http/Controllers/UserController.php
git commit -m "feat: Update user controller logic"
git push origin main
```

### Step 3: View GitHub Actions

1. Go to: https://github.com/sahil-kothiya/laravel-ai-cicd-demo/actions
2. Click on your latest workflow run
3. Expand "🤖 AI Test Selection" step
4. You'll see:
   ```
   🔴 CRITICAL TESTS (Always Run) - 5 tests
   ✅ CHANGE-BASED TESTS (Selected by AI) - X tests
   ```

---

## Understanding the Output

### GitHub Actions will show you:

**Section 1: Critical Tests (RED 🔴)**

- Always shows 5 tests
- These ALWAYS run regardless of changes
- Provides baseline safety

**Section 2: Change-Based Tests (GREEN ✅)**

- Shows tests selected by AI
- Number varies based on what you changed
- Smart selection based on code dependencies

**Annotations at Top:**

- 🔴 Critical: Shows count of always-run tests
- ✅ Change-Based: Shows count of AI-selected tests
- 📊 Reduction: Shows percentage saved

---

## Quick Test Commands

```bash
# See what tests would run now
php artisan ai:select-tests

# See in JSON format
php artisan ai:select-tests --json

# Run the selected tests locally
php artisan ai:select-tests --json | jq -r '.tests | join("|")' | xargs -I {} vendor/bin/phpunit --filter="{}"
```

---

## Files and Their Test Mappings

### Controllers → Tests

- UserController.php → UserTest, UserComprehensiveTest, ApiTest
- ProductController.php → ProductTest, ProductComprehensiveTest, ApiTest
- OrderController.php → OrderTest, OrderComprehensiveTest, ApiTest

### Models → Tests

- User.php → UserTest, UserComprehensiveTest, UserValidationTest, SecurityTest, ServiceLayerTest, RepositoryTest
- Product.php → ProductTest, ProductComprehensiveTest, ProductValidationTest, ServiceLayerTest, RepositoryTest
- Order.php → OrderTest, OrderComprehensiveTest, OrderValidationTest, ServiceLayerTest, RepositoryTest

### Services → Tests

- UserService.php → UserTest, ServiceLayerTest
- ProductService.php → ProductTest, ServiceLayerTest
- OrderService.php → OrderTest, ServiceLayerTest

### Repositories → Tests

- UserRepository.php → RepositoryTest, UserComprehensiveTest
- ProductRepository.php → RepositoryTest, ProductComprehensiveTest
- OrderRepository.php → RepositoryTest, OrderComprehensiveTest
