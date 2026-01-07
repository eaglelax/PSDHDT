<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulletinPaie;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BulletinPaieController extends Controller
{
    /**
     * Liste des bulletins (RH/Directeur)
     */
    public function index(Request $request): JsonResponse
    {
        $query = BulletinPaie::with('user:id,matricule,nom,prenom');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('mois') && $request->has('annee')) {
            $query->where('mois', $request->mois)
                ->where('annee', $request->annee);
        }

        if ($request->has('annee')) {
            $query->where('annee', $request->annee);
        }

        $bulletins = $query->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $bulletins,
        ]);
    }

    /**
     * Générer un bulletin de paie
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020',
            'primes' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
        ]);

        $bulletin = BulletinPaie::generer(
            $request->user_id,
            $request->mois,
            $request->annee,
            $request->get('primes', 0),
            $request->get('deductions', 0)
        );

        $bulletin->load('user:id,matricule,nom,prenom');

        return response()->json([
            'success' => true,
            'message' => 'Bulletin de paie généré avec succès',
            'data' => $bulletin,
        ], 201);
    }

    /**
     * Générer les bulletins pour tous les employés d'un mois
     */
    public function generateAll(Request $request): JsonResponse
    {
        $request->validate([
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020',
        ]);

        $employes = User::where('role', 'employe')
            ->where('actif', true)
            ->get();

        $bulletins = [];

        foreach ($employes as $employe) {
            $bulletins[] = BulletinPaie::generer(
                $employe->id,
                $request->mois,
                $request->annee
            );
        }

        return response()->json([
            'success' => true,
            'message' => count($bulletins) . ' bulletins générés',
            'data' => [
                'count' => count($bulletins),
                'mois' => $request->mois,
                'annee' => $request->annee,
            ],
        ], 201);
    }

    /**
     * Afficher un bulletin
     */
    public function show(BulletinPaie $bulletin): JsonResponse
    {
        $bulletin->load('user:id,matricule,nom,prenom,email,telephone');

        return response()->json([
            'success' => true,
            'data' => $bulletin,
        ]);
    }

    /**
     * Mes bulletins (employé connecté)
     */
    public function mesBulletins(Request $request): JsonResponse
    {
        $user = $request->user();

        $bulletins = BulletinPaie::where('user_id', $user->id)
            ->orderBy('annee', 'desc')
            ->orderBy('mois', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $bulletins,
        ]);
    }

    /**
     * Modifier un bulletin (primes/déductions)
     */
    public function update(Request $request, BulletinPaie $bulletin): JsonResponse
    {
        $request->validate([
            'primes' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
        ]);

        $primes = $request->get('primes', $bulletin->primes);
        $deductions = $request->get('deductions', $bulletin->deductions);

        // Recalculer le salaire net
        $salaireNet = $bulletin->salaire_base + $bulletin->montant_heures_sup + $primes - $deductions;

        $bulletin->update([
            'primes' => $primes,
            'deductions' => $deductions,
            'salaire_net' => $salaireNet,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bulletin mis à jour',
            'data' => $bulletin->fresh(),
        ]);
    }
}
