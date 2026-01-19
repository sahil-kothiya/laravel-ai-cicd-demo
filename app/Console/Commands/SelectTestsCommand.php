<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SelectTestsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:select-tests {--json : Output as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AI-powered test selection based on changed files';

    /**
     * File to test mappings
     */
    protected $mappings = [
        'app/Http/Controllers/UserController.php' => [
            'tests/Feature/UserControllerTest.php',
        ],
        'app/Http/Controllers/ProductController.php' => [
            'tests/Feature/ProductControllerTest.php',
        ],
        'app/Http/Controllers/OrderController.php' => [
            'tests/Feature/OrderControllerTest.php',
        ],
        'app/Http/Requests/StoreUserRequest.php' => [
            'tests/Feature/UserControllerTest.php',
        ],
        'app/Http/Requests/UpdateUserRequest.php' => [
            'tests/Feature/UserControllerTest.php',
        ],
        'app/Http/Requests/StoreProductRequest.php' => [
            'tests/Feature/ProductControllerTest.php',
        ],
        'app/Http/Requests/UpdateProductRequest.php' => [
            'tests/Feature/ProductControllerTest.php',
        ],
        'app/Http/Requests/StoreOrderRequest.php' => [
            'tests/Feature/OrderControllerTest.php',
        ],
        'app/Http/Requests/CancelOrderRequest.php' => [
            'tests/Feature/OrderControllerTest.php',
        ],
        'app/Models/User.php' => [
            'tests/Feature/UserControllerTest.php',
            'tests/Feature/OrderControllerTest.php',
        ],
        'app/Models/Product.php' => [
            'tests/Feature/ProductControllerTest.php',
            'tests/Feature/OrderControllerTest.php',
        ],
        'app/Models/Order.php' => [
            'tests/Feature/OrderControllerTest.php',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get changed files from git
        $changedFiles = $this->getChangedFiles();
        
        if (empty($changedFiles)) {
            $this->warn('No changed files detected. Running smoke tests only.');
            return $this->outputSmokeTests();
        }

        // Analyze changes
        $selectedTests = $this->selectTests($changedFiles);
        
        // Calculate metrics
        $totalTests = 80; // Update this if you add more tests
        $selectedCount = count($selectedTests);
        $reduction = $selectedCount > 0 ? round((1 - $selectedCount / $totalTests) * 100, 2) : 0;

        // Store results
        $this->storeResults([
            'timestamp' => now()->toIso8601String(),
            'changed_files' => $changedFiles,
            'selected_tests' => $selectedTests,
            'test_count' => $selectedCount,
            'total_tests' => $totalTests,
            'reduction_percentage' => $reduction,
            'estimated_time' => $this->estimateTime($selectedTests),
        ]);

        // Output results
        if ($this->option('json')) {
            $this->line(json_encode([
                'tests' => $selectedTests,
                'selected_tests' => $selectedCount,
                'total_tests' => $totalTests,
                'reduction_percentage' => $reduction,
                'estimated_time' => $this->estimateTime($selectedTests),
            ]));
        } else {
            $this->displayResults($changedFiles, $selectedTests, $reduction);
        }

        return 0;
    }

    /**
     * Get list of changed files from git
     */
    protected function getChangedFiles(): array
    {
        // Try to get changed files from git
        $output = shell_exec('git diff --name-only HEAD~1 HEAD 2>&1');
        
        if (!$output) {
            // Fallback: get uncommitted changes
            $output = shell_exec('git diff --name-only 2>&1');
        }
        
        if (!$output) {
            return [];
        }

        $files = array_filter(
            explode("\n", trim($output)),
            fn($file) => !empty($file)
        );

        return array_values($files);
    }

    /**
     * Select tests based on changed files
     */
    protected function selectTests(array $changedFiles): array
    {
        $selectedTests = [];
        $documentationOnly = true;
        $configOnly = true;

        foreach ($changedFiles as $file) {
            // Check if it's a documentation file
            if (preg_match('/\.(md|txt)$/i', $file)) {
                continue; // Skip docs - they don't need tests
            }
            
            $documentationOnly = false;

            // Check if it's a config file
            if (!str_starts_with($file, 'config/')) {
                $configOnly = false;
            }

            // Direct mapping
            if (isset($this->mappings[$file])) {
                $selectedTests = array_merge($selectedTests, $this->mappings[$file]);
                continue;
            }

            // Pattern matching for directories
            if (str_starts_with($file, 'app/Http/Controllers/')) {
                $controller = basename($file, '.php');
                $testFile = "tests/Feature/{$controller}Test.php";
                if (File::exists(base_path($testFile))) {
                    $selectedTests[] = $testFile;
                }
            } elseif (str_starts_with($file, 'app/Models/')) {
                // Model changes might affect multiple controllers
                $model = basename($file, '.php');
                if ($model === 'User') {
                    $selectedTests[] = 'tests/Feature/UserControllerTest.php';
                    $selectedTests[] = 'tests/Feature/OrderControllerTest.php';
                } elseif ($model === 'Product') {
                    $selectedTests[] = 'tests/Feature/ProductControllerTest.php';
                    $selectedTests[] = 'tests/Feature/OrderControllerTest.php';
                } elseif ($model === 'Order') {
                    $selectedTests[] = 'tests/Feature/OrderControllerTest.php';
                }
            } elseif (str_starts_with($file, 'database/migrations/')) {
                // Migration changes - run all feature tests but skip performance
                $selectedTests[] = 'tests/Feature/UserControllerTest.php';
                $selectedTests[] = 'tests/Feature/ProductControllerTest.php';
                $selectedTests[] = 'tests/Feature/OrderControllerTest.php';
            }
        }

        // Remove duplicates
        $selectedTests = array_unique($selectedTests);

        // If only documentation changed, return empty (will run smoke tests)
        if ($documentationOnly) {
            return [];
        }

        // If only config changed, run minimal tests
        if ($configOnly && empty($selectedTests)) {
            return ['tests/Feature/UserControllerTest.php'];
        }

        // Never automatically include performance tests
        // They should only run on main branch or explicitly
        $selectedTests = array_filter(
            $selectedTests,
            fn($test) => !str_contains($test, 'PerformanceTest')
        );

        return array_values($selectedTests);
    }

    /**
     * Output smoke tests configuration
     */
    protected function outputSmokeTests(): int
    {
        if ($this->option('json')) {
            $this->line(json_encode([
                'tests' => [],
                'selected_tests' => 0,
                'total_tests' => 80,
                'reduction_percentage' => 100,
                'estimated_time' => '2s',
                'run_smoke_only' => true,
            ]));
        } else {
            $this->info('🔍 Documentation/Config only changed');
            $this->info('📝 Running smoke tests only (2-3 seconds)');
            $this->info('💡 Reduction: 100% (skip all heavy tests)');
        }

        return 0;
    }

    /**
     * Estimate execution time based on selected tests
     */
    protected function estimateTime(array $tests): string
    {
        if (empty($tests)) {
            return '2s'; // Smoke tests
        }

        $timing = [
            'tests/Feature/UserControllerTest.php' => 9,
            'tests/Feature/ProductControllerTest.php' => 7,
            'tests/Feature/OrderControllerTest.php' => 7,
            'tests/Feature/PerformanceTest.php' => 52,
        ];

        $totalSeconds = 0;
        foreach ($tests as $test) {
            $totalSeconds += $timing[$test] ?? 5;
        }

        return $totalSeconds . 's';
    }

    /**
     * Store results to file
     */
    protected function storeResults(array $data): void
    {
        $directory = storage_path('ai');
        
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put(
            $directory . '/test-selection.json',
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Display results in console
     */
    protected function displayResults(array $changedFiles, array $selectedTests, float $reduction): void
    {
        $this->info('🤖 AI Test Selection Results');
        $this->newLine();

        $this->comment('Changed Files:');
        foreach ($changedFiles as $file) {
            $this->line("  - {$file}");
        }
        $this->newLine();

        if (empty($selectedTests)) {
            $this->warn('No code changes detected. Run smoke tests only.');
            return;
        }

        $this->comment('Selected Tests:');
        foreach ($selectedTests as $test) {
            $this->line("  - {$test}");
        }
        $this->newLine();

        $this->info("📊 Test Reduction: {$reduction}%");
        $this->info("⏱️  Estimated Time: {$this->estimateTime($selectedTests)}");
        $this->info("💰 Cost Savings: ~{$reduction}%");
    }
}
