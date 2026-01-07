<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SessionTravail extends Model
{
    use HasFactory;

    protected $table = 'sessions_travail';

    protected $fillable = [
        'user_id',
        'date',
        'heure_entree',
        'heure_sortie',
        'heures_normales',
        'heures_supplementaires',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'heures_normales' => 'decimal:2',
            'heures_supplementaires' => 'decimal:2',
        ];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Calculer les heures travaillées
    public function calculerHeures(): void
    {
        if (!$this->heure_entree || !$this->heure_sortie) {
            $this->heures_normales = 0;
            $this->heures_supplementaires = 0;
            $this->statut = 'incomplet';
            return;
        }

        $entree = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->heure_entree);
        $sortie = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->heure_sortie);

        // Si sortie avant entrée, on ajoute un jour (travail de nuit)
        if ($sortie->lt($entree)) {
            $sortie->addDay();
        }

        $totalHeures = $entree->diffInMinutes($sortie) / 60;

        // Maximum 8h normales, le reste en heures sup
        $this->heures_normales = min($totalHeures, 8);
        $this->heures_supplementaires = max($totalHeures - 8, 0);
        $this->statut = 'complet';
    }

    // Mettre à jour une session à partir des pointages
    public static function mettreAJour(int $userId, string $date): self
    {
        $session = self::firstOrCreate(
            ['user_id' => $userId, 'date' => $date],
            ['statut' => 'incomplet']
        );

        // Récupérer les pointages du jour
        $pointages = Pointage::where('user_id', $userId)
            ->whereDate('horodatage', $date)
            ->orderBy('horodatage')
            ->get();

        $entree = $pointages->where('type', 'entree')->first();
        $sortie = $pointages->where('type', 'sortie')->last();

        if ($entree) {
            $session->heure_entree = Carbon::parse($entree->horodatage)->format('H:i:s');
        }

        if ($sortie) {
            $session->heure_sortie = Carbon::parse($sortie->horodatage)->format('H:i:s');
        }

        $session->calculerHeures();
        $session->save();

        return $session;
    }

    // Récupérer le total des heures sur une période
    public static function totalHeures(int $userId, string $dateDebut, string $dateFin): array
    {
        $sessions = self::where('user_id', $userId)
            ->whereBetween('date', [$dateDebut, $dateFin])
            ->get();

        return [
            'heures_normales' => $sessions->sum('heures_normales'),
            'heures_supplementaires' => $sessions->sum('heures_supplementaires'),
            'total' => $sessions->sum('heures_normales') + $sessions->sum('heures_supplementaires'),
            'jours_travailles' => $sessions->where('statut', 'complet')->count(),
        ];
    }
}
