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
        'salaire_brut',
        'montant_heures_sup',
        'taux_cnss',
        'cotisation_cnss',
        'taux_irg',
        'montant_irg',
        'primes',
        'deductions',
        'total_retenues',
        'salaire_net',
        'fichier_pdf',
        'commentaires',
    ];

    protected function casts(): array
    {
        return [
            'total_heures_normales' => 'decimal:2',
            'total_heures_sup' => 'decimal:2',
            'salaire_base' => 'decimal:2',
            'salaire_brut' => 'decimal:2',
            'montant_heures_sup' => 'decimal:2',
            'taux_cnss' => 'decimal:2',
            'cotisation_cnss' => 'decimal:2',
            'taux_irg' => 'decimal:2',
            'montant_irg' => 'decimal:2',
            'primes' => 'decimal:2',
            'deductions' => 'decimal:2',
            'total_retenues' => 'decimal:2',
            'salaire_net' => 'decimal:2',
        ];
    }

    // Taux par defaut
    const TAUX_CNSS_DEFAULT = 3.50;  // 3.5%
    const TAUX_IRG_DEFAULT = 10.00;  // 10%

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

    /**
     * Generer un bulletin de paie avec calcul des cotisations
     *
     * @param int $userId ID de l'employe
     * @param int $mois Mois (1-12)
     * @param int $annee Annee
     * @param float $primes Primes additionnelles
     * @param float $deductions Deductions supplementaires
     * @param float|null $tauxCnss Taux CNSS (defaut 3.5%)
     * @param float|null $tauxIrg Taux IRG (defaut 10%)
     * @param string|null $commentaires Notes/commentaires
     * @return self
     */
    public static function generer(
        int $userId,
        int $mois,
        int $annee,
        float $primes = 0,
        float $deductions = 0,
        ?float $tauxCnss = null,
        ?float $tauxIrg = null,
        ?string $commentaires = null
    ): self {
        $user = User::findOrFail($userId);

        // Calculer la periode
        $dateDebut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $dateFin = Carbon::create($annee, $mois, 1)->endOfMonth();

        // Recuperer le total des heures
        $heures = SessionTravail::totalHeures($userId, $dateDebut->toDateString(), $dateFin->toDateString());

        // Calcul du salaire de base
        $salaireBase = $user->salaire_base;
        $tauxHoraire = $user->taux_horaire;

        // Heures supplementaires = taux horaire x 1.5
        $montantHeuresSup = $heures['heures_supplementaires'] * $tauxHoraire * 1.5;

        // Salaire brut = base + heures sup + primes
        $salaireBrut = $salaireBase + $montantHeuresSup + $primes;

        // Taux des cotisations
        $tauxCnss = $tauxCnss ?? self::TAUX_CNSS_DEFAULT;
        $tauxIrg = $tauxIrg ?? self::TAUX_IRG_DEFAULT;

        // Cotisation CNSS = salaire brut x taux CNSS
        $cotisationCnss = round($salaireBrut * ($tauxCnss / 100), 2);

        // Base imposable IRG = salaire brut - cotisation CNSS
        $baseImposable = $salaireBrut - $cotisationCnss;

        // Montant IRG = base imposable x taux IRG
        $montantIrg = round($baseImposable * ($tauxIrg / 100), 2);

        // Total des retenues = CNSS + IRG + autres deductions
        $totalRetenues = $cotisationCnss + $montantIrg + $deductions;

        // Salaire net = salaire brut - total retenues
        $salaireNet = $salaireBrut - $totalRetenues;

        // Creer ou mettre a jour le bulletin
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
                'salaire_brut' => $salaireBrut,
                'montant_heures_sup' => $montantHeuresSup,
                'taux_cnss' => $tauxCnss,
                'cotisation_cnss' => $cotisationCnss,
                'taux_irg' => $tauxIrg,
                'montant_irg' => $montantIrg,
                'primes' => $primes,
                'deductions' => $deductions,
                'total_retenues' => $totalRetenues,
                'salaire_net' => $salaireNet,
                'commentaires' => $commentaires,
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
