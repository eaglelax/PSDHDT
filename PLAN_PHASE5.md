# Plan d'Implementation - Phase 5 : Fonctionnalites Avancees

## Resume de l'Exploration

### Etat Actuel

**API (Laravel):**
- Calcul des salaires DEJA IMPLEMENTE dans `BulletinPaie::generer()`
- Formules existantes:
  - Heures sup = taux_horaire x 1.5
  - Salaire net = salaire_base + montant_heures_sup + primes - deductions
- Endpoints bulletins fonctionnels (`/bulletins`, `/bulletins/generate`, etc.)

**Web App:**
- Pages bulletins.html et statistiques.html DEJA CREEES
- Affichage en tables HTML simples
- Impression basique via `window.print()`
- AUCUNE librairie de charts ou PDF

---

## Taches a Implementer

### 1. Amelioration du Calcul des Salaires (API)

**Fichiers concernes:**
- `api/app/Models/BulletinPaie.php`
- `api/app/Http/Controllers/Api/BulletinPaieController.php`

**Modifications:**
- Ajouter calcul des cotisations sociales (CNSS, etc.)
- Ajouter calcul de l'impot sur le revenu (IRG)
- Ajouter gestion des absences et retards
- Ajouter commentaires/notes sur le bulletin

**Schema de calcul propose:**
```
Salaire Brut = Salaire Base + Heures Sup + Primes
Cotisations = Salaire Brut x Taux CNSS (ex: 3.5%)
IRG = (Salaire Brut - Cotisations) x Taux IRG (ex: 10%)
Salaire Net = Salaire Brut - Cotisations - IRG - Autres Deductions
```

---

### 2. Generation PDF des Bulletins de Paie

**Option A: Cote Backend (Laravel) - RECOMMANDE**
- Utiliser la librairie `barryvdh/laravel-dompdf`
- Creer un template Blade pour le bulletin
- Endpoint: `GET /bulletins/{id}/pdf`

**Option B: Cote Frontend (JavaScript)**
- Utiliser `jsPDF` + `html2canvas`
- Generer le PDF dans le navigateur

**Structure du PDF:**
```
+------------------------------------------+
|           BULLETIN DE PAIE               |
|           [Logo Entreprise]              |
+------------------------------------------+
| Employe: Jean DUPONT                     |
| Matricule: EMP001                        |
| Periode: Janvier 2026                    |
+------------------------------------------+
| DESIGNATION          | MONTANT           |
+------------------------------------------+
| Salaire de base      | 500,000 FCFA      |
| Heures normales (160h)|                   |
| Heures sup (20h)     | +75,000 FCFA      |
| Primes               | +25,000 FCFA      |
+------------------------------------------+
| SALAIRE BRUT         | 600,000 FCFA      |
+------------------------------------------+
| Cotisations CNSS     | -21,000 FCFA      |
| IRG                  | -57,900 FCFA      |
| Autres deductions    | -10,000 FCFA      |
+------------------------------------------+
| NET A PAYER          | 511,100 FCFA      |
+------------------------------------------+
| Date: 31/01/2026     | Signature: ______ |
+------------------------------------------+
```

---

### 3. Graphiques Statistiques (Charts)

**Librairie choisie: Chart.js v4**
- Legere et simple
- Pas de dependances
- Responsive
- CDN: `https://cdn.jsdelivr.net/npm/chart.js`

**Graphiques a ajouter sur statistiques.html:**

1. **Camembert (Pie Chart)** - Repartition des heures
   - Heures normales vs Heures supplementaires

2. **Barres (Bar Chart)** - Presences par employe
   - Top 10 employes par heures travaillees

3. **Ligne (Line Chart)** - Evolution mensuelle
   - Masse salariale sur 12 mois
   - Heures travaillees sur 12 mois

4. **Barres horizontales** - Taux de presence
   - Remplacer les barres CSS actuelles

**Graphiques a ajouter sur dashboard.html:**
- Mini graphique ligne pour tendance du mois

---

### 4. Export Rapports (Excel/PDF)

**Export Excel:**
- Utiliser `SheetJS (xlsx)` cote frontend
- OU `maatwebsite/excel` cote backend Laravel

**Rapports a exporter:**
1. Liste des pointages (periode)
2. Liste des employes
3. Recapitulatif des heures (mensuel)
4. Masse salariale (mensuel)

**Boutons a ajouter:**
- Sur pointages.html: "Exporter Excel" / "Exporter PDF"
- Sur employes.html: "Exporter Excel"
- Sur statistiques.html: "Telecharger Rapport"
- Sur bulletins.html: "Exporter tous les bulletins"

---

## Plan d'Execution

### Etape 1: Generation PDF Bulletins (Backend)
1. Installer `barryvdh/laravel-dompdf`
2. Creer template Blade `resources/views/pdf/bulletin.blade.php`
3. Ajouter methode `downloadPdf()` dans BulletinPaieController
4. Ajouter route `GET /bulletins/{id}/pdf`
5. Ajouter bouton "Telecharger PDF" dans bulletins.html

### Etape 2: Graphiques Statistiques (Frontend)
1. Ajouter Chart.js via CDN dans statistiques.html
2. Creer conteneurs canvas pour les graphiques
3. Modifier loadStats() pour alimenter les charts
4. Ajouter graphique mini sur dashboard.html

### Etape 3: Export Excel (Frontend)
1. Ajouter SheetJS via CDN
2. Creer fonction `exportToExcel(data, filename)`
3. Ajouter boutons d'export sur chaque page

### Etape 4: Amelioration Calcul Salaires (Backend)
1. Ajouter champs cotisations/irg dans migration bulletins
2. Modifier `BulletinPaie::generer()` avec nouvelles formules
3. Mettre a jour l'affichage dans bulletins.html

---

## Fichiers a Creer/Modifier

### Nouveaux fichiers:
- `api/resources/views/pdf/bulletin.blade.php`

### Fichiers a modifier:
- `api/app/Http/Controllers/Api/BulletinPaieController.php`
- `api/app/Models/BulletinPaie.php`
- `api/routes/api.php`
- `web-app/statistiques.html`
- `web-app/bulletins.html`
- `web-app/dashboard.html`
- `web-app/pointages.html`
- `web-app/employes.html`
- `web-app/js/api.js`

---

## Estimation

| Tache | Complexite |
|-------|------------|
| PDF Bulletins | Moyenne |
| Charts Stats | Moyenne |
| Export Excel | Facile |
| Calcul Salaires | Facile |

---

## Decisions de l'utilisateur

1. **Cotisations sociales**: ✅ Cotisations detaillees (CNSS 3.5%, IRG 10%)
2. **Generation PDF**: ✅ Frontend JavaScript (jsPDF + html2canvas)
3. **Devise**: FCFA
