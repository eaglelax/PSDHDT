# Plan de Développement - Application de Pointage RH

## Vue d'ensemble du projet

Application de suivi des heures de travail avec :
- **API Backend** (Laravel/PHP + MySQL)
- **Application Web** (HTML/CSS/JavaScript) - pour RH et Directeur
- **Application Mobile** (Flutter) - pour Employés et Gardien

---

## MODULE 1 : Base de données et API Backend

### 1.1 Configuration initiale
- [x] Installer Laravel dans le dossier `api/` ✅
- [x] Configurer la connexion MySQL (.env) ✅
- [x] Créer la base de données `pointage_db` ✅

### 1.2 Tables de la base de données

#### Table `users` (Utilisateurs)
| Champ | Type | Description |
|-------|------|-------------|
| id | INT | Clé primaire |
| matricule | VARCHAR(20) | Matricule unique employé |
| nom | VARCHAR(100) | Nom |
| prenom | VARCHAR(100) | Prénom |
| email | VARCHAR(150) | Email unique |
| password | VARCHAR(255) | Mot de passe hashé |
| telephone | VARCHAR(20) | Numéro de téléphone |
| role | ENUM | 'employe', 'gardien', 'rh', 'directeur' |
| salaire_base | DECIMAL(10,2) | Salaire de base mensuel |
| taux_horaire | DECIMAL(8,2) | Taux horaire |
| actif | BOOLEAN | Compte actif ou non |
| created_at | TIMESTAMP | Date création |
| updated_at | TIMESTAMP | Date modification |

#### Table `pointages` (Enregistrements de pointage)
| Champ | Type | Description |
|-------|------|-------------|
| id | INT | Clé primaire |
| user_id | INT | FK vers users |
| type | ENUM | 'entree', 'sortie' |
| horodatage | DATETIME | Date et heure du pointage |
| qr_code_id | INT | FK vers qr_codes |
| latitude | DECIMAL(10,8) | Position GPS (optionnel) |
| longitude | DECIMAL(11,8) | Position GPS (optionnel) |
| created_at | TIMESTAMP | Date création |

#### Table `qr_codes` (Codes QR générés)
| Champ | Type | Description |
|-------|------|-------------|
| id | INT | Clé primaire |
| code | VARCHAR(100) | Code unique généré |
| gardien_id | INT | FK vers users (gardien) |
| date_generation | DATETIME | Date de génération |
| date_expiration | DATETIME | Date d'expiration |
| actif | BOOLEAN | Code encore valide |

#### Table `sessions_travail` (Sessions de travail calculées)
| Champ | Type | Description |
|-------|------|-------------|
| id | INT | Clé primaire |
| user_id | INT | FK vers users |
| date | DATE | Date de travail |
| heure_entree | TIME | Heure d'arrivée |
| heure_sortie | TIME | Heure de départ |
| heures_normales | DECIMAL(4,2) | Heures normales |
| heures_supplementaires | DECIMAL(4,2) | Heures sup |
| statut | ENUM | 'complet', 'incomplet', 'absent' |

#### Table `bulletins_paie` (Bulletins de salaire)
| Champ | Type | Description |
|-------|------|-------------|
| id | INT | Clé primaire |
| user_id | INT | FK vers users |
| mois | INT | Mois (1-12) |
| annee | INT | Année |
| total_heures_normales | DECIMAL(6,2) | Total heures normales |
| total_heures_sup | DECIMAL(6,2) | Total heures sup |
| salaire_base | DECIMAL(10,2) | Salaire de base |
| montant_heures_sup | DECIMAL(10,2) | Montant heures sup |
| primes | DECIMAL(10,2) | Primes |
| deductions | DECIMAL(10,2) | Déductions |
| salaire_net | DECIMAL(10,2) | Salaire net |
| fichier_pdf | VARCHAR(255) | Chemin du PDF |
| created_at | TIMESTAMP | Date génération |

### 1.3 Endpoints API REST

#### Authentification
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/auth/login` | Connexion utilisateur |
| POST | `/api/auth/logout` | Déconnexion |
| GET | `/api/auth/me` | Profil utilisateur connecté |
| POST | `/api/auth/refresh` | Rafraîchir le token |

#### Gestion des utilisateurs (RH)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/users` | Liste des utilisateurs |
| POST | `/api/users` | Créer un utilisateur |
| GET | `/api/users/{id}` | Détails utilisateur |
| PUT | `/api/users/{id}` | Modifier utilisateur |
| DELETE | `/api/users/{id}` | Supprimer utilisateur |

#### QR Codes (Gardien)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/qrcode/generate` | Générer un nouveau QR code |
| GET | `/api/qrcode/current` | QR code actuel valide |
| POST | `/api/qrcode/validate` | Valider un QR code |

#### Pointages
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/pointages` | Enregistrer un pointage |
| GET | `/api/pointages/me` | Mes pointages |
| GET | `/api/pointages/user/{id}` | Pointages d'un utilisateur |
| GET | `/api/pointages/date/{date}` | Pointages d'une date |

#### Sessions et Heures
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/sessions/me` | Mes sessions de travail |
| GET | `/api/sessions/user/{id}` | Sessions d'un utilisateur |
| GET | `/api/sessions/periode` | Sessions sur une période |
| GET | `/api/heures/resume/{user_id}` | Résumé heures mensuel |

#### Bulletins de paie (RH)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/bulletins/generate` | Générer un bulletin |
| GET | `/api/bulletins` | Liste des bulletins |
| GET | `/api/bulletins/{id}` | Détails bulletin |
| GET | `/api/bulletins/{id}/pdf` | Télécharger PDF |
| GET | `/api/bulletins/me` | Mes bulletins |

#### Statistiques (Directeur)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/stats/presences` | Stats de présence |
| GET | `/api/stats/heures` | Stats des heures |
| GET | `/api/stats/absences` | Stats des absences |
| GET | `/api/stats/dashboard` | Données tableau de bord |

---

## MODULE 2 : Application Web (RH & Directeur)

### 2.1 Pages à développer

#### Pages d'authentification
- [ ] Page de connexion (`login.html`)
- [ ] Récupération mot de passe (`forgot-password.html`)

#### Dashboard principal
- [ ] Tableau de bord RH (`dashboard-rh.html`)
- [ ] Tableau de bord Directeur (`dashboard-directeur.html`)

#### Gestion des employés (RH)
- [ ] Liste des employés (`employees/list.html`)
- [ ] Formulaire ajout/modification (`employees/form.html`)
- [ ] Fiche employé détaillée (`employees/detail.html`)

#### Suivi des pointages (RH)
- [ ] Vue calendrier des présences (`pointages/calendar.html`)
- [ ] Liste des pointages (`pointages/list.html`)
- [ ] Rapport journalier (`pointages/daily.html`)

#### Gestion des salaires (RH)
- [ ] Calcul des heures (`salaires/heures.html`)
- [ ] Génération bulletins (`salaires/bulletins.html`)
- [ ] Historique des bulletins (`salaires/historique.html`)

#### Statistiques (Directeur)
- [ ] Vue globale (`stats/overview.html`)
- [ ] Graphiques de présence (`stats/presence.html`)
- [ ] Rapports exportables (`stats/reports.html`)

### 2.2 Composants réutilisables
- [ ] Header avec navigation
- [ ] Sidebar menu
- [ ] Tableaux avec pagination
- [ ] Modales de confirmation
- [ ] Notifications/Alertes
- [ ] Formulaires avec validation

---

## MODULE 3 : Application Mobile (Flutter)

### 3.1 Écrans Employé

#### Authentification
- [ ] Écran de connexion (`login_screen.dart`)
- [ ] Écran mot de passe oublié (`forgot_password_screen.dart`)

#### Pointage
- [ ] Scanner QR code (`scan_screen.dart`)
- [ ] Confirmation pointage (`confirmation_screen.dart`)

#### Consultation
- [ ] Historique personnel (`history_screen.dart`)
- [ ] Mes heures du mois (`hours_screen.dart`)
- [ ] Mes bulletins de paie (`payslips_screen.dart`)

#### Profil
- [ ] Mon profil (`profile_screen.dart`)
- [ ] Modifier mot de passe (`change_password_screen.dart`)

### 3.2 Écrans Gardien

- [ ] Génération QR code (`generate_qr_screen.dart`)
- [ ] Affichage QR code plein écran (`display_qr_screen.dart`)
- [ ] Historique des scans du jour (`scans_today_screen.dart`)

### 3.3 Services Flutter
- [ ] Service d'authentification (`auth_service.dart`)
- [ ] Service API (`api_service.dart`)
- [ ] Service de stockage local (`storage_service.dart`)
- [ ] Service de scan QR (`qr_service.dart`)

### 3.4 Modèles de données
- [ ] User (`user_model.dart`)
- [ ] Pointage (`pointage_model.dart`)
- [ ] Session (`session_model.dart`)
- [ ] Bulletin (`bulletin_model.dart`)

---

## MODULE 4 : Fonctionnalités transversales

### 4.1 Sécurité
- [ ] Authentification JWT
- [ ] Hashage des mots de passe (bcrypt)
- [ ] Middleware de vérification des rôles
- [ ] Protection CORS
- [ ] Validation des données entrantes
- [ ] QR codes avec expiration (5 minutes)

### 4.2 Calculs métier
- [ ] Calcul heures normales (8h/jour max)
- [ ] Calcul heures supplémentaires (>8h)
- [ ] Calcul salaire : base + (heures_sup × taux × 1.5)
- [ ] Gestion des absences
- [ ] Gestion des jours fériés (optionnel)

### 4.3 Génération PDF
- [ ] Template bulletin de paie
- [ ] Export des rapports
- [ ] Logo entreprise personnalisable

---

## Ordre de développement recommandé

### Phase 1 : Fondations ✅ TERMINÉE
1. ✅ Structure des dossiers
2. ✅ Installation Laravel + configuration MySQL
3. ✅ Création des migrations (tables)
4. ✅ Modèles Eloquent
5. ✅ Seeders (données de test)

### Phase 2 : API Core
6. [ ] Authentification (login/logout/JWT)
7. [ ] CRUD Utilisateurs
8. [ ] Génération QR codes
9. [ ] Enregistrement pointages
10. [ ] Calcul sessions de travail

### Phase 3 : Web App - Base
11. [ ] Page de connexion
12. [ ] Dashboard RH
13. [ ] Liste des employés
14. [ ] Vue des pointages

### Phase 4 : Mobile App - Base
15. [ ] Écran connexion
16. [ ] Scanner QR code
17. [ ] Écran gardien (affichage QR)
18. [ ] Historique employé

### Phase 5 : Fonctionnalités avancées
19. [ ] Calcul des salaires
20. [ ] Génération bulletins PDF
21. [ ] Statistiques et graphiques
22. [ ] Dashboard Directeur

### Phase 6 : Finalisation
23. [ ] Tests complets
24. [ ] Corrections bugs
25. [ ] Déploiement

---

## Configuration requise

### Environnement de développement
- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js (pour les assets si besoin)
- Flutter SDK 3.0+
- Android Studio ou VS Code

### Ports par défaut
- API Laravel : `http://localhost:8000`
- MySQL : `localhost:3306`
- Web App : `http://localhost:5500` (Live Server)

---

## Notes importantes

1. **QR Code dynamique** : Le code change toutes les 5 minutes pour éviter la fraude
2. **Géolocalisation** : Optionnel, pour vérifier que l'employé est sur site
3. **Hors ligne** : L'app mobile doit pouvoir fonctionner en mode dégradé
4. **Multi-entreprise** : À prévoir pour une évolution SaaS future

---

**Validez ce plan avant de commencer le développement.**

Souhaitez-vous des modifications ou des ajouts ?
