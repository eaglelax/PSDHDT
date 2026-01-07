<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Directeur
        User::create([
            'matricule' => 'DIR001',
            'nom' => 'DUPONT',
            'prenom' => 'Jean',
            'email' => 'directeur@entreprise.com',
            'password' => Hash::make('password123'),
            'telephone' => '0600000001',
            'role' => 'directeur',
            'salaire_base' => 5000.00,
            'taux_horaire' => 35.00,
            'actif' => true,
        ]);

        // Responsable RH
        User::create([
            'matricule' => 'RH001',
            'nom' => 'MARTIN',
            'prenom' => 'Marie',
            'email' => 'rh@entreprise.com',
            'password' => Hash::make('password123'),
            'telephone' => '0600000002',
            'role' => 'rh',
            'salaire_base' => 3500.00,
            'taux_horaire' => 25.00,
            'actif' => true,
        ]);

        // Gardien
        User::create([
            'matricule' => 'GAR001',
            'nom' => 'BERNARD',
            'prenom' => 'Paul',
            'email' => 'gardien@entreprise.com',
            'password' => Hash::make('password123'),
            'telephone' => '0600000003',
            'role' => 'gardien',
            'salaire_base' => 2000.00,
            'taux_horaire' => 12.00,
            'actif' => true,
        ]);

        // Employés
        $employes = [
            ['matricule' => 'EMP001', 'nom' => 'PETIT', 'prenom' => 'Sophie', 'email' => 'sophie.petit@entreprise.com'],
            ['matricule' => 'EMP002', 'nom' => 'DURAND', 'prenom' => 'Pierre', 'email' => 'pierre.durand@entreprise.com'],
            ['matricule' => 'EMP003', 'nom' => 'LEROY', 'prenom' => 'Julie', 'email' => 'julie.leroy@entreprise.com'],
            ['matricule' => 'EMP004', 'nom' => 'MOREAU', 'prenom' => 'Thomas', 'email' => 'thomas.moreau@entreprise.com'],
            ['matricule' => 'EMP005', 'nom' => 'SIMON', 'prenom' => 'Emma', 'email' => 'emma.simon@entreprise.com'],
        ];

        foreach ($employes as $index => $employe) {
            User::create([
                'matricule' => $employe['matricule'],
                'nom' => $employe['nom'],
                'prenom' => $employe['prenom'],
                'email' => $employe['email'],
                'password' => Hash::make('password123'),
                'telephone' => '060000000' . ($index + 4),
                'role' => 'employe',
                'salaire_base' => 2500.00,
                'taux_horaire' => 15.00,
                'actif' => true,
            ]);
        }
    }
}
