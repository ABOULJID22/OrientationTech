# Script de Test Rapide - Vérification Build
Write-Host "=== VERIFICATION BUILD TAILWIND V3 ===" -ForegroundColor Cyan
Write-Host ""

# Vérifier que le build existe
if (-not (Test-Path "public\build\manifest.json")) {
    Write-Host "ERREUR: Pas de build! Executez: npm run build" -ForegroundColor Red
    exit 1
}

$manifest = Get-Content "public\build\manifest.json" | ConvertFrom-Json

Write-Host "1. Frontend CSS (app.css):" -ForegroundColor Yellow
$appCss = $manifest.'resources/css/app.css'.file
if ($appCss) {
    $size = [math]::Round((Get-Item "public\build\$appCss").Length / 1KB, 2)
    Write-Host "   $appCss ($size KB)" -ForegroundColor Green
    
    if ($size -lt 50) {
        Write-Host "   ATTENTION: Taille trop petite! (devrait etre ~73 KB)" -ForegroundColor Yellow
    } else {
        Write-Host "   Taille OK" -ForegroundColor Green
    }
} else {
    Write-Host "   ERREUR: app.css manquant!" -ForegroundColor Red
}

Write-Host ""
Write-Host "2. Filament CSS (theme.css):" -ForegroundColor Yellow
$themeCss = $manifest.'resources/css/filament/admin/theme.css'.file
if ($themeCss) {
    $size = [math]::Round((Get-Item "public\build\$themeCss").Length / 1KB, 2)
    Write-Host "   $themeCss ($size KB)" -ForegroundColor Green
    
    if ($size -lt 50) {
        Write-Host "   ATTENTION: Taille trop petite! (devrait etre ~74 KB)" -ForegroundColor Yellow
    } else {
        Write-Host "   Taille OK" -ForegroundColor Green
    }
} else {
    Write-Host "   ERREUR: theme.css manquant!" -ForegroundColor Red
}

Write-Host ""
Write-Host "3. Fichier public/hot:" -ForegroundColor Yellow
if (Test-Path "public\hot") {
    Write-Host "   ATTENTION: public/hot existe (supprimez apres npm run dev)" -ForegroundColor Yellow
} else {
    Write-Host "   OK - Pas de fichier hot" -ForegroundColor Green
}

Write-Host ""
Write-Host "4. Tailwind version:" -ForegroundColor Yellow
$packageJson = Get-Content "package.json" | ConvertFrom-Json
$twVersion = $packageJson.devDependencies.tailwindcss
Write-Host "   tailwindcss: $twVersion" -ForegroundColor Green

if ($packageJson.devDependencies.'tailwindcss-v4') {
    Write-Host "   ATTENTION: tailwindcss-v4 installe!" -ForegroundColor Yellow
}
if ($packageJson.devDependencies.'@tailwindcss/vite') {
    Write-Host "   ATTENTION: @tailwindcss/vite installe!" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== INSTRUCTIONS TEST ===" -ForegroundColor Cyan
Write-Host "1. Ouvrez: http://127.0.0.1:8000 (Frontend)" -ForegroundColor White
Write-Host "2. Ouvrez: http://127.0.0.1:8000/admin (Filament)" -ForegroundColor White
Write-Host "3. Appuyez sur: Ctrl + Shift + R (vider cache)" -ForegroundColor White
Write-Host "4. F12 > Network > Verifiez que les CSS chargent" -ForegroundColor White
Write-Host ""
