# Script de Vérification - Version Tailwind Unifiée
# Vérifie que seul Tailwind v3 est installé

Write-Host "=== VERIFICATION TAILWIND VERSION ===" -ForegroundColor Cyan
Write-Host ""

# 1. Vérifier npm list
Write-Host "1. Versions installées:" -ForegroundColor Yellow
npm list tailwindcss 2>$null

Write-Host ""
Write-Host "2. Package.json:" -ForegroundColor Yellow
$packageJson = Get-Content package.json | ConvertFrom-Json
Write-Host "   tailwindcss: $($packageJson.devDependencies.tailwindcss)" -ForegroundColor Green
Write-Host "   @tailwindcss/forms: $($packageJson.devDependencies.'@tailwindcss/forms')" -ForegroundColor Green
Write-Host "   @tailwindcss/typography: $($packageJson.devDependencies.'@tailwindcss/typography')" -ForegroundColor Green

if ($packageJson.devDependencies.'@tailwindcss/vite') {
    Write-Host "   @tailwindcss/vite: $($packageJson.devDependencies.'@tailwindcss/vite')" -ForegroundColor Red
    Write-Host "   ❌ ERREUR: @tailwindcss/vite (v4) détecté!" -ForegroundColor Red
} else {
    Write-Host "   @tailwindcss/vite: NOT FOUND" -ForegroundColor Green
    Write-Host "   ✅ Pas de Tailwind v4" -ForegroundColor Green
}

Write-Host ""
Write-Host "3. Fichier public/hot:" -ForegroundColor Yellow
if (Test-Path "public\hot") {
    Write-Host "   ❌ EXISTE - À SUPPRIMER!" -ForegroundColor Red
} else {
    Write-Host "   ✅ N'existe pas (OK)" -ForegroundColor Green
}

Write-Host ""
Write-Host "4. Fichiers CSS build:" -ForegroundColor Yellow
if (Test-Path "public\build\manifest.json") {
    $manifest = Get-Content "public\build\manifest.json" | ConvertFrom-Json
    Write-Host "   ✅ manifest.json existe" -ForegroundColor Green
    
    $appCss = $manifest.'resources/css/app.css'.file
    $themeCss = $manifest.'resources/css/filament/admin/theme.css'.file
    
    if ($appCss) {
        $size = [math]::Round((Get-Item "public\build\$appCss").Length / 1KB, 2)
        Write-Host "   ✅ Frontend CSS: $appCss ($size KB)" -ForegroundColor Green
    }
    
    if ($themeCss) {
        $size = [math]::Round((Get-Item "public\build\$themeCss").Length / 1KB, 2)
        Write-Host "   ✅ Filament CSS: $themeCss ($size KB)" -ForegroundColor Green
    }
} else {
    Write-Host "   ❌ Pas de build - Exécutez: npm run build" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== RESULTAT ===" -ForegroundColor Cyan
if (-not (Test-Path "public\hot") -and -not $packageJson.devDependencies.'@tailwindcss/vite') {
    Write-Host "✅ CONFIGURATION CORRECTE - Tailwind v3 uniquement" -ForegroundColor Green
} else {
    Write-Host "❌ CONFIGURATION INCORRECTE - Corriger les erreurs ci-dessus" -ForegroundColor Red
}
