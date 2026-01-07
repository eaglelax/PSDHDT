<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'role',
        'salaire_base',
        'taux_horaire',
        'actif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'salaire_base' => 'decimal:2',
            'taux_horaire' => 'decimal:2',
            'actif' => 'boolean',
        ];
    }

    // Relations
    public function pointages(): HasMany
    {
        return $this->hasMany(Pointage::class);
    }

    public function sessionsTravail(): HasMany
    {
        return $this->hasMany(SessionTravail::class);
    }

    public function bulletinsPaie(): HasMany
    {
        return $this->hasMany(BulletinPaie::class);
    }

    public function qrCodesGeneres(): HasMany
    {
        return $this->hasMany(QrCode::class, 'gardien_id');
    }

    // Helpers
    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['rh', 'directeur']);
    }

    public function isGardien(): bool
    {
        return $this->role === 'gardien';
    }

    public function isEmploye(): bool
    {
        return $this->role === 'employe';
    }
}
