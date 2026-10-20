<?php

namespace App\Http\Controllers;

use App\Models\BerkasPerkara;
use App\Models\Perkara;
use App\Models\User;
use App\Services\PerkaraService;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SampahController extends Controller
{
    public function __construct(
        protected PerkaraService $perkaraService,
        protected UserService $userService
    ) {}

    /**
     * Tampilkan daftar data yang terhapus (soft delete) di Halaman Sampah (PRD F-44)
     */
    public function index(Request $request): View
    {
        $activeTab = $request->string('tab', 'perkara')->trim()->toString();
        if (! in_array($activeTab, ['perkara', 'berkas', 'user'], true)) {
            $activeTab = 'perkara';
        }

        $search = $request->string('search')->trim()->toString();
        $tanggalDari = $request->string('tanggal_dari', $request->string('tanggal')->toString())->trim()->toString();
        $tanggalSampai = $request->string('tanggal_sampai')->trim()->toString();

        // Hitung total item per kategori untuk badge tab
        $countPerkara = Perkara::onlyTrashed()->count();
        $countBerkas = BerkasPerkara::onlyTrashed()->count();
        $countUser = User::onlyTrashed()->count();

        $items = match ($activeTab) {
            'berkas' => BerkasPerkara::onlyTrashed()
                ->with(['perkara', 'tersangka'])
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('nomor_berkas', 'like', "%{$search}%")
                            ->orWhereHas('tersangka', function (Builder $sub) use ($search) {
                                $sub->where('nama', 'like', "%{$search}%")
                                    ->orWhere('nik', 'like', "%{$search}%");
                            })
                            ->orWhereHas('perkara', function (Builder $sub) use ($search) {
                                $sub->where('nomor_laporan_kejadian', 'like', "%{$search}%")
                                    ->orWhere('nomor_surat_perintah', 'like', "%{$search}%");
                            });
                    });
                })
                ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                    $query->whereDate('deleted_at', '>=', $tanggalDari);
                })
                ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                    $query->whereDate('deleted_at', '<=', $tanggalSampai);
                })
                ->orderByDesc('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            'user' => User::onlyTrashed()
                ->with(['jabatan', 'bidang', 'panggol'])
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                            ->orWhere('nip', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                    $query->whereDate('deleted_at', '>=', $tanggalDari);
                })
                ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                    $query->whereDate('deleted_at', '<=', $tanggalSampai);
                })
                ->orderByDesc('deleted_at')
                ->paginate(10)
                ->withQueryString(),

            default => Perkara::onlyTrashed()
                ->with(['dokumenLaporan', 'creator'])
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('nomor_laporan_kejadian', 'like', "%{$search}%")
                            ->orWhere('nomor_surat_perintah', 'like', "%{$search}%")
                            ->orWhere('deskripsi', 'like', "%{$search}%");
                    });
                })
                ->when($tanggalDari !== '', function (Builder $query) use ($tanggalDari) {
                    $query->whereDate('deleted_at', '>=', $tanggalDari);
                })
                ->when($tanggalSampai !== '', function (Builder $query) use ($tanggalSampai) {
                    $query->whereDate('deleted_at', '<=', $tanggalSampai);
                })
                ->orderByDesc('deleted_at')
                ->paginate(10)
                ->withQueryString(),
        };

        if ($request->header('X-Filter-Partial')) {
            return view('pages.sampah._table-partial', compact('items', 'activeTab', 'countPerkara', 'countBerkas', 'countUser'));
        }

        return view('pages.sampah.index', compact('items', 'activeTab', 'countPerkara', 'countBerkas', 'countUser'));
    }

    /**
     * Pulihkan data Laporan Kejadian & Perkara dari Sampah
     */
    public function restorePerkara(Request $request, int $id): RedirectResponse
    {
        Gate::authorize('soft-delete');

        $this->perkaraService->restorePerkara($id, $request->user());

        return redirect()
            ->route('sampah.index', ['tab' => 'perkara'])
            ->with('success', [
                'title' => 'Pemulihan Berhasil',
                'message' => 'Data Perkara & Laporan Kejadian berhasil dipulihkan dari Sampah.',
            ]);
    }

    /**
     * Pulihkan data Berkas Perkara dari Sampah
     */
    public function restoreBerkas(Request $request, int $id): RedirectResponse
    {
        Gate::authorize('soft-delete');

        $this->perkaraService->restoreBerkas($id, $request->user());

        return redirect()
            ->route('sampah.index', ['tab' => 'berkas'])
            ->with('success', [
                'title' => 'Pemulihan Berhasil',
                'message' => 'Berkas Perkara berhasil dipulihkan dari Sampah.',
            ]);
    }

    /**
     * Pulihkan akun Pengguna dari Sampah
     */
    public function restoreUser(Request $request, int $id): RedirectResponse
    {
        Gate::authorize('manage-users');

        $this->userService->restoreUser($id, $request->user()?->id);

        return redirect()
            ->route('sampah.index', ['tab' => 'user'])
            ->with('success', [
                'title' => 'Pemulihan Berhasil',
                'message' => 'Akun Pengguna berhasil dipulihkan dari Sampah.',
            ]);
    }
}
