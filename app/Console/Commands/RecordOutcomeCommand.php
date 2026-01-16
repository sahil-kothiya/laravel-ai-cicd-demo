<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RecordOutcomeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:record-outcome 
                          {--prediction= : The AI prediction (PASS/FAIL)}
                          {--actual= : The actual outcome (success/failure)}
                          {--confidence= : The prediction confidence percentage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record build outcome for AI model training';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $prediction = $this->option('prediction');
        $actual = $this->option('actual');
        $confidence = $this->option('confidence');

        // Normalize actual outcome
        $actualNormalized = in_array($actual, ['success', 'passed', 'pass']) ? 'PASS' : 'FAIL';

        // Create training data entry
        $trainingData = [
            'timestamp' => now()->toIso8601String(),
            'prediction' => $prediction,
            'actual' => $actualNormalized,
            'confidence' => (float) $confidence,
            'correct' => ($prediction === $actualNormalized),
            'git_sha' => trim(shell_exec('git rev-parse HEAD') ?: 'unknown'),
            'git_branch' => trim(shell_exec('git rev-parse --abbrev-ref HEAD') ?: 'unknown'),
        ];

        // Ensure training data directory exists
        $trainingDataPath = 'ai/training-data';
        if (!Storage::exists($trainingDataPath)) {
            Storage::makeDirectory($trainingDataPath);
        }

        // Append to training data file
        $filename = 'ai/training-data/' . date('Y-m') . '.json';
        $existingData = [];
        
        if (Storage::exists($filename)) {
            $existingData = json_decode(Storage::get($filename), true) ?: [];
        }

        $existingData[] = $trainingData;
        Storage::put($filename, json_encode($existingData, JSON_PRETTY_PRINT));

        // Update accuracy metrics
        $this->updateAccuracyMetrics($trainingData);

        $this->info('✅ Build outcome recorded successfully');
        $this->info("Prediction: {$prediction} | Actual: {$actualNormalized} | " . 
                   ($trainingData['correct'] ? '✓ Correct' : '✗ Incorrect'));

        return Command::SUCCESS;
    }

    /**
     * Update accuracy metrics
     */
    private function updateAccuracyMetrics(array $newData): void
    {
        $metricsFile = 'ai/metrics.json';
        $metrics = [];

        if (Storage::exists($metricsFile)) {
            $metrics = json_decode(Storage::get($metricsFile), true) ?: [];
        }

        // Initialize metrics if not exist
        if (!isset($metrics['total_predictions'])) {
            $metrics = [
                'total_predictions' => 0,
                'correct_predictions' => 0,
                'accuracy' => 0,
                'last_updated' => null,
            ];
        }

        // Update metrics
        $metrics['total_predictions']++;
        if ($newData['correct']) {
            $metrics['correct_predictions']++;
        }
        $metrics['accuracy'] = round(($metrics['correct_predictions'] / $metrics['total_predictions']) * 100, 2);
        $metrics['last_updated'] = now()->toIso8601String();

        Storage::put($metricsFile, json_encode($metrics, JSON_PRETTY_PRINT));
    }
}
