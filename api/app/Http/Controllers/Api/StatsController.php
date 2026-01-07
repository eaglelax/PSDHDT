<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pointage;
use App\Models\SessionTravail;
use App\Models\User;
use App\Models\BulletinPaie;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * Dashboard principal (stats globales)
     */
    public function dashboard(Request $request): JsonResponse
    {
        $today = Carbon::today();

        // Nombre d'employés actifs
        $totalEmployes = User::where('role', 'employe')->where('actif', true)->count();

        // Présents aujourd'hui
        $presentsAujourdhui = SessionTravail::where('date', $today)
            ->whereNotNull('heure_entree')
            ->count();

        // Absents aujourd'hui (employés sans pointage)
        $absentsAujourdhui = $totalEmployes - $presentsAujourdhui;

        // Encore au travail (entrée mais pas de sortie)
        $encoreAuTravail = SessionTravail::where('date', $today)
            ->whereNotNull('heure_entree')
            ->whereNull('heure_sortie')
            ->count();

        // Stats du mois en cours
        $debutMois = $today->copy()->startOfMonth();
        $finMois = $today->copy()->endOfMonth();

        $sessionsduMois = SessionTravail::whereBetween('date', [$debutMois, $finMois])->get();

        $totalHeuresNormales = $sessionsduMois->sum('heures_normales');
        $totalHeuresSup = $sessionsduMois->sum('heures_supplementaires');

        // Derniers pointages
        $derniersPointages = Pointage::with('user:id,matricule,nom,prenom')
            ->whereDate('horodatage', $today)
            ->orderBy('horodatage', 'desc')
            ->take(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'user' => $p->user,
                    'type' => $p->type,
                    'heure' => $p->horodatage->format('H:i:s'),
                ];
            });

        // Présents du jour avec détails
        $presents = SessionTravail::with('user:id,matricule,nom,prenom')
            ->where('date', $today)
            ->whereNotNull('heure_entree')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->user->id,
                    'nom' => $s->user->nom,
                    'prenom' => $s->user->prenom,
                    'heure_arrivee' => $s->heure_entree,
                    'en_cours' => is_null($s->heure_sortie),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $today->format('Y-m-d'),
                'total_employes' => $totalEmployes,
                'presents_aujourd_hui' => $presentsAujourdhui,
                'absents_aujourd_hui' => $absentsAujourdhui,
                'encore_au_travail' => $encoreAuTravail,
                'heures_mois' => round($totalHeuresNormales + $totalHeuresSup, 2),
                'heures_normales' => round($totalHeuresNormales, 2),
                'heures_supplementaires' => round($totalHeuresSup, 2),
                'jours_travail' => $sessionsduMois->groupBy('date')->count(),
                'taux_presence' => $totalEmployes > 0 ? round(($presentsAujourdhui / $totalEmployes) * 100, 1) : 0,
                'presents' => $presents,
                'derniers_pointages' => $derniersPointages,
            ],
        ]);
    }

    /**
     * Statistiques de présence
     */
    public function presences(Request $request): JsonResponse
    {
        $mois = $request->get('mois', Carbon::now()->month);
        $annee = $request->get('annee', Carbon::now()->year);

        $debutMois = Carbon::create($annee, $mois, 1)->startOfMonth();
        $finMois = Carbon::create($annee, $mois, 1)->endOfMonth();

        // Jours ouvrés du mois (lundi à vendredi)
        $joursOuvres = 0;
        $date = $debutMois->copy();
        while ($date->lte($finMois)) {
            if (!$date->isWeekend()) {
                $joursOuvres++;
            }
            $date->addDay();
        }

        // Sessions par employé
        $employes = User::where('role', 'employe')
            ->where('actif', true)
            ->with(['sessionsTravail' => function ($query) use ($debutMois, $finMois) {
                $query->whereBetween('date', [$debutMois, $finMois]);
            }])
            ->get()
            ->map(function ($employe) use ($joursOuvres) {
                $sessions = $employe->sessionsTravail;
                $joursPresent = $sessions->where('statut', 'complet')->count();
                $joursIncomplet = $sessions->where('statut', 'incomplet')->count();

                return [
                    'id' => $employe->id,
                    'matricule' => $employe->matricule,
                    'nom_complet' => $employe->nom_complet,
                    'jours_present' => $joursPresent,
                    'jours_incomplet' => $joursIncomplet,
                    'jours_absent' => $joursOuvres - $joursPresent - $joursIncomplet,
                    'taux_presence' => $joursOuvres > 0 ? round(($joursPresent / $joursOuvres) * 100, 1) : 0,
                    'heures_normales' => $sessions->sum('heures_normales'),
                    'heures_sup' => $sessions->sum('heures_supplementaires'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => [
                    'mois' => $mois,
                    'annee' => $annee,
                    'jours_ouvres' => $joursOuvres,
                ],
                'employes' => $employes,
            ],
        ]);
    }

    /**
     * Statistiques des heures
     */
    public function heures(Request $request): JsonResponse
    {
        $mois = $request->get('mois', Carbon::now()->month);
        $annee = $request->get('annee', Carbon::now()->year);

        $debutMois = Carbon::create($annee, $mois, 1)->startOfMonth();
        $finMois = Carbon::create($annee, $mois, 1)->endOfMonth();

        $sessions = SessionTravail::whereBetween('date', [$debutMois, $finMois])
            ->get()
            ->groupBy('date');

        $parJour = $sessions->map(function ($sessionsJour, $date) {
            return [
                'date' => $date,
                'heures_normales' => $sessionsJour->sum('heures_normales'),
                'heures_sup' => $sessionsJour->sum('heures_supplementaires'),
                'employes_presents' => $sessionsJour->where('statut', 'complet')->count(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => [
                    'mois' => $mois,
                    'annee' => $annee,
                ],
                'par_jour' => $parJour,
                'totaux' => [
                    'heures_normales' => $sessions->flatten()->sum('heures_normales'),
                    'heures_sup' => $sessions->flatten()->sum('heures_supplementaires'),
                ],
            ],
        ]);
    }

    /**
     * Statistiques des salaires
     */
    public function salaires(Request $request): JsonResponse
    {
        $mois = $request->get('mois', Carbon::now()->month);
        $annee = $request->get('annee', Carbon::now()->year);

        $bulletins = BulletinPaie::with('user:id,matricule,nom,prenom')
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => [
                    'mois' => $mois,
                    'annee' => $annee,
                ],
                'bulletins' => $bulletins,
                'totaux' => [
                    'salaires_base' => $bulletins->sum('salaire_base'),
                    'heures_sup' => $bulletins->sum('montant_heures_sup'),
                    'primes' => $bulletins->sum('primes'),
                    'deductions' => $bulletins->sum('deductions'),
                    'salaires_nets' => $bulletins->sum('salaire_net'),
                ],
            ],
        ]);
    }
}
