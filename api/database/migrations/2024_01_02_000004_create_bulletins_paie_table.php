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
        Schema::create('bulletins_paie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('mois');
            $table->year('annee');
            $table->decimal('total_heures_normales', 6, 2)->default(0);
            $table->decimal('total_heures_sup', 6, 2)->default(0);
            $table->decimal('salaire_base', 10, 2)->default(0);
            $table->decimal('montant_heures_sup', 10, 2)->default(0);
            $table->decimal('primes', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('salaire_net', 10, 2)->default(0);
            $table->string('fichier_pdf', 255)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'mois', 'annee']);
            $table->index(['user_id', 'annee', 'mois']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletins_paie');
    }
};
