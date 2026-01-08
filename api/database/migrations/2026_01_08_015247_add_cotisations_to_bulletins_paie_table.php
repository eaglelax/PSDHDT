<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bulletins_paie', function (Blueprint $table) {
            // Salaire brut (base + heures sup + primes)
            $table->decimal('salaire_brut', 12, 2)->default(0)->after('salaire_base');

            // Cotisations sociales
            $table->decimal('taux_cnss', 5, 2)->default(3.50)->after('montant_heures_sup');
            $table->decimal('cotisation_cnss', 12, 2)->default(0)->after('taux_cnss');

            // Impot sur le revenu
            $table->decimal('taux_irg', 5, 2)->default(10.00)->after('cotisation_cnss');
            $table->decimal('montant_irg', 12, 2)->default(0)->after('taux_irg');

            // Total des retenues
            $table->decimal('total_retenues', 12, 2)->default(0)->after('deductions');

            // Commentaires/notes
            $table->text('commentaires')->nullable()->after('fichier_pdf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulletins_paie', function (Blueprint $table) {
            $table->dropColumn([
                'salaire_brut',
                'taux_cnss',
                'cotisation_cnss',
                'taux_irg',
                'montant_irg',
                'total_retenues',
                'commentaires'
            ]);
        });
    }
};
