#!/usr/bin/env pwsh
# AI CI/CD Demo Script
# This script demonstrates how different code changes trigger different tests

Write-Host "`n╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║         AI-Powered CI/CD Test Selection Demo                  ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

function Show-TestSelection {
    param([string]$Scenario)
    
    Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow
    Write-Host "  Scenario: $Scenario" -ForegroundColor Yellow
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Yellow
    Write-Host ""
    
    $result = php artisan ai:select-tests --json | ConvertFrom-Json
    
    Write-Host "📊 Test Selection Summary:" -ForegroundColor Cyan
    Write-Host "  Total Tests in Suite: $($result.total_tests)" -ForegroundColor White
    Write-Host "  Selected Tests: $($result.selected_tests)" -ForegroundColor Green
    Write-Host "  Reduction: $($result.reduction_percentage)%" -ForegroundColor Magenta
    Write-Host ""
    
    # Show Critical Tests
    $criticalCount = $result.breakdown.critical
    Write-Host "🔴 CRITICAL TESTS (Always Run): $criticalCount tests" -ForegroundColor Red
    $result.full_test_paths | ForEach-Object {
        $criticalTests = @("Tests\Unit\UserTest", "Tests\Unit\ProductTest", "Tests\Unit\OrderTest", 
                          "Tests\Unit\SecurityTest", "Tests\Unit\IntegrationTest")
        if ($criticalTests -contains $_) {
            Write-Host "  🔴 $_" -ForegroundColor Red
        }
    }
    
    # Show Change-Based Tests
    $changeCount = $result.selected_tests - $criticalCount
    if ($changeCount -gt 0) {
        Write-Host "`n✅ CHANGE-BASED TESTS (AI Selected): $changeCount tests" -ForegroundColor Green
        $result.full_test_paths | ForEach-Object {
            $criticalTests = @("Tests\Unit\UserTest", "Tests\Unit\ProductTest", "Tests\Unit\OrderTest", 
                              "Tests\Unit\SecurityTest", "Tests\Unit\IntegrationTest")
            if ($criticalTests -notcontains $_) {
                Write-Host "  ✅ $_" -ForegroundColor Green
            }
        }
    }
    
    Write-Host ""
    Write-Host "⚡ Time Savings: $($result.estimated_time_savings) minutes" -ForegroundColor Magenta
    Write-Host ""
}

# Menu
Write-Host "Choose a scenario to test:" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. 📝 Change UserController.php" -ForegroundColor White
Write-Host "2. 👤 Change User Model" -ForegroundColor White
Write-Host "3. 📦 Change ProductController.php" -ForegroundColor White
Write-Host "4. 🔧 Change UserService.php" -ForegroundColor White
Write-Host "5. 📚 Change README.md (docs only)" -ForegroundColor White
Write-Host "6. 🔄 Change Multiple Files" -ForegroundColor White
Write-Host "7. 📊 Show Current Selection" -ForegroundColor White
Write-Host "8. 🧹 Reset All Changes" -ForegroundColor White
Write-Host ""

$choice = Read-Host "Enter choice (1-8)"

switch ($choice) {
    "1" {
        Write-Host "`n🔨 Adding change to UserController.php..." -ForegroundColor Yellow
        "// Feature: Added new validation logic - $(Get-Date)" | Add-Content app/Http/Controllers/UserController.php
        Show-TestSelection "Modified UserController.php"
    }
    "2" {
        Write-Host "`n🔨 Adding change to User.php..." -ForegroundColor Yellow
        "// Feature: Added new user field - $(Get-Date)" | Add-Content app/Models/User.php
        Show-TestSelection "Modified User Model"
    }
    "3" {
        Write-Host "`n🔨 Adding change to ProductController.php..." -ForegroundColor Yellow
        "// Feature: Updated product listing - $(Get-Date)" | Add-Content app/Http/Controllers/ProductController.php
        Show-TestSelection "Modified ProductController.php"
    }
    "4" {
        Write-Host "`n🔨 Adding change to UserService.php..." -ForegroundColor Yellow
        "// Feature: Enhanced user service - $(Get-Date)" | Add-Content app/Services/UserService.php
        Show-TestSelection "Modified UserService.php"
    }
    "5" {
        Write-Host "`n🔨 Adding change to README.md..." -ForegroundColor Yellow
        "`n## Updated documentation - $(Get-Date)" | Add-Content README.md
        Show-TestSelection "Modified Documentation Only"
    }
    "6" {
        Write-Host "`n🔨 Adding changes to multiple files..." -ForegroundColor Yellow
        "// Update - $(Get-Date)" | Add-Content app/Models/User.php
        "// Update - $(Get-Date)" | Add-Content app/Models/Product.php
        "// Update - $(Get-Date)" | Add-Content app/Services/UserService.php
        Show-TestSelection "Modified Multiple Files"
    }
    "7" {
        Show-TestSelection "Current State"
    }
    "8" {
        Write-Host "`n🧹 Resetting all test changes..." -ForegroundColor Yellow
        git checkout -- app/Http/Controllers/UserController.php
        git checkout -- app/Models/User.php
        git checkout -- app/Models/Product.php
        git checkout -- app/Http/Controllers/ProductController.php
        git checkout -- app/Services/UserService.php
        git checkout -- README.md
        Write-Host "✅ Reset complete!" -ForegroundColor Green
    }
    default {
        Write-Host "❌ Invalid choice!" -ForegroundColor Red
    }
}

Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host ""
Write-Host "💡 Next Steps:" -ForegroundColor Yellow
Write-Host "  1. Review the selected tests above" -ForegroundColor White
Write-Host "  2. Commit your changes: git add . && git commit -m 'test'" -ForegroundColor White
Write-Host "  3. Push to GitHub: git push origin main" -ForegroundColor White
Write-Host "  4. Check GitHub Actions to see the same results!" -ForegroundColor White
Write-Host ""
