# 🚀 Guide Rapide - Utilisation des Assets Vite

## 📋 Deux Modes d'Utilisation

### 🔧 Mode Développement (Hot Reload)
**Quand l'utiliser :** Pendant que vous développez et modifiez les fichiers CSS/JS

```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite (Hot Reload)
npm run dev
```

**Ce qui se passe :**
- ✅ Fichier `public/hot` est créé automatiquement
- ✅ Assets chargés depuis Vite Dev Server (`http://localhost:5173`)
- ✅ Modifications CSS/JS visibles instantanément (sans F5)
- ⚠️ **Vite DOIT rester actif** sinon pas de styles !

---

### 📦 Mode Production (Fichiers Compilés)
**Quand l'utiliser :** Pour tester le site comme en production, ou avant de déployer

```bash
# 1. Compiler les assets
npm run build

# 2. IMPORTANT: Supprimer public/hot
Remove-Item public\hot -Force

# 3. Démarrer Laravel
php artisan serve
```

**Ce qui se passe :**
- ✅ Pas de fichier `public/hot`
- ✅ Assets chargés depuis `public/build/assets/`
- ✅ Fonctionne sans `npm run dev` actif
- ✅ Fichiers minifiés et optimisés

---

## ⚠️ RÈGLE D'OR

> **Après avoir arrêté `npm run dev`, TOUJOURS supprimer `public/hot` !**

```bash
# Arrêter Vite (Ctrl+C)
# Puis IMMÉDIATEMENT :
Remove-Item public\hot -Force
```

---

## 🔧 Scripts Utiles

### Fix Rapide (styles manquants)
```bash
.\fix-frontend-styles.ps1
```

### Vérification Complète
```bash
.\check-filament-setup.ps1
```

### Passer en Mode DEV
```bash
npm run dev
# public/hot est créé automatiquement
```

### Passer en Mode PROD
```bash
npm run build
Remove-Item public\hot -Force
php artisan config:clear
```

---

## 🐛 Symptômes et Solutions

### ❌ Symptôme: Frontend = HTML sans styles

**Diagnostic :**
```bash
Test-Path public\hot
# Si TRUE = C'est le problème !
```

**Solution :**
```bash
Remove-Item public\hot -Force
php artisan config:clear
# Puis Ctrl+Shift+R dans le navigateur
```

---

### ❌ Symptôme: Styles ne se mettent pas à jour

**En mode DEV :**
```bash
# Vérifier que Vite tourne
# Vous devez voir: "VITE ready in X ms"
```

**En mode PROD :**
```bash
# Recompiler
npm run build
php artisan config:clear
```

---

### ❌ Symptôme: Erreur "ERR_CONNECTION_REFUSED"

**Cause :** `public/hot` existe mais Vite n'est pas actif

**Solution :**
```bash
Remove-Item public\hot -Force
```

---

## 📊 Tableau de Décision

| Situation | Action | Commande |
|-----------|--------|----------|
| Je développe activement | Mode DEV | `npm run dev` |
| Je teste avant commit | Mode PROD | `npm run build` puis supprimer `public/hot` |
| Je déploie en production | Mode PROD | `npm run build` (jamais de `public/hot`) |
| Styles manquants | Fix | `.\fix-frontend-styles.ps1` |
| Arrêt de `npm run dev` | Nettoyage | `Remove-Item public\hot -Force` |

---

## 🎯 Workflow Recommandé

### Début de Journée
```bash
git pull
composer install
npm install
npm run dev          # Lancer Vite
# Dans un autre terminal:
php artisan serve    # Lancer Laravel
```

### Pendant le Développement
- ✅ Gardez `npm run dev` actif
- ✅ Modifiez vos fichiers CSS/JS
- ✅ Pas besoin de F5, les changements apparaissent automatiquement

### Avant de Commit
```bash
# Ctrl+C pour arrêter npm run dev
Remove-Item public\hot -Force
npm run build
git add .
git commit -m "Votre message"
```

### Fin de Journée
```bash
# Ctrl+C pour arrêter npm run dev
Remove-Item public\hot -Force
# C'est tout !
```

---

## 🔍 Vérification Rapide

```bash
# État actuel du système
if (Test-Path public\hot) {
    Write-Host "MODE: DEV (Vite actif ou problème)" -ForegroundColor Yellow
} else {
    Write-Host "MODE: PROD (OK)" -ForegroundColor Green
}
```

---

## 💡 Astuces

### 1. Alias PowerShell Utiles
Ajoutez dans votre profil PowerShell :

```powershell
function dev { npm run dev }
function build { npm run build; Remove-Item public\hot -Force -ErrorAction SilentlyContinue }
function fix-styles { .\fix-frontend-styles.ps1 }
```

### 2. Git Ignore
Vérifiez que `.gitignore` contient :
```
/public/hot
/public/build
```

### 3. Commande Tout-en-Un
```bash
# Nettoyer + Compiler + Vérifier
npm run build; Remove-Item public\hot -Force; php artisan optimize:clear; .\check-filament-setup.ps1
```

---

## 📞 Support Rapide

**Frontend sans styles ?**
```bash
.\fix-frontend-styles.ps1
```

**Vérifier la config ?**
```bash
.\check-filament-setup.ps1
```

**Tout réinitialiser ?**
```bash
Remove-Item public\hot -Force
Remove-Item -Recurse public\build
npm run build
php artisan optimize:clear
```

---

**Date:** 11 octobre 2025  
**Problème résolu:** Frontend HTML sans CSS (fichier `public/hot` orphelin)  
**Solution:** Suppression de `public/hot` après arrêt de Vite
