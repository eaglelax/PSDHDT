# Guide de Lancement Local - Application de Pointage RH

## Pre-requis

- **XAMPP** (MySQL + Apache) - Demarrer MySQL avant de lancer l'API
- **PHP 8.2+** avec Composer
- **Flutter SDK** (ajoute au PATH)
- **Node.js** (optionnel, pour serveur web)

---

## 1. Lancer l'API (Laravel)

```bash
# Ouvrir un terminal dans le dossier du projet
cd c:\Users\JOACHIM\Documents\PSDHDT\api

# Installer les dependances (premiere fois uniquement)
composer install

# Demarrer le serveur Laravel sur le port 8080
php artisan serve --port=8080
```

**URL API** : `http://localhost:8080/api`

### Commandes utiles API

```bash
# Reinitialiser la base de donnees avec les donnees de test
php artisan migrate:fresh --seed

# Voir toutes les routes API
php artisan route:list --path=api

# Vider le cache
php artisan cache:clear
php artisan config:clear
```

---

## 2. Lancer l'Application Mobile (Flutter)

```bash
# Ouvrir un terminal dans le dossier mobile
cd c:\Users\JOACHIM\Documents\PSDHDT\mobile-app

# Installer les dependances (premiere fois uniquement)
"C:/Users/JOACHIM/Documents/flutter/bin/flutter.bat" pub get

# Lancer sur Edge (navigateur)
"C:/Users/JOACHIM/Documents/flutter/bin/flutter.bat" run -d edge

# OU lancer sur Chrome
"C:/Users/JOACHIM/Documents/flutter/bin/flutter.bat" run -d chrome
```

**Raccourcis Flutter en cours d'execution** :
- `r` : Hot reload (rechargement rapide)
- `R` : Hot restart (redemarrage complet)
- `q` : Quitter l'application

---

## 3. Lancer l'Application Web

```bash
# Ouvrir un terminal dans le dossier web
cd c:\Users\JOACHIM\Documents\PSDHDT\web-app

# Option 1 : Serveur Python (simple)
python -m http.server 3000

# Option 2 : Extension Live Server dans VSCode
# Clic droit sur login.html > "Open with Live Server"
```

**URL Web App** : `http://localhost:3000/login.html`

---

## Comptes Utilisateurs de Test

| Role | Email | Mot de passe | Description |
|------|-------|--------------|-------------|
| **Directeur** | directeur@entreprise.com | password123 | Acces complet (stats, employes, bulletins) |
| **RH** | rh@entreprise.com | password123 | Gestion employes et bulletins |
| **Gardien** | gardien@entreprise.com | password123 | Generation QR codes |
| **Employe** | sophie.petit@entreprise.com | password123 | Scanner QR et voir historique |

---

## Scenario de Test Complet

### Test 1 : Connexion Gardien + Generation QR

1. Lancer l'API : `php artisan serve --port=8080`
2. Lancer Flutter : `flutter run -d edge`
3. Se connecter avec `gardien@entreprise.com` / `password123`
4. Le QR code s'affiche avec un timer de 5 minutes

### Test 2 : Pointage Employe

1. Ouvrir un nouvel onglet
2. Se connecter avec `sophie.petit@entreprise.com` / `password123`
3. Cliquer sur "SCANNER"
4. Scanner le QR code affiche par le gardien (ou tester via curl ci-dessous)

### Test 3 : Dashboard RH/Directeur (Web App)

1. Lancer le serveur web : `python -m http.server 3000`
2. Ouvrir `http://localhost:3000/login.html`
3. Se connecter avec `rh@entreprise.com` / `password123`
4. Explorer : Dashboard, Employes, Pointages, Statistiques, Bulletins

---

## Test API avec cURL (Windows CMD)

### Connexion

```bash
# Connexion Gardien
curl -X POST http://localhost:8080/api/auth/login -H "Content-Type: application/json" -d "{\"email\":\"gardien@entreprise.com\",\"password\":\"password123\"}"

# Connexion Employe
curl -X POST http://localhost:8080/api/auth/login -H "Content-Type: application/json" -d "{\"email\":\"sophie.petit@entreprise.com\",\"password\":\"password123\"}"

# Connexion RH
curl -X POST http://localhost:8080/api/auth/login -H "Content-Type: application/json" -d "{\"email\":\"rh@entreprise.com\",\"password\":\"password123\"}"

# Connexion Directeur
curl -X POST http://localhost:8080/api/auth/login -H "Content-Type: application/json" -d "{\"email\":\"directeur@entreprise.com\",\"password\":\"password123\"}"
```

### Generer un QR Code (Gardien)

```bash
curl -X POST http://localhost:8080/api/qrcode/generate -H "Content-Type: application/json" -H "Authorization: Bearer VOTRE_TOKEN_GARDIEN"
```

### Scanner un QR Code (Employe)

```bash
curl -X POST http://localhost:8080/api/pointages -H "Content-Type: application/json" -H "Authorization: Bearer VOTRE_TOKEN_EMPLOYE" -d "{\"qr_code\":\"CODE_QR_GENERE\"}"
```

### Voir mes pointages (Employe)

```bash
curl http://localhost:8080/api/pointages/me -H "Authorization: Bearer VOTRE_TOKEN_EMPLOYE"
```

### Dashboard Stats (RH/Directeur)

```bash
curl http://localhost:8080/api/stats/dashboard -H "Authorization: Bearer VOTRE_TOKEN_RH"
```

---

## Ports Utilises

| Service | Port | URL |
|---------|------|-----|
| API Laravel | 8080 | http://localhost:8080/api |
| Web App | 3000 | http://localhost:3000 |
| Flutter Web | 5XXXX | (port dynamique affiche au lancement) |
| MySQL | 3306 | localhost |

---

## Depannage

### "Connection refused" sur l'API
- Verifier que MySQL est demarre dans XAMPP
- Verifier que le serveur Laravel tourne (`php artisan serve --port=8080`)

### "Port already in use"
```bash
# Trouver le processus qui utilise le port
netstat -ano | findstr :8080

# Tuer le processus (remplacer PID par le numero)
taskkill /PID <PID> /F
```

### Reinitialiser les donnees
```bash
cd api
php artisan migrate:fresh --seed
```

### Flutter ne trouve pas les dependances
```bash
cd mobile-app
flutter clean
flutter pub get
```

---

## Structure du Projet

```
PSDHDT/
├── api/                    # Backend Laravel
│   ├── app/
│   ├── database/
│   └── routes/api.php
├── web-app/               # Frontend Web (HTML/JS)
│   ├── css/
│   ├── js/
│   └── *.html
├── mobile-app/            # App Mobile Flutter
│   ├── lib/
│   └── pubspec.yaml
├── SUIVI_PROJET.md        # Suivi d'avancement
└── LANCEMENT_LOCAL.md     # Ce fichier
```
