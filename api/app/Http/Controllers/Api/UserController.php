<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs (avec filtres)
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filtrer par rôle
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filtrer par statut actif
        if ($request->has('actif')) {
            $query->where('actif', $request->boolean('actif'));
        }

        // Recherche par nom, prénom ou matricule
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('matricule', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('nom')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Créer un utilisateur
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matricule' => 'required|string|max:20|unique:users',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users',
            'password' => 'required|string|min:8',
            'telephone' => 'nullable|string|max:20',
            'role' => 'required|in:employe,gardien,rh,directeur',
            'salaire_base' => 'nullable|numeric|min:0',
            'taux_horaire' => 'nullable|numeric|min:0',
            'actif' => 'nullable|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['actif'] = $validated['actif'] ?? true;

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'data' => $user,
        ], 201);
    }

    /**
     * Afficher un utilisateur
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Modifier un utilisateur
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'matricule' => ['sometimes', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'email' => ['sometimes', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'telephone' => 'nullable|string|max:20',
            'role' => 'sometimes|in:employe,gardien,rh,directeur',
            'salaire_base' => 'nullable|numeric|min:0',
            'taux_horaire' => 'nullable|numeric|min:0',
            'actif' => 'nullable|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur modifié avec succès',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user): JsonResponse
    {
        // Empêcher la suppression de son propre compte
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas supprimer votre propre compte',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès',
        ]);
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleActive(User $user): JsonResponse
    {
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas désactiver votre propre compte',
            ], 403);
        }

        $user->update(['actif' => !$user->actif]);

        return response()->json([
            'success' => true,
            'message' => $user->actif ? 'Utilisateur activé' : 'Utilisateur désactivé',
            'data' => $user,
        ]);
    }

    /**
     * Liste des employés uniquement
     */
    public function employes(Request $request): JsonResponse
    {
        $employes = User::where('role', 'employe')
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $employes,
        ]);
    }
}
