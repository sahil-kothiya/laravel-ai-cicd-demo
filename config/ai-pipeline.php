<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Pipeline Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI-powered CI/CD optimizations including test selection,
    | failure prediction, and build optimization.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Test Selection
    |--------------------------------------------------------------------------
    |
    | Configure intelligent test selection based on code changes.
    |
    */
    'test_selection' => [
        // Enable/disable AI test selection
        'enabled' => env('AI_TEST_SELECTION_ENABLED', true),

        // Confidence threshold (0.0 - 1.0)
        // Tests with impact score below this won't be selected
        'confidence_threshold' => env('AI_TEST_CONFIDENCE_THRESHOLD', 0.75),

        // Maximum number of tests to select
        'max_tests' => env('AI_TEST_MAX_TESTS', 100),

        // Always run critical tests regardless of changes
        'always_run_critical' => true,

        // Critical test patterns (regex)
        'critical_patterns' => [
            '/Auth.*Test/',
            '/Payment.*Test/',
            '/Security.*Test/',
            '/Integration.*Test/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Failure Prediction
    |--------------------------------------------------------------------------
    |
    | Configure ML-based build failure prediction.
    |
    */
    'failure_prediction' => [
        // Enable/disable failure prediction
        'enabled' => env('AI_FAILURE_PREDICTION_ENABLED', true),

        // Model file path
        'model_path' => storage_path('ai/models/failure_predictor.json'),

        // Training data path
        'training_data_path' => storage_path('ai/training-data/build-history.json'),

        // Confidence threshold for predictions
        'confidence_threshold' => env('AI_PREDICTION_CONFIDENCE', 0.7),

        // Automatically retrain model after N builds
        'retrain_after_builds' => 50,

        // Feature extraction settings
        'features' => [
            // File change thresholds
            'large_change_threshold' => 15, // files
            'large_diff_threshold' => 500,  // lines

            // Complexity thresholds
            'high_complexity_threshold' => 25,

            // Critical times (higher risk)
            'risky_hours' => [16, 17, 18, 19, 20, 21, 22, 23], // Evening/night
            'risky_days' => [5], // Friday
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Build Optimization
    |--------------------------------------------------------------------------
    |
    | Configure smart build optimizations.
    |
    */
    'build_optimization' => [
        // Enable parallel test execution
        'parallel_tests' => env('AI_PARALLEL_TESTS', true),

        // Number of parallel processes
        'parallel_processes' => env('AI_PARALLEL_PROCESSES', 4),

        // Smart caching
        'cache_dependencies' => true,
        'cache_build_artifacts' => true,

        // Skip build if prediction is highly confident PASS
        'skip_on_confident_pass' => false,
        'skip_confidence_threshold' => 0.95,
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics & Reporting
    |--------------------------------------------------------------------------
    |
    | Configure data collection and reporting.
    |
    */
    'analytics' => [
        // Store build metrics for analysis
        'collect_metrics' => true,

        // Metrics to track
        'metrics' => [
            'build_duration',
            'test_duration',
            'tests_run',
            'tests_selected',
            'prediction_accuracy',
            'time_saved',
        ],

        // Storage path for metrics
        'metrics_path' => storage_path('ai/metrics'),

        // Generate reports
        'generate_reports' => true,
        'report_interval' => 'weekly', // daily, weekly, monthly
    ],

    /*
    |--------------------------------------------------------------------------
    | Machine Learning
    |--------------------------------------------------------------------------
    |
    | Configure ML model training and updates.
    |
    */
    'machine_learning' => [
        // Minimum builds required before training
        'min_training_data' => 100,

        // Model evaluation metrics
        'evaluation_metrics' => [
            'accuracy',
            'precision',
            'recall',
            'f1_score',
        ],

        // Minimum model accuracy to deploy
        'min_accuracy_threshold' => 0.75,

        // Auto-update model when accuracy improves
        'auto_update_model' => env('AI_AUTO_UPDATE_MODEL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    |
    | Configure integrations with CI/CD platforms.
    |
    */
    'integrations' => [
        // GitHub Actions
        'github_actions' => [
            'enabled' => env('GITHUB_ACTIONS', false),
            'token' => env('GITHUB_TOKEN'),
            'repository' => env('GITHUB_REPOSITORY'),
        ],

        // GitLab CI
        'gitlab_ci' => [
            'enabled' => env('GITLAB_CI', false),
            'token' => env('GITLAB_TOKEN'),
            'project_id' => env('CI_PROJECT_ID'),
        ],

        // Jenkins
        'jenkins' => [
            'enabled' => env('JENKINS_HOME') !== null,
            'url' => env('JENKINS_URL'),
            'user' => env('JENKINS_USER'),
            'token' => env('JENKINS_TOKEN'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure notifications for predictions and results.
    |
    */
    'notifications' => [
        // Notify on high-risk predictions
        'notify_on_fail_prediction' => true,
        'notify_threshold' => 0.8, // confidence

        // Notification channels
        'channels' => [
            'slack' => [
                'enabled' => env('SLACK_WEBHOOK_URL') !== null,
                'webhook_url' => env('SLACK_WEBHOOK_URL'),
            ],
            'email' => [
                'enabled' => true,
                'recipients' => ['devops@example.com'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug & Development
    |--------------------------------------------------------------------------
    |
    | Settings for debugging and development.
    |
    */
    'debug' => [
        // Verbose logging
        'verbose' => env('AI_PIPELINE_DEBUG', false),

        // Log file path
        'log_path' => storage_path('logs/ai-pipeline.log'),

        // Save intermediate results for debugging
        'save_intermediate_results' => env('APP_ENV') === 'local',

        // Simulate predictions (for testing)
        'simulate_predictions' => env('AI_SIMULATE', false),
    ],

];
