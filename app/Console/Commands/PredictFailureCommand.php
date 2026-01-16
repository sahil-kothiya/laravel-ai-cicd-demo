<?php

namespace App\Console\Commands;

use App\Services\AI\FailurePredictor;
use Illuminate\Console\Command;

class PredictFailureCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:predict-failure 
                          {--branch=main : Base branch to compare against}
                          {--json : Output in JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use AI to predict if the build will fail';

    /**
     * Execute the console command.
     */
    public function handle(FailurePredictor $predictor): int
    {
        $branch = $this->option('branch');
        $jsonOutput = $this->option('json');

        if (!$jsonOutput) {
            $this->info('🤖 AI Build Failure Predictor');
            $this->info('═══════════════════════════════════════');
            $this->newLine();
            $this->info('Analyzing code changes and build history...');
        }

        // Run prediction
        $prediction = $predictor->predict($branch);

        if ($jsonOutput) {
            // JSON output for CI/CD
            $this->line(json_encode($prediction, JSON_PRETTY_PRINT));
        } else {
            // Pretty CLI output
            $this->displayPrediction($prediction);
        }

        // Return appropriate exit code
        return Command::SUCCESS;
    }

    /**
     * Display formatted prediction
     */
    private function displayPrediction(array $prediction): void
    {
        $this->newLine();

        // Prediction box
        $outcome = $prediction['prediction'];
        $confidence = round($prediction['confidence'] * 100);
        
        $emoji = match($outcome) {
            'PASS' => '✅',
            'FAIL' => '⚠️',
            'FLAKY' => '⚡',
            default => '❓',
        };

        $color = match($outcome) {
            'PASS' => 'info',
            'FAIL' => 'error',
            'FLAKY' => 'warn',
            default => 'comment',
        };

        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║           Build Failure Prediction                   ║');
        $this->info('╠══════════════════════════════════════════════════════╣');
        $this->$color(sprintf('║ Prediction: %-41s║', "{$emoji} {$outcome}"));
        $this->info(sprintf('║ Confidence: %-41s║', "{$confidence}%"));
        $this->info('╚══════════════════════════════════════════════════════╝');

        $this->newLine();

        // Probabilities
        $this->info('📊 Probability Distribution:');
        $passProb = round($prediction['probability']['pass'] * 100);
        $failProb = round($prediction['probability']['fail'] * 100);
        $flakyProb = round($prediction['probability']['flaky'] * 100);

        $this->line("  ✅ PASS:  " . str_repeat('█', (int)($passProb / 5)) . " {$passProb}%");
        $this->line("  ⚠️  FAIL:  " . str_repeat('█', (int)($failProb / 5)) . " {$failProb}%");
        $this->line("  ⚡ FLAKY: " . str_repeat('█', (int)($flakyProb / 5)) . " {$flakyProb}%");

        $this->newLine();

        // Risk factors
        if (!empty($prediction['risk_factors'])) {
            $this->warn('⚠️  Risk Factors:');
            foreach ($prediction['risk_factors'] as $risk) {
                $severityColor = match($risk['severity']) {
                    'HIGH' => 'error',
                    'MEDIUM' => 'warn',
                    'LOW' => 'comment',
                    default => 'info',
                };

                $this->$severityColor("  • [{$risk['severity']}] {$risk['factor']}");
                $this->line("    {$risk['detail']}");
            }
            $this->newLine();
        }

        // Recommendations
        if (!empty($prediction['recommendations'])) {
            $this->info('💡 Recommendations:');
            foreach ($prediction['recommendations'] as $index => $recommendation) {
                $this->line("  " . ($index + 1) . ". {$recommendation}");
            }
            $this->newLine();
        }

        // Feature analysis (detailed view)
        if ($this->output->isVerbose()) {
            $this->displayFeatureAnalysis($prediction['features']);
        }

        // Final message
        if ($outcome === 'FAIL' && $confidence >= 70) {
            $this->newLine();
            $this->error('⚠️  WARNING: High probability of build failure detected!');
            $this->warn('Consider running tests locally before pushing.');
        } elseif ($outcome === 'PASS') {
            $this->newLine();
            $this->info('✅ Build looks good to proceed!');
        }
    }

    /**
     * Display detailed feature analysis
     */
    private function displayFeatureAnalysis(array $features): void
    {
        $this->newLine();
        $this->info('🔍 Detailed Feature Analysis:');
        $this->newLine();

        $this->table(
            ['Feature', 'Value', 'Impact'],
            [
                ['Files Changed', $features['files_changed'], $this->getImpactLevel($features['files_changed'], 5, 15)],
                ['Lines Added', $features['lines_added'], $this->getImpactLevel($features['lines_added'], 50, 200)],
                ['Lines Removed', $features['lines_removed'], $this->getImpactLevel($features['lines_removed'], 50, 200)],
                ['Avg Complexity', round($features['avg_complexity'], 1), $this->getImpactLevel($features['avg_complexity'], 10, 25)],
                ['Critical Files', $features['critical_files_touched'], $features['critical_files_touched'] > 0 ? 'HIGH' : 'LOW'],
                ['Author Fail Rate', round($features['author_fail_rate'] * 100, 1) . '%', $this->getImpactLevel($features['author_fail_rate'], 0.05, 0.15)],
                ['Test Files', $features['test_files_changed'], $features['test_files_changed'] > 0 ? 'GOOD' : 'RISKY'],
                ['Recent Failures', $features['recent_failures'], $this->getImpactLevel($features['recent_failures'], 1, 3)],
            ]
        );
    }

    /**
     * Get impact level based on thresholds
     */
    private function getImpactLevel($value, $lowThreshold, $highThreshold): string
    {
        if ($value >= $highThreshold) {
            return 'HIGH';
        } elseif ($value >= $lowThreshold) {
            return 'MEDIUM';
        } else {
            return 'LOW';
        }
    }
}
