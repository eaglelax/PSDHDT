<?php

namespace Database\Seeders;

use App\Models\Pointage;
use App\Models\SessionTravail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PointageSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer tous les employés
        $employes = User::where('role', 'employe')->get();

        // Générer des pointages pour les 30 derniers jours (jours ouvrés)
        $dateDebut = Carbon::now()->subDays(30);
        $dateFin = Carbon::now();

        foreach ($employes as $employe) {
            $date = $dateDebut->copy();

            while ($date->lte($dateFin)) {
                // Ignorer les weekends
                if ($date->isWeekend()) {
                    $date->addDay();
                    continue;
                }

                // Simuler une présence avec variation
                $present = rand(1, 10) > 1; // 90% de présence

                if ($present) {
                    // Heure d'entrée entre 7h30 et 9h00
                    $heureEntree = rand(730, 900);
                    $entreeH = intdiv($heureEntree, 100);
                    $entreeM = $heureEntree % 100;
                    if ($entreeM >= 60) {
                        $entreeH++;
                        $entreeM -= 60;
                    }

                    $horodatageEntree = $date->copy()
                        ->setTime($entreeH, $entreeM, rand(0, 59));

                    Pointage::create([
                        'user_id' => $employe->id,
                        'type' => 'entree',
                        'horodatage' => $horodatageEntree,
                        'qr_code_id' => null,
                    ]);

                    // Heure de sortie entre 16h30 et 19h00
                    $heureSortie = rand(1630, 1900);
                    $sortieH = intdiv($heureSortie, 100);
                    $sortieM = $heureSortie % 100;
                    if ($sortieM >= 60) {
                        $sortieH++;
                        $sortieM -= 60;
                    }

                    $horodatageSortie = $date->copy()
                        ->setTime($sortieH, $sortieM, rand(0, 59));

                    Pointage::create([
                        'user_id' => $employe->id,
                        'type' => 'sortie',
                        'horodatage' => $horodatageSortie,
                        'qr_code_id' => null,
                    ]);

                    // Mettre à jour la session de travail
                    SessionTravail::mettreAJour($employe->id, $date->toDateString());
                }

                $date->addDay();
            }
        }
    }
}
