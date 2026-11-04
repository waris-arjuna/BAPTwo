<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Daftar aksi audit yang tergolong milestone penting penyidikan.
     *
     * @var list<string>
     */
    public const MILESTONE_ACTIONS = [
        'finalisasi_tahap',
        'terbitkan_sp3',
        'aktifkan_dpo',
        'nonaktifkan_dpo',
        'finalize_perkara_berkas',
        'create_laporan_kejadian',
        'attach_sprindik',
        'sistem_tidak_selesai',
    ];

    /**
     * @var string
     */
    protected $table = 'audit_log';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'perkara_id',
        'berkas_perkara_id',
        'aksi',
        'tabel',
        'record_id',
        'data_lama',
        'data_baru',
        'ip_address',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_lama' => 'array',
            'data_baru' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Perkara, $this>
     */
    public function perkara(): BelongsTo
    {
        return $this->belongsTo(Perkara::class, 'perkara_id');
    }

    /**
     * @return BelongsTo<BerkasPerkara, $this>
     */
    public function berkasPerkara(): BelongsTo
    {
        return $this->belongsTo(BerkasPerkara::class, 'berkas_perkara_id');
    }

    /**
     * Memeriksa apakah aksi log ini termasuk milestone penting perkara.
     */
    public function isMilestone(): bool
    {
        return in_array($this->aksi, self::MILESTONE_ACTIONS, true);
    }

    /**
     * Scope query untuk hanya mengambil aktivitas milestone penting.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeMilestone(Builder $query): Builder
    {
        return $query->whereIn('aksi', self::MILESTONE_ACTIONS);
    }

    /**
     * Scope query untuk mengurutkan audit log terkini dengan batasan limit.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeTerkini(Builder $query, int $limit = 6): Builder
    {
        return $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit);
    }

    /**
     * Mendapatkan metadata tampilan aksi (label, icon Flaticon, badge, icon_bg).
     *
     * @return array{label: string, icon: string, badge: string, icon_bg: string}
     */
    public function getActionMetaAttribute(): array
    {
        return self::actionMeta($this->aksi ?? '');
    }

    /**
     * Memeriksa apakah aksi audit dilakukan secara otomatis oleh sistem (scheduler/event latar belakang).
     */
    public function isSystemAction(): bool
    {
        return empty($this->user_id);
    }

    /**
     * Mendapatkan label nama aktor pelaksana (nama user atau 'Sistem Otomatis').
     */
    public function getActorLabelAttribute(): string
    {
        if ($this->isSystemAction()) {
            return 'Sistem Otomatis';
        }

        return $this->user?->nama ?? 'Pengguna Tidak Dikenal';
    }

    /**
     * Mendapatkan label peran/role aktor pelaksana.
     */
    public function getActorRoleLabelAttribute(): string
    {
        if ($this->isSystemAction()) {
            return 'Penjadwal Otomatis Sistem';
        }

        return $this->user?->role?->label() ?? 'Pengguna Terdaftar';
    }

    /**
     * Mengekstrak dan merapikan payload data lama & baru untuk visualisasi timeline.
     *
     * @return array{
     *     attributes: array<string, string>,
     *     transitions: list<array{label: string, from: string, to: string}>,
     *     notes: array<string, string>
     * }
     */
    public function getFormattedDetailsAttribute(): array
    {
        $dataLama = is_array($this->data_lama) ? $this->data_lama : [];
        $dataBaru = is_array($this->data_baru) ? $this->data_baru : [];

        $attributes = [];
        $transitions = [];
        $notes = [];

        // 1. Deteksi Transisi Status (Sebelum -> Sesudah)
        $statusKeys = [
            'status_formal' => 'Status Formal',
            'status_proses' => 'Status Proses',
            'status' => 'Status',
            'is_dpo' => 'Status DPO',
        ];

        $handledTransitionKeys = [];

        foreach ($statusKeys as $key => $label) {
            if (array_key_exists($key, $dataLama) && array_key_exists($key, $dataBaru)) {
                $fromVal = $dataLama[$key];
                $toVal = $dataBaru[$key];

                if ($fromVal !== $toVal) {
                    $transitions[] = [
                        'label' => $label,
                        'from' => $this->formatStatusValue($key, $fromVal),
                        'to' => $this->formatStatusValue($key, $toVal),
                    ];
                    $handledTransitionKeys[] = $key;
                }
            }
        }

        // 2. Deteksi Catatan Khusus (Keterangan / Alasan)
        $noteKeyMap = [
            'keterangan' => 'Keterangan',
            'alasan_pencabutan' => 'Alasan Pencabutan',
            'alasan' => 'Alasan',
            'keterangan_penangkapan' => 'Keterangan Penangkapan',
            'deskripsi' => 'Deskripsi',
        ];

        foreach ($noteKeyMap as $key => $label) {
            if (! empty($dataBaru[$key]) && is_string($dataBaru[$key])) {
                $notes[$label] = $dataBaru[$key];
            }
        }

        // 3. Deteksi Atribut Key-Value Rinci
        $ignoredKeys = [
            'id', 'created_at', 'updated_at', 'deleted_at',
            'created_by', 'updated_by', 'user_id', 'perkara_id',
            'berkas_perkara_id', 'penyidik_utama_id', 'penyidik_anggota_ids',
            'laporan_finalized_by', 'sprindik_finalized_by', 'perkara_finalized_by',
            'is_final', 'dpo_dicabut_otomatis', 'file_path', 'nama_file_asli',
            'file_size', 'is_dpo',
        ];

        $attributeLabelMap = [
            'nomor_sp3' => 'Nomor SP3',
            'nomor_surat' => 'Nomor Surat',
            'nomor_laporan_kejadian' => 'Nomor Laporan Kejadian',
            'nomor_surat_perintah' => 'Nomor Sprindik',
            'nomor_berkas' => 'Nomor Berkas',
            'nomor_surat_penangkapan' => 'Nomor Surat Penangkapan',
            'nomor_surat_penahanan' => 'Nomor Surat Penahanan',
            'tanggal_sp3' => 'Tanggal SP3',
            'tanggal_surat' => 'Tanggal Surat',
            'tanggal_laporan_kejadian' => 'Tanggal Laporan Kejadian',
            'tanggal_surat_perintah' => 'Tanggal Sprindik',
            'tanggal_penetapan' => 'Tanggal Penetapan',
            'tanggal_pencabutan' => 'Tanggal Pencabutan',
            'tanggal_penangkapan' => 'Tanggal Penangkapan',
            'tanggal_penahanan' => 'Tanggal Penahanan',
            'jenis_tahap' => 'Tahapan',
            'penandatangan_nama' => 'Penandatangan',
            'penandatangan_jabatan' => 'Jabatan Penandatangan',
            'jumlah_dokumen' => 'Jumlah Dokumen',
            'nama_tersangka' => 'Nama Tersangka',
            'tersangka' => 'Tersangka',
            'pasal' => 'Pasal',
            'status_proses' => 'Status Proses',
            'status_formal' => 'Status Formal',
            'status' => 'Status',
        ];

        foreach ($dataBaru as $key => $val) {
            if (in_array($key, $ignoredKeys, true)) {
                continue;
            }

            if (array_key_exists($key, $noteKeyMap)) {
                continue;
            }

            if (in_array($key, $handledTransitionKeys, true)) {
                continue;
            }

            if ($val === null || $val === '') {
                continue;
            }

            if (is_array($val)) {
                if (array_is_list($val) && count($val) > 0 && is_scalar($val[0])) {
                    $valStr = implode(', ', $val);
                } else {
                    continue;
                }
            } elseif (is_bool($val)) {
                $valStr = $val ? 'Ya' : 'Tidak';
            } else {
                $valStr = (string) $val;
                if (str_starts_with($key, 'tanggal_') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $valStr)) {
                    try {
                        $valStr = Carbon::parse($valStr)->translatedFormat('d F Y');
                    } catch (\Throwable) {
                        // Pertahankan nilai string asli jika gagal diparsing
                    }
                }
            }

            $label = $attributeLabelMap[$key] ?? ucwords(str_replace('_', ' ', $key));
            $attributes[$label] = $valStr;
        }

        return [
            'attributes' => $attributes,
            'transitions' => $transitions,
            'notes' => $notes,
        ];
    }

    /**
     * Memformat representasi string status formal, proses, atau boolean.
     */
    protected function formatStatusValue(string $key, mixed $val): string
    {
        if ($val === null || $val === '') {
            return '-';
        }

        if (is_bool($val)) {
            return $val ? 'Aktif' : 'Tidak Aktif';
        }

        $valStr = (string) $val;

        return match (strtolower($valStr)) {
            'tidak_selesai' => 'Tidak Selesai',
            'selesai' => 'Selesai',
            'aktif' => 'Aktif',
            'nonaktif' => 'Nonaktif',
            'draft' => 'Draft',
            'final' => 'Final',
            default => $valStr,
        };
    }

    /**
     * Kamus terpusat metadata aksi audit log.
     *
     * @return array{label: string, icon: string, badge: string, icon_bg: string}
     */
    public static function actionMeta(string $aksi): array
    {
        $metaMap = [
            'aktifkan_dpo' => [
                'label' => 'Penetapan Status DPO',
                'icon' => 'fi-rr-shield-exclamation',
                'badge' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
                'icon_bg' => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
            ],
            'nonaktifkan_dpo' => [
                'label' => 'Pencabutan Status DPO',
                'icon' => 'fi-rr-user-check',
                'badge' => 'bg-success-50 text-success-700 border-success-200 dark:bg-success-900/20 dark:text-success-300 dark:border-success-800',
                'icon_bg' => 'bg-success-100 text-success-600 dark:bg-success-900/40 dark:text-success-400',
            ],
            'finalisasi_tahap' => [
                'label' => 'Finalisasi Tahapan Perkara',
                'icon' => 'fi-rr-badge-check',
                'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
                'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
            ],
            'simpan_draft_tahap' => [
                'label' => 'Simpan Draft Tahapan Perkara',
                'icon' => 'fi-rr-edit',
                'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
                'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
            ],
            'simpan_sub_tahap' => [
                'label' => 'Simpan Sub-Tahap Tersemat',
                'icon' => 'fi-rr-document-signed',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
                'icon_bg' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400',
            ],
            'finalize_perkara_berkas' => [
                'label' => 'Finalisasi Data Perkara Pra-SPDP',
                'icon' => 'fi-rr-lock',
                'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
                'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
            ],
            'finalisasi_perkara' => [
                'label' => 'Finalisasi Data Perkara Pra-SPDP',
                'icon' => 'fi-rr-lock',
                'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
                'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
            ],
            'save_draft_perkara_berkas' => [
                'label' => 'Simpan Draft Data Perkara Pra-SPDP',
                'icon' => 'fi-rr-edit',
                'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
                'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
            ],
            'simpan_draft_perkara' => [
                'label' => 'Simpan Draft Data Perkara Pra-SPDP',
                'icon' => 'fi-rr-edit',
                'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
                'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
            ],
            'create_laporan_kejadian' => [
                'label' => 'Pembuatan Laporan Kejadian',
                'icon' => 'fi-rr-document',
                'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
                'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
            ],
            'finalisasi_laporan' => [
                'label' => 'Finalisasi Laporan Kejadian',
                'icon' => 'fi-rr-lock',
                'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
                'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
            ],
            'update_laporan_kejadian' => [
                'label' => 'Pembaruan Laporan Kejadian',
                'icon' => 'fi-rr-edit',
                'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
                'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
            ],
            'simpan_draft_laporan' => [
                'label' => 'Simpan Draft Laporan Kejadian',
                'icon' => 'fi-rr-edit',
                'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
                'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
            ],
            'delete_laporan_kejadian' => [
                'label' => 'Penghapusan Laporan Kejadian',
                'icon' => 'fi-rr-trash',
                'badge' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
                'icon_bg' => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
            ],
            'attach_sprindik' => [
                'label' => 'Penerbitan Surat Perintah Penyidikan',
                'icon' => 'fi-rr-document-signed',
                'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
                'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
            ],
            'finalisasi_sprindik' => [
                'label' => 'Finalisasi Surat Perintah Penyidikan',
                'icon' => 'fi-rr-lock',
                'badge' => 'bg-brand-50 text-brand-700 border-brand-200 dark:bg-brand-900/20 dark:text-brand-300 dark:border-brand-800',
                'icon_bg' => 'bg-brand-100 text-brand-600 dark:bg-brand-900/40 dark:text-brand-400',
            ],
            'update_sprindik' => [
                'label' => 'Pembaruan Surat Perintah Penyidikan',
                'icon' => 'fi-rr-edit',
                'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
                'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
            ],
            'simpan_draft_sprindik' => [
                'label' => 'Simpan Draft Sprindik',
                'icon' => 'fi-rr-edit',
                'badge' => 'bg-yellow-50 text-yellow-800 border-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-800',
                'icon_bg' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
            ],
            'remove_sprindik' => [
                'label' => 'Pencabutan Sprindik',
                'icon' => 'fi-rr-cross-circle',
                'badge' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
                'icon_bg' => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
            ],
            'restore_perkara' => [
                'label' => 'Pemulihan Data Perkara',
                'icon' => 'fi-rr-refresh',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
                'icon_bg' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400',
            ],
            'restore_berkas_perkara' => [
                'label' => 'Pemulihan Berkas Perkara',
                'icon' => 'fi-rr-refresh',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800',
                'icon_bg' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400',
            ],
            'terbitkan_sp3' => [
                'label' => 'Penerbitan SP3 (Penghentian Penyidikan)',
                'icon' => 'fi-rr-ban',
                'badge' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
                'icon_bg' => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
            ],
            'sistem_tidak_selesai' => [
                'label' => 'Batas Waktu Tahun Berakhir (Tidak Selesai Otomatis)',
                'icon' => 'fi-rr-calendar-clock',
                'badge' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800',
                'icon_bg' => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
            ],
        ];

        return $metaMap[$aksi] ?? [
            'label' => ucwords(str_replace('_', ' ', $aksi)),
            'icon' => 'fi-rr-time-past',
            'badge' => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-white/[0.05] dark:text-gray-300 dark:border-gray-800',
            'icon_bg' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        ];
    }
}
