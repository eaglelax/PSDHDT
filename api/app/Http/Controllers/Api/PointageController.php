<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pointage;
use App\Models\QrCode;
use App\Models\SessionTravail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PointageController extends Controller
{
    /**
     * Liste de tous les pointages (Admin)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pointage::with('user:id,matricule,nom,prenom');

        // Filtrer par utilisateur
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtrer par date
        if ($request->has('date')) {
            $query->whereDate('horodatage', $request->date);
        }

        // Filtrer par période
        if ($request->has('date_debut')) {
            $query->whereDate('horodatage', '>=', $request->date_debut);
        }
        if ($request->has('date_fin')) {
            $query->whereDate('horodatage', '<=', $request->date_fin);
        }

        $pointages = $query->orderBy('horodatage', 'desc')
            ->paginate($request->get('per_page', 50));

        // Transformer les données pour le frontend
        $data = collect($pointages->items())->map(function ($p) {
            return [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'user' => $p->user,
                'type' => $p->type,
                'date' => $p->horodatage->format('Y-m-d'),
                'heure' => $p->horodatage->format('H:i:s'),
                'methode' => $p->methode ?? 'qr_code',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $pointages->total(),
                'per_page' => $pointages->perPage(),
                'current_page' => $pointages->currentPage(),
                'last_page' => $pointages->lastPage(),
            ],
        ]);
    }

    /**
     * Enregistrer un pointage (scan QR code)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = $request->user();

        // Valider le QR code
        $qrCode = QrCode::validerCode($request->qr_code);

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR code invalide ou expiré. Veuillez demander au gardien de générer un nouveau code.',
            ], 400);
        }

        // Déterminer le type de pointage (entrée ou sortie)
        $type = Pointage::typeAttendu($user->id);

        // Enregistrer le pointage
        $pointage = Pointage::enregistrer(
            $user->id,
            $type,
            $qrCode->id,
            $request->latitude,
            $request->longitude
        );

        return response()->json([
            'success' => true,
            'message' => $type === 'entree' ? 'Entrée enregistrée' : 'Sortie enregistrée',
            'data' => [
                'id' => $pointage->id,
                'type' => $pointage->type,
                'horodatage' => $pointage->horodatage->format('Y-m-d H:i:s'),
                'prochain_type' => $type === 'entree' ? 'sortie' : 'entree',
            ],
        ], 201);
    }

    /**
     * Mes pointages (employé connecté)
     */
    public function mesPointages(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Pointage::where('user_id', $user->id);

        // Filtrer par date
        if ($request->has('date')) {
            $query->whereDate('horodatage', $request->date);
        }

        // Filtrer par période
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->whereBetween('horodatage', [$request->date_debut, $request->date_fin]);
        }

        $pointages = $query->orderBy('horodatage', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $pointages,
        ]);
    }

    /**
     * Pointages d'un utilisateur (RH/Directeur)
     */
    public function userPointages(Request $request, User $user): JsonResponse
    {
        $query = Pointage::where('user_id', $user->id);

        if ($request->has('date')) {
            $query->whereDate('horodatage', $request->date);
        }

        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->whereBetween('horodatage', [$request->date_debut, $request->date_fin]);
        }

        $pointages = $query->orderBy('horodatage', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $pointages,
        ]);
    }

    /**
     * Pointages d'une date (tous les employés)
     */
    public function parDate(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $pointages = Pointage::with('user:id,matricule,nom,prenom')
            ->whereDate('horodatage', $request->date)
            ->orderBy('horodatage')
            ->get()
            ->groupBy('user_id');

        return response()->json([
            'success' => true,
            'data' => $pointages,
        ]);
    }

    /**
     * Statut actuel de l'employé (pour savoir si entrée ou sortie)
     */
    public function statut(Request $request): JsonResponse
    {
        $user = $request->user();

        $dernierPointage = Pointage::dernierPointage($user->id);
        $typeAttendu = Pointage::typeAttendu($user->id);

        // Session du jour
        $sessionJour = SessionTravail::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'dernier_pointage' => $dernierPointage ? [
                    'type' => $dernierPointage->type,
                    'horodatage' => $dernierPointage->horodatage->format('Y-m-d H:i:s'),
                ] : null,
                'prochain_type' => $typeAttendu,
                'session_jour' => $sessionJour ? [
                    'heure_entree' => $sessionJour->heure_entree,
                    'heure_sortie' => $sessionJour->heure_sortie,
                    'heures_normales' => $sessionJour->heures_normales,
                    'heures_supplementaires' => $sessionJour->heures_supplementaires,
                    'statut' => $sessionJour->statut,
                ] : null,
            ],
        ]);
    }

    /**
     * Mes sessions de travail
     */
    public function mesSessions(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = SessionTravail::where('user_id', $user->id);

        if ($request->has('mois') && $request->has('annee')) {
            $query->whereMonth('date', $request->mois)
                ->whereYear('date', $request->annee);
        }

        $sessions = $query->orderBy('date', 'desc')
            ->paginate($request->get('per_page', 30));

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Résumé des heures d'un utilisateur
     */
    public function resumeHeures(Request $request, User $user): JsonResponse
    {
        $mois = $request->get('mois', Carbon::now()->month);
        $annee = $request->get('annee', Carbon::now()->year);

        $dateDebut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $dateFin = Carbon::create($annee, $mois, 1)->endOfMonth();

        $heures = SessionTravail::totalHeures($user->id, $dateDebut->toDateString(), $dateFin->toDateString());

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'nom_complet' => $user->nom_complet,
                    'matricule' => $user->matricule,
                ],
                'periode' => [
                    'mois' => $mois,
                    'annee' => $annee,
                ],
                'heures' => $heures,
            ],
        ]);
    }
}
