<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role',
        'jabatan_id',
        'bidang_id',
        'panggol_id',
        'alamat_id',
        'nama',
        'nip',
        'email',
        'password',
        'foto_profil',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'status_pernikahan',
        'no_telp',
        'status',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'tanggal_lahir' => 'date',
            'is_active' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function panggol(): BelongsTo
    {
        return $this->belongsTo(Panggol::class, 'panggol_id');
    }

    /**
     * Pangkat dari relasi panggol.
     */
    public function getPangkatAttribute(): ?string
    {
        return $this->panggol?->pangkat;
    }

    /**
     * Label nama, NIP, dan pangkat PPNS yang rapi untuk antarmuka.
     */
    public function getFormattedPpnsLabelAttribute(): string
    {
        $parts = [];
        if ($this->nip && $this->nip !== '-') {
            $parts[] = "NIP: {$this->nip}";
        }
        if ($this->panggol?->pangkat) {
            $parts[] = $this->panggol->pangkat;
        }

        return ! empty($parts)
            ? "{$this->nama} (".implode(', ', $parts).')'
            : $this->nama;
    }

    public function alamat(): BelongsTo
    {
        return $this->belongsTo(Alamat::class, 'alamat_id');
    }

    public function fotoProfilUrl(): string
    {
        if ($this->foto_profil && Storage::disk('public')->exists($this->foto_profil)) {
            return asset('storage/'.ltrim($this->foto_profil, '/'));
        }

        return asset('images/user/owner.png');
    }

    public function panggolLabel(): string
    {
        return $this->panggol ? $this->panggol->label() : '-';
    }

    public function jenisKelaminLabel(): string
    {
        return match ($this->jenis_kelamin) {
            'laki-laki' => __('Laki-laki'),
            'perempuan' => __('Perempuan'),
            default => '-',
        };
    }

    public function statusPernikahanLabel(): string
    {
        return match ($this->status_pernikahan) {
            'belum_kawin' => __('Belum Kawin'),
            'kawin' => __('Kawin'),
            'cerai_hidup' => __('Cerai Hidup'),
            'cerai_mati' => __('Cerai Mati'),
            default => '-',
        };
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMINISTRATOR;
    }

    public function isPimpinan(): bool
    {
        return $this->role === UserRole::PIMPINAN;
    }

    public function isPenyidik(): bool
    {
        return $this->role === UserRole::PENYIDIK;
    }

    public function isUmum(): bool
    {
        return $this->role === UserRole::UMUM;
    }
}
