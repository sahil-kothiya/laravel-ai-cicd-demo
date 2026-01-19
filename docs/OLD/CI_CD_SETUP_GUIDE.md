# 🚀 CI/CD Pipeline Setup Guide

**Step-by-Step Guide to Create GitHub Repository and Deploy CI/CD Pipelines**

---

## 📋 What We're Building

Two CI/CD pipelines to demonstrate the difference:

1. **Traditional Pipeline** (`traditional-pipeline.yml`) - Runs ALL tests (slow)
2. **AI-Powered Pipeline** (`ai-pipeline.yml`) - Runs ONLY affected tests (fast)

---

## ✅ Step 1: Initialize Git Repository

Open PowerShell in your project directory and run:

```powershell
# Initialize Git
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit: AI-Powered CI/CD Demo"
```

---

## ✅ Step 2: Create GitHub Repository

### Option A: Using GitHub Website (Easiest)

1. Go to [https://github.com/new](https://github.com/new)
2. Fill in the details:
   - **Repository name**: `laravel-ai-cicd-demo`
   - **Description**: `AI-Powered CI/CD Pipeline Demo for Laravel`
   - **Visibility**: Public (or Private if preferred)
   - **❌ DO NOT** check "Initialize with README" (we already have code)
3. Click **"Create repository"**

### Option B: Using GitHub CLI (Advanced)

```powershell
# Install GitHub CLI first: winget install GitHub.cli
gh repo create laravel-ai-cicd-demo --public --source=. --remote=origin
```

---

## ✅ Step 3: Connect Local Repository to GitHub

After creating the GitHub repo, you'll see commands like this:

```powershell
# Add GitHub as remote origin
git remote add origin https://github.com/YOUR_USERNAME/laravel-ai-cicd-demo.git

# Rename branch to main (if not already)
git branch -M main

# Push code to GitHub
git push -u origin main
```

**Replace `YOUR_USERNAME`** with your actual GitHub username!

---

## ✅ Step 4: Verify Workflows Are Active

1. Go to your GitHub repository
2. Click on **"Actions"** tab
3. You should see both workflows listed:
   - ✅ Traditional CI/CD Pipeline (OLD WAY)
   - ✅ AI-Powered CI/CD Pipeline (NEW WAY)

---

## ✅ Step 5: Trigger the Pipelines

### Method 1: Make a Change and Push

```powershell
# Make a small change
echo "// Demo change" >> app/Http/Controllers/UserController.php

# Commit and push
git add .
git commit -m "Demo: Trigger CI/CD pipelines"
git push
```

### Method 2: Manual Trigger

1. Go to **Actions** tab on GitHub
2. Click on a workflow name
3. Click **"Run workflow"** button
4. Select branch and click **"Run workflow"**

---

## 📊 Step 6: Compare the Results

Once both pipelines run, you'll see:

### Traditional Pipeline Results:
```
⏰ Time: 15+ minutes
🧪 Tests Run: 500
💸 Cost: $0.128
😤 Feeling: Frustrated
```

### AI-Powered Pipeline Results:
```
⏰ Time: 1-2 minutes
🧪 Tests Run: 12
💸 Cost: $0.012
😊 Feeling: Happy!
```

---

## 🎯 Understanding the Workflows

### Traditional Pipeline (traditional-pipeline.yml)

**What it does:**
- Runs ALL 500 tests
- No intelligence
- Wastes time and money

**Steps:**
1. Checkout code
2. Setup PHP
3. Install dependencies
4. Run ALL tests (no filtering)
5. Report time wasted

### AI-Powered Pipeline (ai-pipeline.yml)

**What it does:**
- Uses AI to predict failures
- Selects only affected tests
- Runs tests in parallel
- Saves time and money

**Steps:**
1. **AI Failure Prediction** - Predicts if build will fail (30 sec)
2. **AI Test Selection** - Selects only affected tests (1 min)
3. **Run Selected Tests** - Runs 90% fewer tests (1 min)
4. **Comparison Report** - Shows savings

---

## 🔍 Viewing Pipeline Logs

1. Go to **Actions** tab
2. Click on a workflow run
3. Click on a job name (e.g., "AI Failure Prediction")
4. View detailed logs with AI analysis

**You'll see output like:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  🤖 AI analyzing your changes...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Prediction: PASS ✅
Confidence: 95%
Risk Factors: None
Selected Tests: 12 of 500 (97.6% reduction)
```

---

## 🎬 Demo Script for Presentation

### Live Demo Flow:

**1. Show Traditional Pipeline (5 min)**
```powershell
# In VS Code, open: .github/workflows/traditional-pipeline.yml
# Show the workflow
# Point out: "Runs ALL tests every time"
```

**2. Show AI Pipeline (5 min)**
```powershell
# Open: .github/workflows/ai-pipeline.yml
# Point out the AI steps:
#   - Failure prediction
#   - Test selection
#   - Smart execution
```

**3. Make a Change and Push (5 min)**
```powershell
# Make tiny change
echo "// Updated for demo" >> app/Http/Controllers/UserController.php

# Commit
git add .
git commit -m "Demo: Small UserController change"

# Push (triggers both workflows)
git push
```

**4. Show GitHub Actions (10 min)**
- Open GitHub → Actions tab
- Show both pipelines running side-by-side
- Point to timing difference
- Show AI predictions in logs

**5. Show Results (5 min)**
- Traditional: 15 min, 500 tests, $0.128
- AI-Powered: 1.5 min, 12 tests, $0.012
- Calculate savings: 90% time, 90% cost

---

## 🛠️ Troubleshooting

### Issue: "Workflows not showing up"

**Solution:**
```powershell
# Workflows must be in .github/workflows/
ls .github/workflows/

# Should show:
#   traditional-pipeline.yml
#   ai-pipeline.yml
```

### Issue: "Tests failing"

**Solution:**
```powershell
# Make sure .env.example exists
ls .env.example

# Test locally first
cp .env.example .env
php artisan key:generate
vendor/bin/phpunit
```

### Issue: "Permission denied"

**Solution:**
```powershell
# Check GitHub Actions is enabled
# Go to: Settings → Actions → General
# Enable: "Allow all actions and reusable workflows"
```

---

## 📈 Monitoring Your Pipelines

### GitHub Actions Dashboard

View all runs:
```
https://github.com/YOUR_USERNAME/laravel-ai-cicd-demo/actions
```

### Metrics to Track

1. **Pipeline Duration**: Traditional vs AI
2. **Cost per Run**: $0.128 vs $0.012
3. **Tests Run**: 500 vs 12
4. **Success Rate**: Both should be 100%

---

## 🎯 Next Steps

### After Demo:

1. ✅ Add more AI features (test prioritization, flaky test detection)
2. ✅ Integrate with Slack for notifications
3. ✅ Add deployment step after tests pass
4. ✅ Create staging/production environments
5. ✅ Add code coverage reports

### Customize for Your Project:

```yaml
# Edit .github/workflows/ai-pipeline.yml

# Change PHP version
env:
  PHP_VERSION: '8.2'  # Your PHP version

# Add database tests
services:
  mysql:
    image: mysql:8.0
    env:
      MYSQL_DATABASE: testing
```

---

## 📚 Additional Resources

- **GitHub Actions Docs**: https://docs.github.com/actions
- **Laravel Testing**: https://laravel.com/docs/testing
- **This Project's Docs**: See `docs/` folder

---

## 🎉 Success Checklist

- [ ] Git repository initialized
- [ ] GitHub repository created
- [ ] Code pushed to GitHub
- [ ] Both workflows visible in Actions tab
- [ ] Workflows run successfully
- [ ] Can see AI predictions in logs
- [ ] Can compare traditional vs AI results
- [ ] Ready to present demo!

---

**🚀 You're all set! Your CI/CD pipelines are live and ready to impress!**

---

## Quick Commands Reference

```powershell
# Initialize and push
git init
git add .
git commit -m "Initial commit: AI-Powered CI/CD Demo"
git remote add origin https://github.com/YOUR_USERNAME/laravel-ai-cicd-demo.git
git branch -M main
git push -u origin main

# Make changes and trigger pipelines
echo "// Demo" >> app/Http/Controllers/UserController.php
git add .
git commit -m "Demo: Trigger pipelines"
git push

# View status
git status
git log --oneline -5
git remote -v
```
