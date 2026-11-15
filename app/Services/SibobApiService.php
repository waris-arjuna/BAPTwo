<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\Panggol;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SibobApiService
{
    private const PANGKAT_MAP = [
        'I/a' => 'Juru Muda',
        'I/b' => 'Juru Muda Tingkat I',
        'I/c' => 'Juru',
        'I/d' => 'Juru Tingkat I',
        'II/a' => 'Pengatur Muda',
        'II/b' => 'Pengatur Muda Tingkat I',
        'II/c' => 'Pengatur',
        'II/d' => 'Pengatur Tingkat I',
        'III/a' => 'Penata Muda',
        'III/b' => 'Penata Muda Tingkat I',
        'III/c' => 'Penata',
        'III/d' => 'Penata Tingkat I',
        'IV/a' => 'Pembina',
        'IV/b' => 'Pembina Tingkat I',
        'IV/c' => 'Pembina Utama Muda',
        'IV/d' => 'Pembina Utama Madya',
        'IV/e' => 'Pembina Utama',
        'VII' => 'PPPK Golongan VII',
        'IX' => 'PPPK Golongan IX',
    ];

    /**
     * @return array{token: string, user: array<string, mixed>}|null
     */
    public function login(string $email, string $password): ?array
    {
        $response = $this->http()->asForm()->post('/api/v1/login', [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response->serverError()) {
            throw new RuntimeException("SIBOB server error: HTTP {$response->status()}");
        }

        if (! $response->successful()) {
            return null;
        }

        $token = $response->json('data.token') ?? $response->json('token');
        $user = $response->json('data.user') ?? $response->json('user') ?? [];

        if (! is_string($token) || $token === '') {
            return null;
        }

        return [
            'token' => $token,
            'user' => is_array($user) ? $user : [],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getProfile(string $token): ?array
    {
        $response = $this->http()->withToken($token)->get('/api/v1/profile');

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json('data') ?? $response->json();

        return is_array($data) ? $data : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getUsers(?string $token = null): array
    {
        $resolvedToken = $this->resolveToken($token);
        $response = $this->http()->withToken($resolvedToken)->get('/api/v1/users');

        if (! $response->successful()) {
            throw new RuntimeException("Gagal mengambil daftar pegawai SIBOB: HTTP {$response->status()}");
        }

        $json = $response->json();
        $items = $json['data'] ?? $json['users'] ?? $json;

        if (! is_array($items)) {
            throw new RuntimeException('Format response daftar pegawai SIBOB tidak valid.');
        }

        return array_values($items);
    }

    /**
     * @param  array<string, mixed>  $sibobUser
     * @param  array<string, mixed>|null  $profileData
     */
    public function syncUserFromSibobData(
        array $sibobUser,
        ?array $profileData = null,
        ?string $plainPassword = null,
        ?int $actorId = null
    ): User {
        $email = strtolower(trim((string) ($sibobUser['email'] ?? $profileData['user']['email'] ?? '')));
        if ($email === '') {
            throw new RuntimeException('Data pegawai SIBOB tidak memiliki email.');
        }

        // Di response SIBOB /api/v1/users, nip kosong sedangkan NIP 18 digit tersimpan di no_pegawai.
        $rawNip = trim((string) ($sibobUser['nip'] ?? '')) ?: trim((string) ($sibobUser['no_pegawai'] ?? ''));
        $nip = ($rawNip !== '' && $rawNip !== '-' && strtolower($rawNip) !== 'superadmin') ? $rawNip : null;

        $existing = User::withTrashed()
            ->where(function ($q) use ($email, $nip) {
                $q->whereRaw('LOWER(email) = ?', [$email]);
                if ($nip !== null) {
                    $q->orWhere('nip', $nip);
                }
            })
            ->first();

        $divisiName = trim((string) ($sibobUser['divisi_name'] ?? $profileData['user']['divisi'] ?? ''));
        $jabatanName = trim((string) ($sibobUser['jabasn_name'] ?? $profileData['user']['jabasn'] ?? ''));
        $golonganCode = $this->normalizeGolongan((string) ($sibobUser['golongan'] ?? ''));

        $bidangId = $this->resolveBidangId($divisiName);
        $jabatanId = $this->resolveJabatanId($jabatanName);
        $panggolId = $this->resolvePanggolId($golonganCode);

        $rawStatus = strtoupper(trim((string) ($sibobUser['status'] ?? 'PNS')));
        $mappedStatus = match ($rawStatus) {
            'PNS', 'CPNS' => 'pns',
            'PPPK' => 'pppk',
            default => 'lainnya',
        };

        $isOutOrInactive = $rawStatus === 'OUT' || strtoupper(trim((string) ($sibobUser['aktif'] ?? 'Y'))) === 'N';

        $rawJkel = strtoupper(trim((string) ($sibobUser['jenis_kelamin'] ?? $sibobUser['jkel'] ?? '')));
        $jenisKelamin = match ($rawJkel) {
            'L' => 'laki-laki',
            'P' => 'perempuan',
            default => $existing?->jenis_kelamin,
        };

        $rawNikah = strtoupper(trim((string) ($sibobUser['nikah'] ?? '')));
        $statusPernikahan = match ($rawNikah) {
            'Y' => 'kawin',
            'N' => 'belum_kawin',
            default => $existing?->status_pernikahan,
        };

        $tempatLahir = trim((string) ($sibobUser['tempat_lhr'] ?? ''));
        $tempatLahir = ($tempatLahir !== '' && $tempatLahir !== '-') ? $tempatLahir : $existing?->tempat_lahir;

        $tanggalLahir = trim((string) ($sibobUser['tgl_lhr'] ?? ''));
        $tanggalLahir = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalLahir) ? $tanggalLahir : $existing?->tanggal_lahir;

        $noTelp = trim((string) ($sibobUser['telp'] ?? ''));
        $noTelp = ($noTelp !== '' && $noTelp !== '-') ? $noTelp : $existing?->no_telp;

        $nama = trim((string) ($sibobUser['name'] ?? $profileData['user']['name'] ?? $existing?->nama ?? $email));

        if ($existing) {
            $payload = [
                'nama' => $nama,
                'nip' => $nip ?? $existing->nip,
                'email' => $email,
                'jabatan_id' => $jabatanId ?? $existing->jabatan_id,
                'bidang_id' => $bidangId ?? $existing->bidang_id,
                'panggol_id' => $panggolId ?? $existing->panggol_id,
                'jenis_kelamin' => $jenisKelamin,
                'status_pernikahan' => $statusPernikahan,
                'tempat_lahir' => $tempatLahir,
                'tanggal_lahir' => $tanggalLahir,
                'no_telp' => $noTelp,
                'status' => $mappedStatus,
                'is_active' => $isOutOrInactive ? false : (bool) $existing->is_active,
                'updated_by' => $actorId ?? $existing->updated_by,
            ];

            if ($plainPassword !== null && $plainPassword !== '') {
                $payload['password'] = Hash::make($plainPassword);
            }

            $existing->update($payload);

            return $existing;
        }

        $defaultRole = strcasecmp($divisiName, 'Penindakan') === 0
            ? UserRole::PENYIDIK
            : UserRole::UMUM;

        return User::create([
            'nama' => $nama,
            'nip' => $nip,
            'email' => $email,
            'role' => $defaultRole,
            'jabatan_id' => $jabatanId,
            'bidang_id' => $bidangId,
            'panggol_id' => $panggolId,
            'jenis_kelamin' => $jenisKelamin,
            'status_pernikahan' => $statusPernikahan,
            'tempat_lahir' => $tempatLahir,
            'tanggal_lahir' => $tanggalLahir,
            'no_telp' => $noTelp,
            'status' => $mappedStatus,
            'is_active' => ! $isOutOrInactive,
            'password' => Hash::make($plainPassword !== null && $plainPassword !== '' ? $plainPassword : Str::random(32)),
            'email_verified_at' => now(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    /**
     * @return array{created: int, updated: int, deactivated: int, total: int}
     */
    public function syncAllUsers(?string $token = null, ?int $actorId = null): array
    {
        $items = $this->getUsers($token);

        $created = 0;
        $updated = 0;
        $deactivated = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $email = strtolower(trim((string) ($item['email'] ?? '')));
            $noPegawai = strtolower(trim((string) ($item['no_pegawai'] ?? '')));

            if ($email === '' || $email === 'example@gmail.com' || $noPegawai === 'superadmin') {
                continue;
            }

            $user = $this->syncUserFromSibobData($item, null, null, $actorId);

            if ($user->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }

            if (! $user->is_active) {
                $deactivated++;
            }
        }

        $summary = [
            'created' => $created,
            'updated' => $updated,
            'deactivated' => $deactivated,
            'total' => $created + $updated,
        ];

        app(AuditTrailService::class)->log(
            aksi: 'sync_sibob_users',
            tabel: 'user',
            recordId: $actorId ?? 0,
            dataBaru: $summary
        );

        return $summary;
    }

    private function http(): PendingRequest
    {
        $baseUrl = rtrim((string) config('services.sibob.base_url', 'https://sibob.bbpombanjarmasin.com'), '/');
        $timeout = (int) config('services.sibob.timeout', 10);
        $verifySsl = (bool) config('services.sibob.verify_ssl', false);

        $req = Http::baseUrl($baseUrl)->timeout($timeout)->acceptJson();

        return $verifySsl ? $req : $req->withoutVerifying();
    }

    private function resolveToken(?string $token = null): string
    {
        if (is_string($token) && trim($token) !== '') {
            return trim($token);
        }

        $sessionToken = session('sibob_token');
        if (is_string($sessionToken) && trim($sessionToken) !== '') {
            return trim($sessionToken);
        }

        $email = trim((string) config('services.sibob.service_email', ''));
        $password = (string) config('services.sibob.service_password', '');

        if ($email === '' || $password === '') {
            throw new RuntimeException('Kredensial layanan SIBOB belum dikonfigurasi pada SIBOB_SERVICE_EMAIL dan SIBOB_SERVICE_PASSWORD.');
        }

        $auth = $this->login($email, $password);
        if (! $auth || empty($auth['token'])) {
            throw new RuntimeException('Autentikasi akun layanan SIBOB gagal.');
        }

        return $auth['token'];
    }

    private function normalizeGolongan(string $raw): ?string
    {
        $clean = trim($raw);
        if ($clean === '' || $clean === '-/-' || strtoupper($clean) === 'NULL') {
            return null;
        }

        $clean = rtrim($clean, '/- ');

        return $clean !== '' ? $clean : null;
    }

    private function resolveBidangId(string $namaBidang): ?int
    {
        if ($namaBidang === '' || $namaBidang === '-') {
            return null;
        }

        $bidang = Bidang::withTrashed()->firstOrCreate(['nama_bidang' => $namaBidang]);
        if ($bidang->trashed()) {
            $bidang->restore();
        }

        return $bidang->id;
    }

    private function resolveJabatanId(string $namaJabatan): ?int
    {
        if ($namaJabatan === '' || $namaJabatan === '-') {
            return null;
        }

        $jabatan = Jabatan::withTrashed()->firstOrCreate(['nama_jabatan' => $namaJabatan]);
        if ($jabatan->trashed()) {
            $jabatan->restore();
        }

        return $jabatan->id;
    }

    private function resolvePanggolId(?string $golongan): ?int
    {
        if ($golongan === null || $golongan === '') {
            return null;
        }

        $pangkat = self::PANGKAT_MAP[$golongan] ?? "Golongan {$golongan}";

        $panggol = Panggol::withTrashed()->firstOrCreate(
            ['golongan' => $golongan],
            ['pangkat' => $pangkat]
        );

        if ($panggol->trashed()) {
            $panggol->restore();
        }

        return $panggol->id;
    }
}
