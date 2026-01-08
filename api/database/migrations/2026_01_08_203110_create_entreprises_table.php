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
        Schema::create('entreprises', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->default('Mon Entreprise');
            $table->string('logo')->nullable();
            $table->string('couleur_primaire', 7)->default('#1a73e8');
            $table->string('couleur_secondaire', 7)->default('#4285f4');
            $table->string('couleur_accent', 7)->default('#34a853');
            $table->string('couleur_texte', 7)->default('#333333');
            $table->string('email_contact')->nullable();
            $table->string('telephone')->nullable();
            $table->text('adresse')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entreprises');
    }
};
