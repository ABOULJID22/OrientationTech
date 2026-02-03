#!/usr/bin/env pwsh
# Script pour resoudre le probleme de styles frontend

Write-Host "`n=============================================================" -ForegroundColor Cyan
Write-Host "  Resolution du probleme de styles frontend" -ForegroundColor Cyan
Write-Host "=============================================================" -ForegroundColor Cyan

# 1. Supprimer public/hot
Write-Host "`n1. Verification du fichier public/hot..." -ForegroundColor Yellow
if (Test-Path public\hot) {
    $hotContent = Get-Content public\hot
    Write-Host "   TROUVE: $hotContent" -ForegroundColor Red
    Remove-Item public\hot -Force
    Write-Host "   OK: Fichier supprime!" -ForegroundColor Green
} else {
    Write-Host "   OK: Fichier n'existe pas (deja en mode production)" -ForegroundColor Green
}

# 2. Nettoyer les caches
Write-Host "`n2. Nettoyage des caches..." -ForegroundColor Yellow
php artisan config:clear | Out-Null
Write-Host "   OK: Config cache cleared" -ForegroundColor Green
php artisan view:clear | Out-Null
Write-Host "   OK: View cache cleared" -ForegroundColor Green
php artisan route:clear | Out-Null
Write-Host "   OK: Route cache cleared" -ForegroundColor Green

# 3. Verifier les assets
Write-Host "`n3. Verification des assets compiles..." -ForegroundColor Yellow
if (Test-Path public\build\manifest.json) {
    $manifest = Get-Content public\build\manifest.json | ConvertFrom-Json
    
    if ($manifest."resources/css/app.css") {
        $file = "public\build\" + $manifest."resources/css/app.css".file
        $size = [math]::Round((Get-Item $file).Length / 1KB, 2)
        Write-Host "   OK: Frontend CSS ($size KB)" -ForegroundColor Green
    } else {
        Write-Host "   ERREUR: Frontend CSS manquant!" -ForegroundColor Red
    }
    
    if ($manifest."resources/css/filament/admin/theme.css") {
        $file = "public\build\" + $manifest."resources/css/filament/admin/theme.css".file
        $size = [math]::Round((Get-Item $file).Length / 1KB, 2)
        Write-Host "   OK: Filament Theme CSS ($size KB)" -ForegroundColor Green
    } else {
        Write-Host "   ERREUR: Filament Theme CSS manquant!" -ForegroundColor Red
    }
} else {
    Write-Host "   ERREUR: Manifest manquant!" -ForegroundColor Red
    Write-Host "   SOLUTION: Executez 'npm run build'" -ForegroundColor Yellow
}

Write-Host "`n=============================================================" -ForegroundColor Cyan
Write-Host "  Correction terminee!" -ForegroundColor Green
Write-Host "=============================================================" -ForegroundColor Cyan
Write-Host "`nProchaines etapes:" -ForegroundColor White
Write-Host "  1. Rafraichissez votre navigateur (Ctrl+Shift+R)" -ForegroundColor Gray
Write-Host "  2. Verifiez que les styles s'affichent correctement" -ForegroundColor Gray
Write-Host "  3. Si le probleme persiste, executez: npm run build`n" -ForegroundColor Gray
