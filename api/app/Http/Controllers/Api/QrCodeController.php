<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QrCodeController extends Controller
{
    /**
     * Générer un nouveau QR code (Gardien uniquement)
     */
    public function generate(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isGardien()) {
            return response()->json([
                'success' => false,
                'message' => 'Seul un gardien peut générer un QR code',
            ], 403);
        }

        // Durée de validité en minutes (par défaut 5 minutes)
        $duree = $request->get('duree', 5);

        $qrCode = QrCode::generer($user->id, $duree);

        return response()->json([
            'success' => true,
            'message' => 'QR code généré avec succès',
            'data' => [
                'id' => $qrCode->id,
                'code' => $qrCode->code,
                'date_generation' => $qrCode->date_generation->format('Y-m-d H:i:s'),
                'date_expiration' => $qrCode->date_expiration->format('Y-m-d H:i:s'),
                'secondes_restantes' => $qrCode->date_expiration->diffInSeconds(now()),
            ],
        ], 201);
    }

    /**
     * Récupérer le QR code actuel valide (Gardien uniquement)
     */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isGardien()) {
            return response()->json([
                'success' => false,
                'message' => 'Seul un gardien peut voir le QR code actuel',
            ], 403);
        }

        $qrCode = QrCode::where('gardien_id', $user->id)
            ->where('actif', true)
            ->where('date_expiration', '>', now())
            ->first();

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun QR code actif. Veuillez en générer un nouveau.',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $qrCode->id,
                'code' => $qrCode->code,
                'date_generation' => $qrCode->date_generation->format('Y-m-d H:i:s'),
                'date_expiration' => $qrCode->date_expiration->format('Y-m-d H:i:s'),
                'secondes_restantes' => $qrCode->date_expiration->diffInSeconds(now()),
            ],
        ]);
    }

    /**
     * Valider un QR code (pour le pointage)
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $qrCode = QrCode::validerCode($request->code);

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'QR code invalide ou expiré',
                'valide' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'QR code valide',
            'valide' => true,
            'data' => [
                'qr_code_id' => $qrCode->id,
                'secondes_restantes' => $qrCode->date_expiration->diffInSeconds(now()),
            ],
        ]);
    }

    /**
     * Historique des QR codes générés (Gardien)
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isGardien() && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $query = QrCode::with('gardien:id,nom,prenom');

        if ($user->isGardien()) {
            $query->where('gardien_id', $user->id);
        }

        $qrCodes = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $qrCodes,
        ]);
    }
}
