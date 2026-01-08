<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EntrepriseController extends Controller
{
    /**
     * Récupérer la configuration de l'entreprise (public)
     */
    public function show(): JsonResponse
    {
        $entreprise = Entreprise::getOrCreateDefault();

        return response()->json([
            'success' => true,
            'data' => $entreprise->toFrontendConfig(),
        ]);
    }

    /**
     * Mettre à jour la configuration (admin uniquement)
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'nom' => 'nullable|string|max:255',
            'couleur_primaire' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'couleur_secondaire' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'couleur_accent' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'couleur_texte' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'email_contact' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:500',
        ]);

        $entreprise = Entreprise::getOrCreateDefault();

        $entreprise->update($request->only([
            'nom',
            'couleur_primaire',
            'couleur_secondaire',
            'couleur_accent',
            'couleur_texte',
            'email_contact',
            'telephone',
            'adresse',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Configuration mise à jour avec succès',
            'data' => $entreprise->fresh()->toFrontendConfig(),
        ]);
    }

    /**
     * Upload du logo (admin uniquement)
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $entreprise = Entreprise::getOrCreateDefault();

        // Supprimer l'ancien logo si existant
        if ($entreprise->logo) {
            Storage::disk('public')->delete($entreprise->logo);
        }

        // Sauvegarder le nouveau logo
        $file = $request->file('logo');
        $filename = 'logo_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('logos', $filename, 'public');

        $entreprise->update(['logo' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Logo uploadé avec succès',
            'data' => [
                'logo' => $path,
                'logo_url' => $entreprise->fresh()->logo_url,
            ],
        ]);
    }

    /**
     * Supprimer le logo
     */
    public function deleteLogo(): JsonResponse
    {
        $entreprise = Entreprise::getOrCreateDefault();

        if ($entreprise->logo) {
            Storage::disk('public')->delete($entreprise->logo);
            $entreprise->update(['logo' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logo supprimé avec succès',
        ]);
    }

    /**
     * Réinitialiser aux couleurs par défaut
     */
    public function resetColors(): JsonResponse
    {
        $entreprise = Entreprise::getOrCreateDefault();

        $entreprise->update([
            'couleur_primaire' => '#1a73e8',
            'couleur_secondaire' => '#4285f4',
            'couleur_accent' => '#34a853',
            'couleur_texte' => '#333333',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Couleurs réinitialisées',
            'data' => $entreprise->fresh()->toFrontendConfig(),
        ]);
    }
}
