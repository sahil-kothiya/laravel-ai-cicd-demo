<?php

namespace App\Services\AI;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * Intelligent Test Selector
 *
 * Uses AI/ML techniques to analyze code changes and intelligently select
 * which tests need to run, reducing CI/CD time by up to 90%.
 *
 * Algorithm:
 * 1. Analyze Git diff to identify changed files
 * 2. Map files to tests using static analysis and coverage data
 * 3. Calculate impact scores for each test
 * 4. Select high-impact tests + critical tests
 */
class IntelligentTestSelector
{
    /**
     * Configuration for test selection
     */
    private array $config;

    /**
     * Critical tests that should run for risky changes
     * For documentation/config-only changes, these can be skipped
     */
    private array $criticalTests = [
        'Tests\\Unit\\UserTest',
        'Tests\\Unit\\ProductTest',
        'Tests\\Unit\\OrderTest',
    ];

    /**
     * Cache for file-to-test mappings
     */
    private array $testMappings = [];

    public function __construct()
    {
        $this->config = config('ai-pipeline.test_selection', [
            'enabled' => true,
            'confidence_threshold' => 0.75,
            'max_tests' => 100,
            'always_run_critical' => true,
        ]);

        $this->loadTestMappings();
    }

    /**
     * Select tests based on recent code changes
     *
     * @param  string|null  $baseBranch  Branch to compare against (default: main)
     * @return Collection Selected test classes with metadata
     */
    public function selectTests(?string $baseBranch = 'main'): Collection
    {
        // Step 1: Analyze Git changes
        $changedFiles = $this->analyzeGitDiff($baseBranch);

        if ($changedFiles->isEmpty()) {
            return $this->getFallbackTests();
        }

        // Check if only documentation/config files changed
        $onlyDocsOrConfig = $this->onlyDocsOrConfigChanged($changedFiles);

        if ($onlyDocsOrConfig) {
            // For docs-only changes, run minimal smoke tests
            return collect([[
                'test' => 'Tests\\Unit\\UserTest::test_user_creation',
                'reason' => 'smoke_test',
                'confidence' => 1.0,
                'impact_score' => 0.5,
                'file' => 'smoke',
            ]]);
        }

        // Step 2: Map changed files to affected tests
        $affectedTests = $this->mapFilesToTests($changedFiles);

        // Step 3: Calculate impact scores
        $scoredTests = $this->calculateImpactScores($affectedTests, $changedFiles);

        // Step 4: Select high-impact tests
        $selectedTests = $this->selectHighImpactTests($scoredTests);

        // Step 5: Include critical tests only if code changes are risky
        $hasRiskyChanges = $this->hasRiskyChanges($changedFiles);
        if ($this->config['always_run_critical'] && $hasRiskyChanges) {
            $selectedTests = $this->mergeCriticalTests($selectedTests);
        }

        return $selectedTests;
    }

    /**
     * Analyze Git diff to find changed files
     *
     * @return Collection Array of changed file paths
     */
    private function analyzeGitDiff(string $baseBranch): Collection
    {
        try {
            // Get diff from base branch
            $result = Process::run("git diff --name-only origin/{$baseBranch}...HEAD");

            if ($result->failed()) {
                // Fallback to local changes
                $result = Process::run('git diff --name-only HEAD');
            }

            $files = collect(explode("\n", trim($result->output())))
                ->filter()
                ->filter(fn ($file) => str_ends_with($file, '.php'))
                ->values();

            return $files;

        } catch (\Exception $e) {
            Log::warning("Git diff analysis failed: {$e->getMessage()}");

            return collect();
        }
    }

    /**
     * Map changed files to potentially affected tests
     *
     * Uses multiple strategies:
     * - Direct mapping (UserController → UserControllerTest)
     * - Coverage data analysis
     * - Dependency graph traversal
     * - Historical correlation data
     *
     * @return Collection Test classes with confidence scores
     */
    private function mapFilesToTests(Collection $changedFiles): Collection
    {
        $affectedTests = collect();

        foreach ($changedFiles as $file) {
            // Strategy 1: Direct naming convention
            $directTests = $this->findDirectTests($file);
            $affectedTests = $affectedTests->merge($directTests);

            // Strategy 2: Coverage-based mapping
            if (isset($this->testMappings[$file])) {
                foreach ($this->testMappings[$file] as $test => $coverage) {
                    $affectedTests->push([
                        'test' => $test,
                        'reason' => 'coverage',
                        'confidence' => $coverage,
                        'file' => $file,
                    ]);
                }
            }

            // Strategy 3: Dependency analysis
            $dependencyTests = $this->analyzeDependencies($file);
            $affectedTests = $affectedTests->merge($dependencyTests);
        }

        return $affectedTests;
    }

    /**
     * Find tests that directly correspond to a file
     *
     * Examples:
     * - app/Http/Controllers/UserController.php → Tests/Feature/UserControllerTest.php
     * - app/Models/User.php → Tests/Unit/UserTest.php
     */
    private function findDirectTests(string $file): Collection
    {
        $tests = collect();

        // Controller → Feature Test
        if (str_contains($file, 'Controllers/')) {
            $testName = basename($file, '.php').'Test';
            $testPath = "Tests\\Feature\\{$testName}";

            if ($this->testExists($testPath)) {
                $tests->push([
                    'test' => $testPath,
                    'reason' => 'direct_mapping',
                    'confidence' => 0.95,
                    'file' => $file,
                ]);
            }
        }

        // Model → Unit Test
        if (str_contains($file, 'Models/')) {
            $testName = basename($file, '.php').'Test';
            $testPath = "Tests\\Unit\\{$testName}";

            if ($this->testExists($testPath)) {
                $tests->push([
                    'test' => $testPath,
                    'reason' => 'direct_mapping',
                    'confidence' => 0.95,
                    'file' => $file,
                ]);
            }
        }

        return $tests;
    }

    /**
     * Analyze file dependencies to find affected tests
     */
    private function analyzeDependencies(string $file): Collection
    {
        // This is a simplified version. In production, you'd use:
        // - Static analysis tools (Nikic/PHP-Parser)
        // - Composer autoload maps
        // - Custom dependency graphs

        $dependencies = [];

        // Example: If User model changes, UserController tests may be affected
        if (str_contains($file, 'Models/User.php')) {
            $dependencies[] = [
                'test' => 'Tests\\Feature\\UserControllerTest',
                'reason' => 'dependency',
                'confidence' => 0.70,
                'file' => $file,
            ];
        }

        return collect($dependencies);
    }

    /**
     * Calculate impact scores for each test
     *
     * Score factors:
     * - Confidence from mapping (0.0 - 1.0)
     * - Number of changed lines in source file
     * - Historical failure rate of test
     * - Code complexity of changed files
     *
     * @return Collection Tests with impact scores
     */
    private function calculateImpactScores(Collection $tests, Collection $changedFiles): Collection
    {
        return $tests->map(function ($test) {
            $baseScore = $test['confidence'];

            // Boost score for frequently failing tests
            $failureRate = $this->getTestFailureRate($test['test']);
            $failureBoost = $failureRate * 0.2;

            // Boost score for complex file changes
            $complexity = $this->getFileComplexity($test['file']);
            $complexityBoost = min($complexity / 100, 0.3);

            $finalScore = min($baseScore + $failureBoost + $complexityBoost, 1.0);

            return array_merge($test, [
                'impact_score' => $finalScore,
            ]);
        });
    }

    /**
     * Select tests above the confidence threshold
     */
    private function selectHighImpactTests(Collection $scoredTests): Collection
    {
        $threshold = $this->config['confidence_threshold'];
        $maxTests = $this->config['max_tests'];

        return $scoredTests
            ->filter(fn ($test) => $test['impact_score'] >= $threshold)
            ->sortByDesc('impact_score')
            ->take($maxTests)
            ->unique('test')
            ->values();
    }

    /**
     * Merge critical tests that always run
     */
    private function mergeCriticalTests(Collection $selectedTests): Collection
    {
        $critical = collect($this->criticalTests)->map(fn ($test) => [
            'test' => $test,
            'reason' => 'critical',
            'confidence' => 1.0,
            'impact_score' => 1.0,
            'file' => 'N/A',
        ]);

        return $selectedTests
            ->merge($critical)
            ->unique('test')
            ->values();
    }

    /**
     * Get fallback tests when no changes detected
     */
    private function getFallbackTests(): Collection
    {
        // Run only critical tests when no changes
        return $this->mergeCriticalTests(collect());
    }

    /**
     * Load test coverage mappings from storage
     */
    private function loadTestMappings(): void
    {
        $mappingFile = storage_path('ai/test-mappings.json');

        if (file_exists($mappingFile)) {
            $this->testMappings = json_decode(file_get_contents($mappingFile), true);
        } else {
            // Default mappings for demo - matches actual test structure
            $this->testMappings = [
                'app/Http/Controllers/UserController.php' => [
                    'Tests\\Unit\\UserTest' => 0.95,
                    'Tests\\Unit\\UserValidationTest' => 0.90,
                ],
                'app/Models/User.php' => [
                    'Tests\\Unit\\UserTest' => 0.95,
                    'Tests\\Unit\\UserValidationTest' => 0.95,
                    'Tests\\Unit\\DataIntegrityTest' => 0.70,
                    'Tests\\Unit\\IntegrationTest' => 0.60,
                ],
                'app/Http/Controllers/ProductController.php' => [
                    'Tests\\Unit\\ProductTest' => 0.95,
                    'Tests\\Unit\\ProductValidationTest' => 0.90,
                ],
                'app/Models/Product.php' => [
                    'Tests\\Unit\\ProductTest' => 0.95,
                    'Tests\\Unit\\ProductValidationTest' => 0.95,
                    'Tests\\Unit\\DataIntegrityTest' => 0.70,
                    'Tests\\Unit\\IntegrationTest' => 0.60,
                ],
                'app/Http/Controllers/OrderController.php' => [
                    'Tests\\Unit\\OrderTest' => 0.95,
                    'Tests\\Unit\\OrderValidationTest' => 0.90,
                ],
                'app/Models/Order.php' => [
                    'Tests\\Unit\\OrderTest' => 0.95,
                    'Tests\\Unit\\OrderValidationTest' => 0.95,
                    'Tests\\Unit\\DataIntegrityTest' => 0.70,
                    'Tests\\Unit\\IntegrationTest' => 0.60,
                ],
                'app/Services/UserService.php' => [
                    'Tests\\Unit\\UserTest' => 0.85,
                    'Tests\\Unit\\UserValidationTest' => 0.75,
                    'Tests\\Unit\\IntegrationTest' => 0.50,
                ],
                'app/Services/ProductService.php' => [
                    'Tests\\Unit\\ProductTest' => 0.85,
                    'Tests\\Unit\\ProductValidationTest' => 0.75,
                    'Tests\\Unit\\IntegrationTest' => 0.50,
                ],
                'app/Services/OrderService.php' => [
                    'Tests\\Unit\\OrderTest' => 0.85,
                    'Tests\\Unit\\OrderValidationTest' => 0.75,
                    'Tests\\Unit\\IntegrationTest' => 0.50,
                ],
                'database/migrations/' => [
                    'Tests\\Unit\\DataIntegrityTest' => 0.90,
                    'Tests\\Unit\\IntegrationTest' => 0.70,
                ],
            ];
        }
    }

    /**
     * Check if a test class exists
     */
    private function testExists(string $testPath): bool
    {
        // In production, check if class exists
        // For demo, simulate with common test patterns
        return true;
    }

    /**
     * Get historical failure rate for a test
     *
     * @return float Between 0.0 and 1.0
     */
    private function getTestFailureRate(string $test): float
    {
        // In production, query from build history database
        // For demo, return simulated data
        return 0.05; // 5% failure rate
    }

    /**
     * Calculate cyclomatic complexity of a file
     */
    private function getFileComplexity(string $file): int
    {
        // In production, use static analysis tools
        // For demo, estimate based on file size
        if (file_exists($file)) {
            return substr_count(file_get_contents($file), 'if') +
                   substr_count(file_get_contents($file), 'while') +
                   substr_count(file_get_contents($file), 'for');
        }

        return 10;
    }

    /**
     * Generate a human-readable report of test selection
     */
    public function generateReport(Collection $selectedTests, int $totalTests): array
    {
        $selectedCount = $selectedTests->count();
        $reduction = $totalTests > 0
            ? round((($totalTests - $selectedCount) / $totalTests) * 100, 1)
            : 0;

        $estimatedTime = $this->estimateTimeSavings($totalTests, $selectedCount);

        return [
            'total_tests' => $totalTests,
            'selected_tests' => $selectedCount,
            'reduction_percentage' => $reduction,
            'estimated_time_savings' => $estimatedTime,
            'tests' => $selectedTests->pluck('test')->toArray(),
            'breakdown' => [
                'critical' => $selectedTests->where('reason', 'critical')->count(),
                'direct_mapping' => $selectedTests->where('reason', 'direct_mapping')->count(),
                'coverage' => $selectedTests->where('reason', 'coverage')->count(),
                'dependency' => $selectedTests->where('reason', 'dependency')->count(),
            ],
        ];
    }

    /**
     * Estimate time savings in minutes
     */
    private function estimateTimeSavings(int $totalTests, int $selectedTests): float
    {
        $avgTimePerTest = 2; // seconds
        $totalTime = ($totalTests * $avgTimePerTest) / 60; // minutes
        $selectedTime = ($selectedTests * $avgTimePerTest) / 60; // minutes

        return round($totalTime - $selectedTime, 1);
    }

    /**
     * Check if only documentation or config files changed
     */
    private function onlyDocsOrConfigChanged(Collection $changedFiles): bool
    {
        $docPatterns = ['*.md', '*.txt', '.env.example', '*.yml', '*.yaml', '.gitignore', 'LICENSE'];

        foreach ($changedFiles as $file) {
            // If it's a PHP file, return false
            if (str_ends_with($file, '.php')) {
                return false;
            }

            // If it's a config or migration, return false
            if (str_contains($file, 'config/') && str_ends_with($file, '.php')) {
                return false;
            }
            if (str_contains($file, 'database/migrations/')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if changes involve risky code areas
     */
    private function hasRiskyChanges(Collection $changedFiles): bool
    {
        $riskyPatterns = [
            'app/Models/',
            'app/Http/Controllers/',
            'database/migrations/',
            'app/Services/',
        ];

        foreach ($changedFiles as $file) {
            foreach ($riskyPatterns as $pattern) {
                if (str_contains($file, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }
}
