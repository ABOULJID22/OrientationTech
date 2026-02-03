# ⚠️ RAPPEL IMPORTANT - Vite & public/hot

## 🔴 RÈGLE D'OR

```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│  Après avoir arrêté `npm run dev` (Ctrl+C)                │
│                                                             │
│  TOUJOURS exécuter :                                       │
│                                                             │
│  Remove-Item public\hot -Force                            │
│                                                             │
│  Sinon : Votre frontend n'aura PAS de styles !           │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Fonctionnement de Vite

### Mode DEV (`npm run dev` actif)
```
┌─────────────┐
│ npm run dev │ ← Vite Dev Server actif
└──────┬──────┘
       │
       │ Crée automatiquement
       ▼
┌─────────────┐
│ public/hot  │ ← Contient URL du serveur Vite
└──────┬──────┘
       │
       │ Laravel lit ce fichier
       ▼
┌──────────────────────────────────┐
│ Assets chargés depuis Vite       │
│ http://localhost:5173            │
│ ✅ Hot Reload fonctionne         │
└──────────────────────────────────┘
```

### Mode PROD (Vite arrêté)
```
┌─────────────┐
│ npm run dev │ ← Arrêté (Ctrl+C)
└──────┬──────┘
       │
       │ public/hot existe encore !
       ▼
┌─────────────┐
│ public/hot  │ ⚠️ PROBLÈME : Fichier orphelin
└──────┬──────┘
       │
       │ Laravel essaie de charger depuis Vite
       ▼
┌──────────────────────────────────┐
│ ERR_CONNECTION_REFUSED           │
│ ❌ Aucun style ne se charge      │
│ ❌ Frontend = HTML seulement     │
└──────────────────────────────────┘

SOLUTION :
┌─────────────────────────────────┐
│ Remove-Item public\hot -Force   │
└──────┬──────────────────────────┘
       │
       │ Laravel utilise les fichiers compilés
       ▼
┌──────────────────────────────────┐
│ Assets depuis public/build/      │
│ ✅ Styles fonctionnent           │
│ ✅ Frontend OK                   │
└──────────────────────────────────┘
```

---

## ✅ Workflow Correct

### Démarrer le Développement
```bash
npm run dev          # ✅ Crée public/hot automatiquement
# Développez normalement...
```

### Arrêter le Développement
```bash
# Ctrl+C pour arrêter npm run dev
Remove-Item public\hot -Force    # ✅ IMPORTANT !
```

### Alternative : Script Automatique
```bash
.\fix-frontend-styles.ps1    # ✅ Fait tout automatiquement
```

---

## 🎯 Diagnostic Rapide

### Comment savoir si j'ai le problème ?

```bash
Test-Path public\hot
```

**Si TRUE :**
```
⚠️ ATTENTION !
- Si Vite tourne : OK
- Si Vite arrêté : PROBLÈME !
```

**Si FALSE :**
```
✅ TOUT VA BIEN
Assets chargés depuis public/build/
```

---

## 🔧 Commandes de Secours

### Fix Ultra-Rapide
```bash
Remove-Item public\hot -Force
```

### Fix Complet
```bash
.\fix-frontend-styles.ps1
```

### Vérification
```bash
.\check-filament-setup.ps1
```

---

## 📝 Mémo Visual

```
État de public/hot           Vite              Résultat
─────────────────────────────────────────────────────────
✅ Absent                    Arrêté            OK - Mode PROD
✅ Présent                   Actif             OK - Mode DEV
❌ Présent                   Arrêté            PROBLÈME !
```

---

## 💡 Astuce PowerShell

Créez un alias pour ne plus oublier :

```powershell
# Dans votre profil PowerShell
function Stop-Dev {
    Write-Host "Arrêt de Vite..." -ForegroundColor Yellow
    Remove-Item public\hot -Force -ErrorAction SilentlyContinue
    Write-Host "✅ public/hot supprimé" -ForegroundColor Green
}

# Usage : Après Ctrl+C, tapez juste :
Stop-Dev
```

---

## 🎓 Pourquoi ce Fichier Existe ?

Le fichier `public/hot` est un **mécanisme de détection** :
- Si présent → Laravel charge depuis Vite Dev Server
- Si absent → Laravel charge depuis `public/build/`

C'est **automatique** mais peut devenir un piège si Vite est arrêté sans supprimer le fichier !

---

## ⚡ TL;DR

```bash
# Après Ctrl+C dans terminal Vite :
Remove-Item public\hot -Force

# Ou :
.\fix-frontend-styles.ps1
```

**C'EST TOUT ! 🎉**

---

*Imprimez et collez sur votre écran si nécessaire ! 😄*
