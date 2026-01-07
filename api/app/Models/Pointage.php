<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Pointage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'horodatage',
        'qr_code_id',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'horodatage' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    // Helpers
    public function isEntree(): bool
    {
        return $this->type === 'entree';
    }

    public function isSortie(): bool
    {
        return $this->type === 'sortie';
    }

    // Enregistrer un pointage et mettre à jour la session de travail
    public static function enregistrer(
        int $userId,
        string $type,
        ?int $qrCodeId = null,
        ?float $latitude = null,
        ?float $longitude = null
    ): self {
        $pointage = self::create([
            'user_id' => $userId,
            'type' => $type,
            'horodatage' => Carbon::now(),
            'qr_code_id' => $qrCodeId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        // Mettre à jour la session de travail
        SessionTravail::mettreAJour($userId, Carbon::now()->toDateString());

        return $pointage;
    }

    // Récupérer le dernier pointage d'un utilisateur
    public static function dernierPointage(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->orderBy('horodatage', 'desc')
            ->first();
    }

    // Déterminer le type de pointage attendu
    public static function typeAttendu(int $userId): string
    {
        $dernier = self::dernierPointage($userId);

        if (!$dernier || $dernier->type === 'sortie') {
            return 'entree';
        }

        return 'sortie';
    }
}
