<?php

namespace Database\Seeders;

use App\Models\Entreprise;
use Illuminate\Database\Seeder;

class EntrepriseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Entreprise::create([
            'nom' => 'Entreprise Demo',
            'couleur_primaire' => '#1a73e8',
            'couleur_secondaire' => '#4285f4',
            'couleur_accent' => '#34a853',
            'couleur_texte' => '#333333',
            'email_contact' => 'contact@entreprise.com',
            'telephone' => '+213 555 123 456',
            'adresse' => '123 Rue Principale, Alger, Algérie',
            'actif' => true,
        ]);
    }
}
