<?php

namespace App\Models;

use App\Enums\JenisTahap;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class BerkasPerkara extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_FORMAL_SELESAI = 'selesai';

    public const STATUS_FORMAL_TIDAK_SELESAI = 'tidak_selesai';

    public const STATUS_FORMAL_DALAM_PROSES = 'dalam_proses';

    /**
     * @var string
     */
    protected $table = 'berkas_perkara';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'perkara_id',
        'tersangka_id',
        'nomor_berkas',
        'tanggal_spdp',
        'status_formal',
        'status_proses',
        'progress_persen',
        'is_dpo',
        'sla_deadline',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_spdp' => 'date',
            'sla_deadline' => 'date',
            'progress_persen' => 'integer',
            'is_dpo' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Perkara, $this>
     */
    public function perkara(): BelongsTo
    {
        return $this->belongsTo(Perkara::class, 'perkara_id');
    }

    /**
     * @return BelongsTo<Tersangka, $this>
     */
    public function tersangka(): BelongsTo
    {
        return $this->belongsTo(Tersangka::class, 'tersangka_id');
    }

    /**
     * Pasal berlapis pada Berkas Perkara (PRD F-04)
     *
     * @return BelongsToMany<Pasal, $this>
     */
    public function pasals(): BelongsToMany
    {
        return $this->belongsToMany(Pasal::class, 'berkas_pasal', 'berkas_perkara_id', 'pasal_id');
    }

    /**
     * @return HasMany<Dokumen, $this>
     */
    public function dokumen(): HasMany
    {
        return $this->hasMany(Dokumen::class, 'berkas_perkara_id');
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'berkas_perkara_id');
    }

    /**
     * @return HasMany<TahapanPerkara, $this>
     */
    public function tahapan(): HasMany
    {
        return $this->hasMany(TahapanPerkara::class, 'berkas_perkara_id');
    }

    /**
     * Tahapan progress terakhir yang telah difinalkan
     */
    public function latestFinalTahap(): ?TahapanPerkara
    {
        if ($this->relationLoaded('tahapan')) {
            return $this->tahapan
                ->where('status', 'final')
                ->sortByDesc('id')
                ->first();
        }

        return $this->tahapan()
            ->where('status', 'final')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Tahapan yang sedang berstatus draft (jika ada)
     */
    public function currentDraftTahap(): ?TahapanPerkara
    {
        if ($this->relationLoaded('tahapan')) {
            return $this->tahapan
                ->where('status', 'draft')
                ->first();
        }

        return $this->tahapan()
            ->where('status', 'draft')
            ->first();
    }

    /**
     * Memeriksa apakah jenis tahapan sudah pernah dicatat pada berkas ini
     */
    public function hasTahap(JenisTahap|string $jenis): bool
    {
        $value = $jenis instanceof JenisTahap ? $jenis->value : $jenis;

        if ($this->relationLoaded('tahapan')) {
            return $this->tahapan->contains(function ($t) use ($value) {
                $tValue = $t->jenis_tahap instanceof JenisTahap ? $t->jenis_tahap->value : $t->jenis_tahap;

                return $tValue === $value;
            });
        }

        return $this->tahapan()->where('jenis_tahap', $value)->exists();
    }

    /**
     * @return HasMany<Dpo, $this>
     */
    public function dpos(): HasMany
    {
        return $this->hasMany(Dpo::class, 'berkas_perkara_id');
    }

    /**
     * Mengambil penetapan DPO yang sedang aktif (jika ada)
     */
    public function activeDpo(): ?Dpo
    {
        if ($this->relationLoaded('dpos')) {
            return $this->dpos
                ->where('status', 'aktif')
                ->sortByDesc('id')
                ->first();
        }

        return $this->dpos()
            ->where('status', 'aktif')
            ->latest('id')
            ->first();
    }

    /**
     * @return HasOne<Sp3, $this>
     */
    public function sp3(): HasOne
    {
        return $this->hasOne(Sp3::class, 'berkas_perkara_id')->latestOfMany();
    }

    /**
     * Memeriksa apakah berkas perkara telah memiliki dokumen/catatan SP3
     */
    public function hasSp3(): bool
    {
        return $this->relationLoaded('sp3') ? $this->sp3 !== null : $this->sp3()->exists();
    }

    /**
     * Memeriksa apakah berkas berstatus formal Tidak Selesai
     */
    public function isTidakSelesai(): bool
    {
        return $this->status_formal === self::STATUS_FORMAL_TIDAK_SELESAI;
    }

    /**
     * Scope untuk berkas perkara yang masih dalam proses penyidikan aktif
     * (status_formal bernilai NULL atau 'dalam_proses')
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeDalamProses(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('status_formal')
                ->orWhere('status_formal', self::STATUS_FORMAL_DALAM_PROSES);
        });
    }

    /**
     * Scope untuk berkas perkara yang sudah Selesai (tuntas Tahap II)
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status_formal', self::STATUS_FORMAL_SELESAI);
    }

    /**
     * Scope untuk berkas perkara yang Tidak Selesai (SP3 atau penutupan 1 Januari)
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeTidakSelesai(Builder $query): Builder
    {
        return $query->where('status_formal', self::STATUS_FORMAL_TIDAK_SELESAI);
    }

    /**
     * Scope untuk memfilter berkas perkara berdasarkan tahun (dan opsional bulan)
     * dari tanggal Laporan Kejadian pada perkara terkait.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeTahunLk(Builder $query, int $tahun, ?int $bulan = null): Builder
    {
        return $query->whereHas('perkara', function (Builder $q) use ($tahun, $bulan) {
            $q->whereYear('tanggal_laporan_kejadian', $tahun);
            if ($bulan !== null && $bulan >= 1 && $bulan <= 12) {
                $q->whereMonth('tanggal_laporan_kejadian', $bulan);
            }
        });
    }

    /**
     * Label tampilan status formal (PRD F-20, F-21: null = "Dalam Proses")
     */
    public function getStatusFormalLabelAttribute(): string
    {
        return match ($this->status_formal) {
            self::STATUS_FORMAL_SELESAI => 'Selesai',
            self::STATUS_FORMAL_TIDAK_SELESAI => 'Tidak Selesai',
            default => 'Dalam Proses',
        };
    }

    /**
     * Tahapan SPDP final yang terhubung dengan berkas ini
     */
    public function spdpTahap(): ?TahapanPerkara
    {
        if ($this->relationLoaded('tahapan')) {
            return $this->tahapan
                ->where('status', 'final')
                ->first(function ($t) {
                    $val = $t->jenis_tahap instanceof JenisTahap ? $t->jenis_tahap->value : $t->jenis_tahap;

                    return $val === JenisTahap::SPDP->value;
                });
        }

        return $this->tahapan()
            ->where('jenis_tahap', JenisTahap::SPDP->value)
            ->where('status', 'final')
            ->first();
    }

    /**
     * Tahapan Tahap 2 final yang terhubung dengan berkas ini
     */
    public function tahap2Final(): ?TahapanPerkara
    {
        if ($this->relationLoaded('tahapan')) {
            return $this->tahapan
                ->where('status', 'final')
                ->first(function ($t) {
                    $val = $t->jenis_tahap instanceof JenisTahap ? $t->jenis_tahap->value : $t->jenis_tahap;

                    return $val === JenisTahap::TAHAP_2->value;
                });
        }

        return $this->tahapan()
            ->where('jenis_tahap', JenisTahap::TAHAP_2->value)
            ->where('status', 'final')
            ->first();
    }

    /**
     * Tanggal efektif batas waktu SLA & batas tahun mutlak (PRD bagian SLA)
     */
    public function getEffectiveDeadlineAttribute(): ?Carbon
    {
        if ($this->sla_deadline) {
            return Carbon::parse($this->sla_deadline);
        }

        if ($this->tanggal_spdp) {
            $tgl = Carbon::parse($this->tanggal_spdp);
            $sla1Bulan = $tgl->copy()->addMonth();
            $akhirTahun = $tgl->copy()->endOfYear();

            return $sla1Bulan->gt($akhirTahun) ? $akhirTahun : $sla1Bulan;
        }

        return null;
    }

    /**
     * Sisa hari menuju effective deadline (negatif jika melewati batas waktu / overdue)
     */
    public function getSisaHariSlaAttribute(): ?int
    {
        $deadline = $this->effective_deadline;
        if (! $deadline) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($deadline->startOfDay(), false);
    }

    /**
     * Status evaluasi SLA (PRD bagian SLA)
     */
    public function getSlaStatusAttribute(): string
    {
        if ($this->status_formal === 'tidak_selesai') {
            return 'tidak_selesai';
        }

        $deadline = $this->effective_deadline;

        if ($this->status_formal === 'selesai') {
            $tahap2 = $this->tahap2Final();
            $selesaiAt = $tahap2?->finalized_at ?? $tahap2?->tanggal_surat ?? $this->updated_at;

            if ($deadline && $selesaiAt && Carbon::parse($selesaiAt)->startOfDay()->gt($deadline->startOfDay())) {
                return 'selesai_lewat_sla';
            }

            return 'selesai_tepat_waktu';
        }

        if (! $deadline) {
            return 'belum_spdp';
        }

        $sisa = $this->sisa_hari_sla;

        if ($sisa < 0) {
            return 'lewat_sla';
        }

        if ($sisa <= 7) {
            return 'mendekati_sla';
        }

        return 'sesuai_sla';
    }

    /**
     * Metadata visual badge SLA untuk antarmuka Blade
     *
     * @return array{label: string, icon: string, badge_class: string}
     */
    public function getSlaBadgeAttribute(): array
    {
        return match ($this->sla_status) {
            'selesai_tepat_waktu' => [
                'label' => 'Selesai Tepat Waktu',
                'icon' => 'fi-rr-badge-check',
                'badge_class' => 'bg-success-50 text-success-700 border-success-200 dark:bg-success-900/20 dark:text-success-300 dark:border-success-800',
            ],
            'selesai_lewat_sla' => [
                'label' => 'Selesai Melebihi SLA',
                'icon' => 'fi-rr-check',
                'badge_class' => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-white/[0.05] dark:text-gray-300 dark:border-gray-800',
            ],
            'tidak_selesai' => [
                'label' => 'Tidak Selesai',
                'icon' => 'fi-rr-ban',
                'badge_class' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
            ],
            'lewat_sla' => [
                'label' => 'Melebihi SLA'.($this->sisa_hari_sla !== null ? ' (+'.abs($this->sisa_hari_sla).' hari)' : ''),
                'icon' => 'fi-rr-exclamation',
                'badge_class' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
            ],
            'mendekati_sla' => [
                'label' => 'Mendekati Batas SLA ('.($this->sisa_hari_sla ?? 0).' hari)',
                'icon' => 'fi-rr-time-forward',
                'badge_class' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
            ],
            'sesuai_sla' => [
                'label' => 'SLA Berjalan ('.($this->sisa_hari_sla ?? 0).' hari)',
                'icon' => 'fi-rr-clock',
                'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
            ],
            default => [
                'label' => 'Pra-SPDP',
                'icon' => 'fi-rr-hourglass-start',
                'badge_class' => 'bg-gray-50 text-gray-600 border-gray-200 dark:bg-white/[0.02] dark:text-gray-400 dark:border-gray-800',
            ],
        };
    }
}
