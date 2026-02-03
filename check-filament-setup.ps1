#!/usr/bin/env pwsh
# Script de verification Filament + Vite

Write-Host "=============================================================" -ForegroundColor Cyan
Write-Host "    Verification Configuration Filament v4 + Vite           " -ForegroundColor Cyan
Write-Host "=============================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Verifier fichiers de configuration
Write-Host "1. Fichiers de configuration..." -ForegroundColor Yellow
$configFiles = @(
    "vite.config.js",
    "postcss.config.js",
    "tailwind.config.js",
    "resources/css/app.css",
    "resources/css/filament/admin/theme.css"
)

foreach ($file in $configFiles) {
    if (Test-Path $file) {
        Write-Host "   OK $file" -ForegroundColor Green
    } else {
        Write-Host "   MANQUANT $file" -ForegroundColor Red
    }
}

Write-Host ""

# 2. Verifier assets compiles
Write-Host "2. Assets compiles..." -ForegroundColor Yellow
if (Test-Path "public/build/manifest.json") {
    Write-Host "   OK manifest.json" -ForegroundColor Green
    
    $manifest = Get-Content "public/build/manifest.json" | ConvertFrom-Json
    
    if ($manifest."resources/css/app.css") {
        Write-Host "   OK Frontend CSS" -ForegroundColor Green
    }
    
    if ($manifest."resources/css/filament/admin/theme.css") {
        Write-Host "   OK Filament Theme CSS" -ForegroundColor Green
    }
    
    if ($manifest."resources/js/app.js") {
        Write-Host "   OK App JS" -ForegroundColor Green
    }
} else {
    Write-Host "   MANQUANT manifest.json" -ForegroundColor Red
    Write-Host "   Executez: npm run build" -ForegroundColor Yellow
}

Write-Host ""

# 3. Verifier serveur Vite
Write-Host "3. Serveur Vite..." -ForegroundColor Yellow
if (Test-Path "public/hot") {
    $hotUrl = Get-Content "public/hot"
    Write-Host "   OK Vite Dev Server: $hotUrl" -ForegroundColor Green
} else {
    Write-Host "   Mode production (OK)" -ForegroundColor Blue
}

Write-Host ""
Write-Host "=============================================================" -ForegroundColor Cyan
Write-Host "Commandes utiles:" -ForegroundColor White
Write-Host "  npm run dev        - Mode developpement" -ForegroundColor Gray
Write-Host "  npm run build      - Compiler pour production" -ForegroundColor Gray
Write-Host "=============================================================" -ForegroundColor Cyan
