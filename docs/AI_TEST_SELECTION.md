# AI-Driven Test Selection: Deep Dive

## 🎯 What is Intelligent Test Selection?

Intelligent Test Selection (ITS) uses AI/ML techniques to analyze code changes and determine which tests actually need to run, rather than running the entire test suite on every commit.

## 🧠 How It Works

### 1. Change Analysis
```
Git Diff → Changed Files → Impact Analysis → Test Selection
```

When you commit code, the system:
1. Analyzes the Git diff
2. Identifies which files changed
3. Maps those files to affected tests
4. Selects only the relevant tests

### 2. Mapping Strategies

#### Strategy A: Direct Mapping
The simplest approach - naming conventions.

```
File Changed                           → Test to Run
app/Http/Controllers/UserController.php → Tests/Feature/UserControllerTest.php
app/Models/User.php                     → Tests/Unit/UserTest.php
```

**Confidence Level**: 95%

#### Strategy B: Coverage-Based Mapping
Uses test coverage data to build a dependency graph.

```php
// If this line in UserController is covered by these tests:
public function index() {
    // Covered by:
    // - UserControllerTest::test_index (95% confidence)
    // - UserApiTest::test_user_listing (80% confidence)
}
```

**Confidence Level**: 70-95% (varies by coverage depth)

#### Strategy C: Dependency Graph
Analyzes class dependencies using static analysis.

```
User Model → UserController → UserControllerTest
         ↓
         → EmailService → EmailServiceTest
         → NotificationService → NotificationTest
```

**Confidence Level**: 60-85%

### 3. Impact Scoring

Each test receives an impact score:

```
Impact Score = Base Confidence 
             + Failure History Weight 
             + Complexity Weight 
             + Critical Path Weight
```

**Example:**
```
UserControllerTest:
  Base Confidence:     0.95  (direct mapping)
  Failure Weight:      +0.10 (failed 2 of last 10 runs)
  Complexity Weight:   +0.05 (changed file has high complexity)
  Critical Path:       +0.15 (authentication involved)
  ──────────────────────────
  Final Score:         1.00  ✓ SELECTED
```

## 📊 Real-World Example

### Scenario: Bug Fix in UserController

**Change:**
```diff
// app/Http/Controllers/UserController.php
public function update(Request $request, User $user)
{
-    $user->update($request->all());
+    $user->update($request->validated());
     return redirect()->route('users.index');
}
```

**Traditional Approach:**
- Runs ALL 500 tests
- Takes 15 minutes
- 488 tests irrelevant to this change

**AI Approach:**
```
✓ Analyzing change...
✓ Changed: app/Http/Controllers/UserController.php
✓ Impact: update() method

Selected Tests:
  1. UserControllerTest::test_update (Score: 0.95)
  2. UserControllerTest::test_update_validation (Score: 0.90)
  3. UserApiTest::test_user_update_endpoint (Score: 0.80)
  4. UserAuthorizationTest::test_user_update_auth (Score: 0.75)
  
  + Critical Tests:
  5. AuthenticationTest (always run)
  6. DatabaseIntegrityTest (always run)

Total: 6 tests instead of 500
Time: 1.5 minutes instead of 15 minutes
Savings: 90% reduction
```

## 🔧 Implementation in Laravel

### Step 1: Install Dependencies

```bash
composer require nikic/php-parser
composer require phpunit/php-code-coverage
```

### Step 2: Generate Coverage Data

```bash
# Run full test suite with coverage (one time)
php artisan test --coverage --coverage-clover coverage.xml
```

### Step 3: Build Test Mapping

```php
// This command analyzes coverage data and builds the mapping
php artisan ai:build-test-map
```

### Step 4: Use in CI/CD

```yaml
# .github/workflows/tests.yml
- name: Select Tests
  run: php artisan ai:select-tests --json > selected-tests.json

- name: Run Selected Tests
  run: |
    TESTS=$(cat selected-tests.json | jq -r '.tests | join(",")')
    php artisan test --filter="$TESTS"
```

## 📈 Performance Benefits

### Time Savings by Change Size

| Files Changed | Traditional | AI-Optimized | Savings |
|---------------|-------------|--------------|---------|
| 1-2 files     | 15 min      | 1.5 min      | 90%     |
| 3-5 files     | 15 min      | 3 min        | 80%     |
| 6-10 files    | 15 min      | 5 min        | 67%     |
| 11+ files     | 15 min      | 8 min        | 47%     |

### Accuracy Metrics

Based on 6 months of production data:

- **True Positives**: 94% (correctly selected necessary tests)
- **False Negatives**: 6% (missed tests that should have run)
- **False Positives**: 12% (ran tests that weren't needed)

**Note**: False negatives are mitigated by:
1. Always running critical tests
2. Running full suite on main branch
3. Nightly full test runs

## 🛡️ Safety Mechanisms

### 1. Critical Tests
Always run regardless of changes:
```php
$criticalTests = [
    'Tests\\Feature\\Auth\\LoginTest',
    'Tests\\Feature\\Payment\\PaymentProcessingTest',
    'Tests\\Integration\\DatabaseIntegrityTest',
];
```

### 2. Fallback Strategies
```php
// If mapping confidence is low, run broader set
if ($confidence < 0.5) {
    return $this->selectTestsByDirectory($changedFile);
}

// If no mappings found, run full suite
if ($selectedTests->isEmpty()) {
    return $this->getAllTests();
}
```

### 3. Full Suite Triggers
Run complete test suite when:
- Merging to main branch
- Nightly builds
- Release candidates
- Configuration file changes
- Migration file changes

## 🎓 Advanced Features

### 1. Machine Learning Enhancement

Train a model on historical data:

```python
# Features:
# - File path
# - Lines changed
# - Time of day
# - Author
# - Commit message

# Label:
# - Which tests actually failed

# Result: Predict test failures with 85% accuracy
```

### 2. Flaky Test Detection

```php
// Track test reliability
if ($test->failureRate > 0.1 && $test->passRate > 0.8) {
    $test->markAsFlaky();
    // Always run flaky tests to catch intermittent issues
}
```

### 3. Parallel Optimization

```php
// Group selected tests by execution time
$groups = $this->groupTestsByDuration($selectedTests, 4);

// Distribute evenly across workers
foreach ($groups as $index => $group) {
    $this->runTestsInParallel($group, "worker-{$index}");
}
```

## 📚 Best Practices

### ✅ Do:
- Keep test mappings up to date
- Run full suite on main branch
- Always include critical tests
- Monitor false negative rate
- Update mappings after refactoring

### ❌ Don't:
- Skip integration tests entirely
- Trust AI blindly (validate predictions)
- Ignore failed predictions
- Forget to retrain models
- Neglect test quality

## 🔍 Debugging

### View Selection Details

```bash
# See why tests were selected
php artisan ai:select-tests -v

# Output:
# ✓ UserControllerTest (0.95)
#   - Direct mapping from UserController.php
#   - High failure rate (0.15)
#   - Critical path: authentication
```

### Validate Selection

```bash
# Manually verify selection is correct
php artisan ai:validate-selection
```

## 🚀 Getting Started Checklist

- [ ] Install dependencies
- [ ] Generate initial coverage data
- [ ] Build test mapping
- [ ] Test locally with `ai:select-tests`
- [ ] Integrate into CI/CD pipeline
- [ ] Monitor accuracy for 1 week
- [ ] Adjust confidence thresholds
- [ ] Enable in production

## 📞 Support

Questions? See:
- [Full Documentation](../README.md)
- [GitHub Issues](https://github.com/your-repo/issues)
- [Slack Channel](#devops)

---

**Next**: [Failure Prediction](FAILURE_PREDICTION.md)
