<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'gardien_id',
        'date_generation',
        'date_expiration',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'date_generation' => 'datetime',
            'date_expiration' => 'datetime',
            'actif' => 'boolean',
        ];
    }

    // Relations
    public function gardien(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gardien_id');
    }

    public function pointages(): HasMany
    {
        return $this->hasMany(Pointage::class);
    }

    // Helpers
    public function isValide(): bool
    {
        return $this->actif && $this->date_expiration->isFuture();
    }

    public function isExpire(): bool
    {
        return $this->date_expiration->isPast();
    }

    // Génération d'un nouveau code QR
    public static function generer(int $gardienId, int $dureeMinutes = 5): self
    {
        // Désactiver les anciens codes du gardien
        self::where('gardien_id', $gardienId)
            ->where('actif', true)
            ->update(['actif' => false]);

        // Créer un nouveau code
        return self::create([
            'code' => Str::uuid()->toString(),
            'gardien_id' => $gardienId,
            'date_generation' => Carbon::now(),
            'date_expiration' => Carbon::now()->addMinutes($dureeMinutes),
            'actif' => true,
        ]);
    }

    // Valider un code
    public static function validerCode(string $code): ?self
    {
        $qrCode = self::where('code', $code)
            ->where('actif', true)
            ->where('date_expiration', '>', Carbon::now())
            ->first();

        return $qrCode;
    }
}
