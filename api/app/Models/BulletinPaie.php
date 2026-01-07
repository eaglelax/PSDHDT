<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BulletinPaie extends Model
{
    use HasFactory;

    protected $table = 'bulletins_paie';

    protected $fillable = [
        'user_id',
        'mois',
        'annee',
        'total_heures_normales',
        'total_heures_sup',
        'salaire_base',
        'montant_heures_sup',
        'primes',
        'deductions',
        'salaire_net',
        'fichier_pdf',
    ];

    protected function casts(): array
    {
        return [
            'total_heures_normales' => 'decimal:2',
            'total_heures_sup' => 'decimal:2',
            'salaire_base' => 'decimal:2',
            'montant_heures_sup' => 'decimal:2',
            'primes' => 'decimal:2',
            'deductions' => 'decimal:2',
            'salaire_net' => 'decimal:2',
        ];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helpers
    public function getPeriodeAttribute(): string
    {
        $moisNoms = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        return $moisNoms[$this->mois] . ' ' . $this->annee;
    }

    // Générer un bulletin de paie
    public static function generer(int $userId, int $mois, int $annee, float $primes = 0, float $deductions = 0): self
    {
        $user = User::findOrFail($userId);

        // Calculer la période
        $dateDebut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $dateFin = Carbon::create($annee, $mois, 1)->endOfMonth();

        // Récupérer le total des heures
        $heures = SessionTravail::totalHeures($userId, $dateDebut->toDateString(), $dateFin->toDateString());

        // Calcul du salaire
        $salaireBase = $user->salaire_base;
        $tauxHoraire = $user->taux_horaire;

        // Heures supplémentaires = taux horaire × 1.5
        $montantHeuresSup = $heures['heures_supplementaires'] * $tauxHoraire * 1.5;

        // Salaire net
        $salaireNet = $salaireBase + $montantHeuresSup + $primes - $deductions;

        // Créer ou mettre à jour le bulletin
        $bulletin = self::updateOrCreate(
            [
                'user_id' => $userId,
                'mois' => $mois,
                'annee' => $annee,
            ],
            [
                'total_heures_normales' => $heures['heures_normales'],
                'total_heures_sup' => $heures['heures_supplementaires'],
                'salaire_base' => $salaireBase,
                'montant_heures_sup' => $montantHeuresSup,
                'primes' => $primes,
                'deductions' => $deductions,
                'salaire_net' => $salaireNet,
            ]
        );

        return $bulletin;
    }

    // Calculer le salaire brut
    public function getSalaireBrutAttribute(): float
    {
        return $this->salaire_base + $this->montant_heures_sup + $this->primes;
    }
}
