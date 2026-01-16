<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel - AI-Powered CI/CD Demo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 60px;
            max-width: 800px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #667eea;
            font-size: 3em;
            margin-bottom: 20px;
            text-align: center;
        }

        h2 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 30px;
            text-align: center;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .feature {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
        }

        .feature-icon {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .feature-title {
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .feature-desc {
            color: #666;
            font-size: 0.9em;
        }

        .version {
            text-align: center;
            margin-top: 40px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🤖 Laravel AI CI/CD</h1>
        <h2>AI-Powered Testing & Deployment</h2>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">🎯</div>
                <div class="feature-title">Smart Test Selection</div>
                <div class="feature-desc">AI selects only relevant tests, reducing CI/CD time by up to 90%</div>
            </div>

            <div class="feature">
                <div class="feature-icon">🔮</div>
                <div class="feature-title">Failure Prediction</div>
                <div class="feature-desc">Predict build failures before running tests with 85% accuracy</div>
            </div>

            <div class="feature">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Parallel Execution</div>
                <div class="feature-desc">Run tests in parallel for maximum performance</div>
            </div>

            <div class="feature">
                <div class="feature-icon">📊</div>
                <div class="feature-title">Analytics & Insights</div>
                <div class="feature-desc">Detailed reports on test coverage and build performance</div>
            </div>
        </div>

        <div class="version">
            Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
        </div>
    </div>
</body>

</html>
