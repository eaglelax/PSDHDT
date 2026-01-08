# Plan d'Implémentation - Espace Admin Entreprise

> **Date de création :** 08/01/2026
> **Objectif :** Permettre la personnalisation de la plateforme (logo, nom, couleurs)

---

## 1. Vue d'Ensemble

### Fonctionnalités à implémenter
- Configuration du nom de l'entreprise
- Upload et affichage du logo
- Personnalisation des couleurs (primaire, secondaire, accent)
- Application dynamique du thème sur toute la plateforme
- Persistence des paramètres en base de données

### Architecture
```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (Web App)                        │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │ admin.html  │  │ style.css   │  │ Toutes les pages    │  │
│  │ (Config UI) │  │ (Variables) │  │ (Thème appliqué)    │  │
│  └──────┬──────┘  └──────┬──────┘  └──────────┬──────────┘  │
│         │                │                     │             │
└─────────┼────────────────┼─────────────────────┼─────────────┘
          │                │                     │
          ▼                ▼                     ▼
┌─────────────────────────────────────────────────────────────┐
│                      API (Laravel)                           │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────┐  │
│  │ EntrepriseCtrl  │  │ Entreprise.php  │  │ Storage     │  │
│  │ (CRUD + Upload) │  │ (Modèle)        │  │ (Logos)     │  │
│  └────────┬────────┘  └────────┬────────┘  └──────┬──────┘  │
│           │                    │                   │         │
└───────────┼────────────────────┼───────────────────┼─────────┘
            │                    │                   │
            ▼                    ▼                   ▼
┌─────────────────────────────────────────────────────────────┐
│                    BASE DE DONNÉES                           │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Table: entreprises                                   │    │
│  │ - id, nom, logo, couleur_primaire, couleur_secondaire│   │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Base de Données

### 2.1 Migration `entreprises`

**Fichier:** `api/database/migrations/xxxx_create_entreprises_table.php`

```php
Schema::create('entreprises', function (Blueprint $table) {
    $table->id();
    $table->string('nom')->default('Mon Entreprise');
    $table->string('logo')->nullable();           // Chemin vers le fichier logo
    $table->string('couleur_primaire', 7)->default('#1a73e8');    // Hex color
    $table->string('couleur_secondaire', 7)->default('#4285f4');  // Hex color
    $table->string('couleur_accent', 7)->default('#34a853');      // Hex color
    $table->string('couleur_texte', 7)->default('#333333');       // Hex color
    $table->string('email_contact')->nullable();
    $table->string('telephone')->nullable();
    $table->text('adresse')->nullable();
    $table->boolean('actif')->default(true);
    $table->timestamps();
});
```

### 2.2 Seeder

**Fichier:** `api/database/seeders/EntrepriseSeeder.php`

```php
Entreprise::create([
    'nom' => 'Entreprise Demo',
    'couleur_primaire' => '#1a73e8',
    'couleur_secondaire' => '#4285f4',
    'couleur_accent' => '#34a853',
    'couleur_texte' => '#333333',
]);
```

---

## 3. Backend API (Laravel)

### 3.1 Modèle Entreprise

**Fichier:** `api/app/Models/Entreprise.php`

```php
class Entreprise extends Model
{
    protected $fillable = [
        'nom', 'logo', 'couleur_primaire', 'couleur_secondaire',
        'couleur_accent', 'couleur_texte', 'email_contact',
        'telephone', 'adresse', 'actif'
    ];

    // Accessor pour l'URL complète du logo
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    // Récupérer l'entreprise active (singleton pattern)
    public static function getActive(): ?self
    {
        return self::where('actif', true)->first();
    }
}
```

### 3.2 Controller EntrepriseController

**Fichier:** `api/app/Http/Controllers/Api/EntrepriseController.php`

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/api/entreprise` | Récupérer config entreprise | Public |
| PUT | `/api/entreprise` | Modifier config | Admin |
| POST | `/api/entreprise/logo` | Upload logo | Admin |
| DELETE | `/api/entreprise/logo` | Supprimer logo | Admin |

**Fonctions principales:**

```php
// Récupérer la configuration (public - pour appliquer le thème)
public function show(): JsonResponse

// Mettre à jour la configuration (admin uniquement)
public function update(Request $request): JsonResponse

// Upload du logo (admin uniquement)
public function uploadLogo(Request $request): JsonResponse

// Supprimer le logo
public function deleteLogo(): JsonResponse
```

### 3.3 Routes API

**Fichier:** `api/routes/api.php`

```php
// Routes publiques (pour charger le thème)
Route::get('/entreprise', [EntrepriseController::class, 'show']);

// Routes admin (protégées)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::put('/entreprise', [EntrepriseController::class, 'update']);
    Route::post('/entreprise/logo', [EntrepriseController::class, 'uploadLogo']);
    Route::delete('/entreprise/logo', [EntrepriseController::class, 'deleteLogo']);
});
```

### 3.4 Configuration Storage

```bash
php artisan storage:link
```

Crée un lien symbolique `public/storage` → `storage/app/public`

---

## 4. Frontend Web App

### 4.1 Variables CSS Dynamiques

**Fichier:** `web-app/css/style.css` (à modifier)

```css
:root {
    /* Couleurs par défaut (seront écrasées par JS) */
    --color-primary: #1a73e8;
    --color-secondary: #4285f4;
    --color-accent: #34a853;
    --color-text: #333333;
    --color-bg: #f5f7fa;
    --color-white: #ffffff;
    --color-border: #e0e0e0;
    --color-error: #ea4335;
    --color-success: #34a853;
    --color-warning: #fbbc04;
}

/* Utilisation des variables dans les styles existants */
.sidebar { background: var(--color-primary); }
.btn-primary { background: var(--color-primary); }
.btn-secondary { background: var(--color-secondary); }
/* etc. */
```

### 4.2 Service de Thème

**Fichier:** `web-app/js/theme.js` (nouveau)

```javascript
const ThemeService = {
    // Charger et appliquer le thème
    async loadTheme() {
        try {
            const response = await fetch(API_URL + '/entreprise');
            const data = await response.json();
            if (data.success) {
                this.applyTheme(data.data);
                this.updateBranding(data.data);
            }
        } catch (error) {
            console.log('Utilisation du thème par défaut');
        }
    },

    // Appliquer les couleurs CSS
    applyTheme(config) {
        const root = document.documentElement;
        root.style.setProperty('--color-primary', config.couleur_primaire);
        root.style.setProperty('--color-secondary', config.couleur_secondaire);
        root.style.setProperty('--color-accent', config.couleur_accent);
        root.style.setProperty('--color-text', config.couleur_texte);
    },

    // Mettre à jour logo et nom
    updateBranding(config) {
        const logoElements = document.querySelectorAll('.company-logo');
        const nameElements = document.querySelectorAll('.company-name');

        logoElements.forEach(el => {
            if (config.logo_url) {
                el.src = config.logo_url;
                el.style.display = 'block';
            }
        });

        nameElements.forEach(el => {
            el.textContent = config.nom;
        });
    }
};

// Charger le thème au démarrage
document.addEventListener('DOMContentLoaded', () => ThemeService.loadTheme());
```

### 4.3 Page Admin Entreprise

**Fichier:** `web-app/admin-entreprise.html` (nouveau)

**Structure de la page:**

```
┌─────────────────────────────────────────────────────────────┐
│  SIDEBAR  │            CONTENU PRINCIPAL                    │
│           │  ┌────────────────────────────────────────────┐ │
│  Dashboard│  │  Administration Entreprise                 │ │
│  Employés │  ├────────────────────────────────────────────┤ │
│  Pointages│  │                                            │ │
│  Stats    │  │  ┌──────────────┐  ┌──────────────────────┐│ │
│  Bulletins│  │  │   PREVIEW    │  │  FORMULAIRE          ││ │
│  ────────│  │  │              │  │                      ││ │
│  ⚙️ Admin │  │  │  [Logo]      │  │  Nom entreprise      ││ │
│           │  │  │  Nom Entrep. │  │  [____________]      ││ │
│           │  │  │              │  │                      ││ │
│           │  │  │  Couleurs    │  │  Logo                ││ │
│           │  │  │  ████ ████   │  │  [Choisir fichier]   ││ │
│           │  │  │              │  │                      ││ │
│           │  │  └──────────────┘  │  Couleur primaire    ││ │
│           │  │                    │  [#1a73e8] 🎨        ││ │
│           │  │                    │                      ││ │
│           │  │                    │  Couleur secondaire  ││ │
│           │  │                    │  [#4285f4] 🎨        ││ │
│           │  │                    │                      ││ │
│           │  │                    │  [Enregistrer]       ││ │
│           │  │                    └──────────────────────┘│ │
│           │  └────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

**Fonctionnalités UI:**
- Aperçu en temps réel des changements
- Color picker natif HTML5
- Drag & drop pour le logo
- Validation des couleurs hex
- Bouton reset aux couleurs par défaut

---

## 5. Étapes d'Implémentation

### Étape 1: Migration et Modèle (Backend)
1. ✅ Créer la migration `create_entreprises_table`
2. ✅ Créer le modèle `Entreprise`
3. ✅ Créer le seeder `EntrepriseSeeder`
4. ✅ Exécuter la migration

### Étape 2: Controller et Routes (Backend)
5. ✅ Créer `EntrepriseController`
6. ✅ Ajouter les routes dans `api.php`
7. ✅ Configurer le storage link

### Étape 3: Variables CSS (Frontend)
8. ✅ Modifier `style.css` avec variables CSS
9. ✅ Remplacer les couleurs hardcodées par des variables

### Étape 4: Service Thème (Frontend)
10. ✅ Créer `theme.js`
11. ✅ Intégrer dans toutes les pages HTML

### Étape 5: Page Admin (Frontend)
12. ✅ Créer `admin-entreprise.html`
13. ✅ Ajouter le lien dans la sidebar
14. ✅ Implémenter l'upload de logo
15. ✅ Implémenter les color pickers
16. ✅ Ajouter l'aperçu en temps réel

### Étape 6: Tests
17. ✅ Tester l'API (GET/PUT entreprise)
18. ✅ Tester l'upload de logo
19. ✅ Vérifier l'application du thème sur toutes les pages
20. ✅ Tester avec différentes couleurs

---

## 6. Design UI/UX

### Principes de design
- **Minimaliste** : Interface épurée, focus sur l'essentiel
- **Professionnel** : Couleurs sobres, typographie claire
- **Intuitif** : Actions évidentes, feedback immédiat
- **Responsive** : Adapté desktop et tablette

### Palette de couleurs par défaut
| Élément | Couleur | Hex |
|---------|---------|-----|
| Primaire | Bleu Google | #1a73e8 |
| Secondaire | Bleu clair | #4285f4 |
| Accent | Vert | #34a853 |
| Texte | Gris foncé | #333333 |
| Background | Gris clair | #f5f7fa |
| Erreur | Rouge | #ea4335 |
| Succès | Vert | #34a853 |

### Composants UI
- **Cards** avec ombres légères
- **Inputs** avec bordures arrondies
- **Boutons** avec états hover/active
- **Color pickers** natifs HTML5
- **Zone de preview** avec mise à jour live

---

## 7. Sécurité

### Validation côté serveur
- Validation format hex pour les couleurs (#RRGGBB)
- Validation type de fichier pour logo (jpg, png, svg)
- Limite de taille pour le logo (2 Mo max)
- Sanitization du nom d'entreprise

### Permissions
- Lecture config : Public (pour appliquer le thème)
- Modification : Directeur uniquement
- Upload logo : Directeur uniquement

---

## 8. Fichiers à Créer/Modifier

### Nouveaux fichiers
| Fichier | Type | Description |
|---------|------|-------------|
| `api/database/migrations/xxxx_create_entreprises_table.php` | Migration | Table entreprises |
| `api/app/Models/Entreprise.php` | Modèle | Modèle Eloquent |
| `api/database/seeders/EntrepriseSeeder.php` | Seeder | Données initiales |
| `api/app/Http/Controllers/Api/EntrepriseController.php` | Controller | API CRUD |
| `web-app/js/theme.js` | JavaScript | Service de thème |
| `web-app/admin-entreprise.html` | HTML | Page d'administration |

### Fichiers à modifier
| Fichier | Modification |
|---------|--------------|
| `api/routes/api.php` | Ajouter routes entreprise |
| `api/database/seeders/DatabaseSeeder.php` | Appeler EntrepriseSeeder |
| `web-app/css/style.css` | Variables CSS dynamiques |
| `web-app/*.html` (toutes les pages) | Inclure theme.js + éléments branding |

---

## 9. Progression

| Étape | Statut | Notes |
|-------|--------|-------|
| 1. Migration | ✅ | Créée et exécutée |
| 2. Modèle | ✅ | Entreprise.php |
| 3. Seeder | ✅ | EntrepriseSeeder.php |
| 4. Controller | ✅ | EntrepriseController.php |
| 5. Routes | ✅ | 5 endpoints ajoutés |
| 6. Variables CSS | ✅ | --theme-primary, etc. |
| 7. theme.js | ✅ | Service de thème |
| 8. admin-entreprise.html | ✅ | Page complète avec aperçu |
| 9. Intégration pages | ✅ | Toutes les pages mises à jour |
| 10. Tests | ✅ | API fonctionnelle |

---

> **Implémentation terminée le 08/01/2026**
>
> Pour tester :
> 1. Se connecter avec un compte Directeur ou RH
> 2. Cliquer sur "Administration" dans la sidebar
> 3. Modifier le nom, les couleurs ou uploader un logo
> 4. Les changements s'appliquent en temps réel sur toute la plateforme
