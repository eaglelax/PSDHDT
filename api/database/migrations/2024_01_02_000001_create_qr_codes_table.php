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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->foreignId('gardien_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('date_generation');
            $table->dateTime('date_expiration');
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->index(['actif', 'date_expiration']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
