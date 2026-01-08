<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'logo',
        'couleur_primaire',
        'couleur_secondaire',
        'couleur_accent',
        'couleur_texte',
        'email_contact',
        'telephone',
        'adresse',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    /**
     * Accessor pour l'URL complète du logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }
        return url('storage/' . $this->logo);
    }

    /**
     * Récupérer l'entreprise active (singleton pattern)
     */
    public static function getActive(): ?self
    {
        return self::where('actif', true)->first();
    }

    /**
     * Récupérer ou créer l'entreprise par défaut
     */
    public static function getOrCreateDefault(): self
    {
        $entreprise = self::first();

        if (!$entreprise) {
            $entreprise = self::create([
                'nom' => 'Mon Entreprise',
                'couleur_primaire' => '#1a73e8',
                'couleur_secondaire' => '#4285f4',
                'couleur_accent' => '#34a853',
                'couleur_texte' => '#333333',
                'actif' => true,
            ]);
        }

        return $entreprise;
    }

    /**
     * Retourne la configuration pour le frontend
     */
    public function toFrontendConfig(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'logo' => $this->logo,
            'logo_url' => $this->logo_url,
            'couleur_primaire' => $this->couleur_primaire,
            'couleur_secondaire' => $this->couleur_secondaire,
            'couleur_accent' => $this->couleur_accent,
            'couleur_texte' => $this->couleur_texte,
            'email_contact' => $this->email_contact,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
        ];
    }
}
