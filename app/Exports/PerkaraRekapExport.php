<?php

namespace App\Exports;

use App\Enums\JenisTahap;
use App\Models\Perkara;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerkaraRekapExport
{
    protected array $bulanIndo = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Menyimpan daftar baris yang memiliki lebih dari satu tersangka.
     *
     * @var int[]
     */
    protected array $multiTersangkaRows = [];

    public function __construct(
        protected string $mode = 'semua',
        protected ?int $tahun = null,
        protected ?int $bulan = null,
        protected ?string $tanggalMulai = null,
        protected ?string $tanggalSelesai = null
    ) {
        $this->tahun = $this->tahun ?: (int) now()->year;
        $this->bulan = $this->bulan ?: (int) now()->month;
    }

    /**
     * Membangun spreadsheet dan mengembalikan respons streaming file download .xlsx
     */
    public function download(string $filename = 'rekap_perkara.xlsx'): StreamedResponse
    {
        @set_time_limit(120);

        $spreadsheet = $this->generateSpreadsheet();
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Membangun objek Spreadsheet lengkap berdasarkan template LAPJU (45 kolom).
     */
    public function generateSpreadsheet(): Spreadsheet
    {
        $templatePath = resource_path('templates/template_lapju.xlsx');
        if (! file_exists($templatePath)) {
            throw new \RuntimeException("File template export LAPJU tidak ditemukan di: {$templatePath}");
        }

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('History LAPJU');

        // 1. Baris 1: Header Judul Utama
        $sheet->setCellValue('A1', 'PROSES PENYIDIKAN / HISTORY LAPJU');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Baris 2: Keterangan Periode dan Tanggal Export
        $labelPeriode = $this->getPeriodeLabel();
        $nowTz = Carbon::now(config('app.timezone', 'Asia/Makassar'));
        $tanggalExport = sprintf('%02d %s %d', $nowTz->day, $this->bulanIndo[$nowTz->month] ?? '', $nowTz->year);

        $sheet->mergeCells('A2:AS2');
        $sheet->setCellValue('A2', "Periode: {$labelPeriode}  |  Tanggal Export: {$tanggalExport}");
        $sheet->getStyle('A2')->getFont()->setName('Arial')->setSize(10)->setBold(false);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2:AS2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);

        // 3. Baris 6: Koreksi Nomor Kolom (AQ=43, AR=44, AS=45)
        $sheet->setCellValue('AQ6', 43);
        $sheet->setCellValue('AR6', 44);
        $sheet->setCellValue('AS6', 45);

        // 4. Baris 7 dan Seterusnya: Isi Data Perkara
        $currentRow = 7;
        $iteration = 1;
        $this->multiTersangkaRows = [];

        foreach ($this->query()->lazy(100) as $perkara) {
            $this->populateRow($sheet, $currentRow, $iteration, $perkara);
            $currentRow++;
            $iteration++;
        }

        $lastRow = $currentRow - 1;
        if ($lastRow >= 7) {
            $this->applyBulkStyles($sheet, $lastRow);
        }

        // Freeze panes di bawah baris nomor kolom (di bawah baris 6)
        $sheet->freezePane('A7');

        return $spreadsheet;
    }

    /**
     * Terapkan styling dalam satu rentang bulk (menghindari timeout PhpSpreadsheet pada dataset besar).
     */
    protected function applyBulkStyles(Worksheet $sheet, int $lastRow): void
    {
        // 1. Terapkan border tipis, vertical center, dan wrap text ke seluruh area data
        $dataRange = "A7:AS{$lastRow}";
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($dataRange)->getAlignment()->setWrapText(true);

        // 2. Kolom-kolom tanggal: format dd/mm/yyyy dan horizontal center
        $dateCols = ['C', 'E', 'U', 'W', 'Y', 'AA', 'AC', 'AE', 'AG', 'AI', 'AL', 'AN'];
        foreach ($dateCols as $col) {
            $colRange = "{$col}7:{$col}{$lastRow}";
            $sheet->getStyle($colRange)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $sheet->getStyle($colRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // 3. Kolom-kolom teks posisi horizontal tengah
        $centerCols = ['A', 'J', 'K', 'N', 'Q', 'R', 'S'];
        foreach ($centerCols as $col) {
            $colRange = "{$col}7:{$col}{$lastRow}";
            $sheet->getStyle($colRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // 4. Baris yang memiliki multi-tersangka dikembalikan ke rata kiri pada kolom J, K, N
        foreach ($this->multiTersangkaRows as $row) {
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("N{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }
    }

    /**
     * Label periode bahasa Indonesia sesuai mode yang dipilih.
     */
    public function getPeriodeLabel(): string
    {
        if ($this->mode === 'bulan') {
            $namaBulan = $this->bulanIndo[$this->bulan] ?? 'Bulan';

            return "{$namaBulan} {$this->tahun}";
        }

        if ($this->mode === 'rentang' && $this->tanggalMulai && $this->tanggalSelesai) {
            $mulai = Carbon::parse($this->tanggalMulai);
            $selesai = Carbon::parse($this->tanggalSelesai);

            $mulaiStr = sprintf('%02d %s %d', $mulai->day, $this->bulanIndo[$mulai->month] ?? '', $mulai->year);
            $selesaiStr = sprintf('%02d %s %d', $selesai->day, $this->bulanIndo[$selesai->month] ?? '', $selesai->year);

            return "{$mulaiStr} s/d {$selesaiStr}";
        }

        if ($this->mode === 'semua') {
            return 'Semua Data';
        }

        return "Tahun {$this->tahun}";
    }

    /**
     * Menghasilkan nama file standar LAPJU_<periode>_<yyyy-mm-dd>.xlsx
     */
    public function getFilename(): string
    {
        $today = Carbon::now(config('app.timezone', 'Asia/Makassar'))->format('Y-m-d');

        if ($this->mode === 'bulan') {
            $bulanStr = str_pad((string) $this->bulan, 2, '0', STR_PAD_LEFT);

            return "LAPJU_{$this->tahun}-{$bulanStr}_{$today}.xlsx";
        }

        if ($this->mode === 'rentang' && $this->tanggalMulai && $this->tanggalSelesai) {
            return "LAPJU_{$this->tanggalMulai}_sd_{$this->tanggalSelesai}_{$today}.xlsx";
        }

        if ($this->mode === 'semua') {
            return "LAPJU_Semua_{$today}.xlsx";
        }

        return "LAPJU_{$this->tahun}_{$today}.xlsx";
    }

    /**
     * Query data perkara dengan filter periode berdasarkan tanggal LK (Laporan Kejadian).
     * Urutan: tanggal LK terbaru ke terlama (descending), jika sama id menurun.
     *
     * @return Builder<Perkara>
     */
    public function query(): Builder
    {
        $query = Perkara::query()
            ->with([
                'ppnsUtama',
                'ppnsAnggota',
                'tkp.alamat',
                'penuntutUmumInstansi',
                'tersangkas.alamat',
                'tersangkas.berkasPerkara.pasals',
                'tersangkas.berkasPerkara.tahapan.pengadilanInstansi',
                'tersangkas.berkasPerkara.dpos',
                'tersangkas.berkasPerkara.sp3',
                'berkasPerkaras.pasals',
                'berkasPerkaras.tahapan.pengadilanInstansi',
                'berkasPerkaras.dpos',
                'berkasPerkaras.sp3',
            ]);

        if ($this->mode === 'semua') {
            // Tanpa batasan tanggal LK
        } elseif ($this->mode === 'bulan') {
            $query->whereYear('tanggal_laporan_kejadian', $this->tahun)
                ->whereMonth('tanggal_laporan_kejadian', $this->bulan);
        } elseif ($this->mode === 'rentang' && $this->tanggalMulai && $this->tanggalSelesai) {
            $query->whereBetween('tanggal_laporan_kejadian', [$this->tanggalMulai, $this->tanggalSelesai]);
        } else {
            // Default mode tahun
            $query->whereYear('tanggal_laporan_kejadian', $this->tahun);
        }

        // 1 perkara = 1 baris. Urutan: tanggal LK terbaru ke terlama (descending), id menurun
        return $query->orderBy('tanggal_laporan_kejadian', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Mengisi baris data untuk satu perkara pada baris tertentu.
     *
     * @param  Worksheet  $sheet
     */
    protected function populateRow($sheet, int $row, int $no, Perkara $perkara): void
    {
        $berkas = $perkara->berkasPerkaras->first() ?? $perkara->tersangkas->first()?->berkasPerkara;
        $tkp = $perkara->tkp;

        // 1. No. (Kolom A)
        $this->setTextCell($sheet, "A{$row}", $no);

        // 2. Laporan Kejadian - Nomor (Kolom B)
        $this->setTextCell($sheet, "B{$row}", $perkara->nomor_laporan_kejadian ?: '-');

        // 3. Laporan Kejadian - Tanggal (Kolom C)
        $this->setDateCell($sheet, "C{$row}", $perkara->tanggal_laporan_kejadian);

        // 4. Sprindik - Nomor (Kolom D)
        $this->setTextCell($sheet, "D{$row}", $perkara->nomor_surat_perintah ?: '-');

        // 5. Sprindik - Tanggal (Kolom E)
        $this->setDateCell($sheet, "E{$row}", $perkara->tanggal_surat_perintah);

        // 6. Pasal yang disangkakan (Kolom F)
        $pasals = $perkara->berkasPerkaras->flatMap(fn ($b) => $b->pasals)
            ->pluck('pasal')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $this->setTextCell($sheet, "F{$row}", $this->formatNumberedList($pasals));

        // 7. PPNS Utama (Kolom G)
        $ppnsUtama = $perkara->ppnsUtama->pluck('nama')->filter()->values()->all();
        $this->setTextCell($sheet, "G{$row}", $this->formatNumberedList($ppnsUtama));

        // 8. PPNS Anggota (Kolom H)
        $ppnsAnggota = $perkara->ppnsAnggota->pluck('nama')->filter()->values()->all();
        $this->setTextCell($sheet, "H{$row}", $this->formatNumberedList($ppnsAnggota));

        // 9–14. Informasi Pelaku / Tersangka (Kolom I–N)
        $tersangkas = $perkara->tersangkas->sortBy('id')->values();
        $isMultiTersangka = $tersangkas->count() > 1;
        if ($isMultiTersangka) {
            $this->multiTersangkaRows[] = $row;
        }

        $namaList = [];
        $jkList = [];
        $agamaList = [];
        $alamatList = [];
        $pekerjaanList = [];
        $kewarganegaraanList = [];

        if ($tersangkas->isEmpty()) {
            $namaList[] = '-';
            $jkList[] = '-';
            $agamaList[] = '-';
            $alamatList[] = '-';
            $pekerjaanList[] = '-';
            $kewarganegaraanList[] = '-';
        } else {
            foreach ($tersangkas as $idx => $t) {
                $prefix = $isMultiTersangka ? ($idx + 1).'. ' : '';
                $namaList[] = $prefix.($t->nama ?: '-');
                $jkList[] = $prefix.$this->formatJenisKelamin($t->jenis_kelamin);
                $agamaList[] = $prefix.($t->agama ? ucfirst($t->agama) : '-');
                $alamatList[] = $prefix.$this->sanitizeAlamatTersangka($t->alamat?->alamat_lengkap ?: '-');
                $pekerjaanList[] = $prefix.($t->pekerjaan ?: '-');
                $kewarganegaraanList[] = $prefix.($t->kewarganegaraan ?: 'WNI');
            }
        }

        $this->setTextCell($sheet, "I{$row}", implode("\n", $namaList));
        $this->setTextCell($sheet, "J{$row}", implode("\n", $jkList));
        $this->setTextCell($sheet, "K{$row}", implode("\n", $agamaList));
        $this->setTextCell($sheet, "L{$row}", implode("\n", $alamatList));
        $this->setTextCell($sheet, "M{$row}", implode("\n", $pekerjaanList));
        $this->setTextCell($sheet, "N{$row}", implode("\n", $kewarganegaraanList));

        // 15–19. Informasi Sarana / TKP (Kolom O–S)
        $namaTkp = $tkp?->alamat?->nama ?: '-';
        $rawAlamatTkp = $tkp?->alamat?->alamat_lengkap ?: '-';
        $cleanAlamatTkp = $this->sanitizeAlamatTkp($rawAlamatTkp, $tkp?->alamat?->nama);

        $this->setTextCell($sheet, "O{$row}", $namaTkp);
        $this->setTextCell($sheet, "P{$row}", $cleanAlamatTkp);
        $this->setTextCell($sheet, "Q{$row}", $tkp?->klasifikasi ? ucfirst($tkp->klasifikasi) : '-');
        $this->setTextCell($sheet, "R{$row}", $tkp?->legalitas ? ucfirst($tkp->legalitas) : '-');
        $this->setTextCell($sheet, "S{$row}", $tkp?->modus ? ucfirst($tkp->modus) : '-');

        // 20–40. Proses Penyidikan (Kolom T–AN)
        // SPDP (T & U)
        $this->populateTahapCells($sheet, $row, 'T', 'U', $perkara, JenisTahap::SPDP);

        // P-16 (V & W)
        $this->populateTahapCells($sheet, $row, 'V', 'W', $perkara, JenisTahap::P16);

        // P-17 (X & Y)
        $this->populateTahapCells($sheet, $row, 'X', 'Y', $perkara, JenisTahap::P17P);

        // Tahap 1 (Z & AA)
        $this->populateTahapCells($sheet, $row, 'Z', 'AA', $perkara, JenisTahap::TAHAP_1);

        // P-18 / P-19 (AB & AC)
        $this->populateP18P19Cells($sheet, $row, 'AB', 'AC', $perkara);

        // P-21 / P-21a (AD & AE)
        $this->populateTahapCells($sheet, $row, 'AD', 'AE', $perkara, JenisTahap::P21);

        // Tahap 2 (AF & AG)
        $this->populateTahapCells($sheet, $row, 'AF', 'AG', $perkara, JenisTahap::TAHAP_2);

        // Putusan (AH: Nomor, AI: Tanggal, AJ: Keterangan)
        $tahapanPutusan = $this->getTahapanCollection($perkara, JenisTahap::KEPUTUSAN_PENGADILAN);
        $putusanItem = $tahapanPutusan->first();
        if ($tahapanPutusan->count() > 1) {
            $nomorList = [];
            $tanggalList = [];
            $ketList = [];
            foreach ($tahapanPutusan as $idx => $put) {
                $pfx = ($idx + 1).'. ';
                $nomorList[] = $pfx.($put->nomor_surat ?: '-');
                $tanggalList[] = $pfx.($put->tanggal_surat ? $put->tanggal_surat->format('d/m/Y') : '-');
                $ketList[] = $pfx.($put->catatan ?: '-');
            }
            $this->setTextCell($sheet, "AH{$row}", implode("\n", $nomorList));
            $this->setTextCell($sheet, "AI{$row}", implode("\n", $tanggalList));
            $this->setTextCell($sheet, "AJ{$row}", implode("\n", $ketList));
        } elseif ($putusanItem) {
            $this->setTextCell($sheet, "AH{$row}", $putusanItem->nomor_surat ?: '-');
            $this->setDateCell($sheet, "AI{$row}", $putusanItem->tanggal_surat);
            $this->setTextCell($sheet, "AJ{$row}", $putusanItem->catatan ?: '-');
        } else {
            $this->setTextCell($sheet, "AH{$row}", '-');
            $this->setDateCell($sheet, "AI{$row}", null);
            $this->setTextCell($sheet, "AJ{$row}", '-');
        }

        // DPO (AK & AL)
        $dpoList = $perkara->berkasPerkaras->flatMap(fn ($b) => $b->dpos)->sortByDesc('id')->values();
        if ($dpoList->count() > 1) {
            $nomorList = [];
            $tanggalList = [];
            foreach ($dpoList as $idx => $d) {
                $pfx = ($idx + 1).'. ';
                $nomorList[] = $pfx.($d->nomor_surat ?: '-');
                $tanggalList[] = $pfx.($d->tanggal_penetapan ? Carbon::parse($d->tanggal_penetapan)->format('d/m/Y') : '-');
            }
            $this->setTextCell($sheet, "AK{$row}", implode("\n", $nomorList));
            $this->setTextCell($sheet, "AL{$row}", implode("\n", $tanggalList));
        } elseif ($dpoFirst = $dpoList->first()) {
            $this->setTextCell($sheet, "AK{$row}", $dpoFirst->nomor_surat ?: '-');
            $this->setDateCell($sheet, "AL{$row}", $dpoFirst->tanggal_penetapan);
        } else {
            $this->setTextCell($sheet, "AK{$row}", '-');
            $this->setDateCell($sheet, "AL{$row}", null);
        }

        // SP3 (AM & AN)
        $sp3List = $perkara->berkasPerkaras->map(fn ($b) => $b->sp3)->filter()->values();
        if ($sp3List->count() > 1) {
            $nomorList = [];
            $tanggalList = [];
            foreach ($sp3List as $idx => $s) {
                $pfx = ($idx + 1).'. ';
                $nomorList[] = $pfx.($s->nomor_surat ?: '-');
                $tanggalList[] = $pfx.($s->tanggal_sp3 ? Carbon::parse($s->tanggal_sp3)->format('d/m/Y') : '-');
            }
            $this->setTextCell($sheet, "AM{$row}", implode("\n", $nomorList));
            $this->setTextCell($sheet, "AN{$row}", implode("\n", $tanggalList));
        } elseif ($sp3First = $sp3List->first()) {
            $this->setTextCell($sheet, "AM{$row}", $sp3First->nomor_surat ?: '-');
            $this->setDateCell($sheet, "AN{$row}", $sp3First->tanggal_sp3);
        } else {
            $this->setTextCell($sheet, "AM{$row}", '-');
            $this->setDateCell($sheet, "AN{$row}", null);
        }

        // 41–42. Penuntut Umum (Kolom AO & AP)
        $namaInstansiJaksa = $perkara->penuntutUmumInstansi?->nama ?: ($perkara->penuntut_umum_instansi_nama ?: '-');
        $namaJaksa = '-';
        if (! empty($perkara->penuntut_umum_nama)) {
            $namaJaksa = $perkara->penuntut_umum_nama;
            if (! empty($perkara->penuntut_umum_nip)) {
                $namaJaksa .= "\nNIP: ".$perkara->penuntut_umum_nip;
            }
        }
        $this->setTextCell($sheet, "AO{$row}", $namaInstansiJaksa);
        $this->setTextCell($sheet, "AP{$row}", $namaJaksa);

        // 43–44. Pengadilan (Kolom AQ & AR)
        $namaInstansiPengadilan = $putusanItem?->pengadilanInstansi?->nama ?: '-';
        $amarPutusan = $putusanItem?->amar_putusan ?: '-';
        $this->setTextCell($sheet, "AQ{$row}", $namaInstansiPengadilan);
        $this->setTextCell($sheet, "AR{$row}", $amarPutusan);

        // 45. Kendala dan Rencana Tindak Lanjut (Kolom AS)
        $kendalaList = [];
        foreach ($perkara->berkasPerkaras as $b) {
            if ($b->hasSp3() && $b->sp3?->keterangan) {
                $kendalaList[] = $b->sp3->keterangan;
            }
            if ($b->is_dpo) {
                $dpoAktif = $b->dpos->first();
                $kendalaList[] = 'Tersangka DPO: '.($dpoAktif?->keterangan ?? 'Dalam pencarian buron.');
            }
            $p18 = $b->tahapan->firstWhere('jenis_tahap', JenisTahap::P18P->value);
            if (! empty($p18?->catatan)) {
                $kendalaList[] = 'Petunjuk P-18: '.$p18->catatan;
            }
            $p19 = $b->tahapan->firstWhere('jenis_tahap', JenisTahap::P19->value);
            if (! empty($p19?->catatan)) {
                $kendalaList[] = 'Petunjuk P-19: '.$p19->catatan;
            }
        }
        $kendalaList = array_unique(array_filter($kendalaList));
        $kendalaVal = ! empty($kendalaList) ? $this->formatNumberedList($kendalaList) : '-';
        $this->setTextCell($sheet, "AS{$row}", $kendalaVal);
    }

    /**
     * Membantu mengisi nomor dan tanggal tahapan tertentu.
     */
    protected function populateTahapCells($sheet, int $row, string $colNomor, string $colTanggal, Perkara $perkara, JenisTahap $jenis): void
    {
        $collection = $this->getTahapanCollection($perkara, $jenis);

        if ($collection->count() > 1) {
            $nomorList = [];
            $tanggalList = [];
            foreach ($collection as $idx => $tahap) {
                $pfx = ($idx + 1).'. ';
                $nomorList[] = $pfx.($tahap->nomor_surat ?: '-');
                $tanggalList[] = $pfx.($tahap->tanggal_surat ? $tahap->tanggal_surat->format('d/m/Y') : '-');
            }
            $this->setTextCell($sheet, "{$colNomor}{$row}", implode("\n", $nomorList));
            $this->setTextCell($sheet, "{$colTanggal}{$row}", implode("\n", $tanggalList));
        } elseif ($item = $collection->first()) {
            $this->setTextCell($sheet, "{$colNomor}{$row}", $item->nomor_surat ?: '-');
            $this->setDateCell($sheet, "{$colTanggal}{$row}", $item->tanggal_surat);
        } else {
            $this->setTextCell($sheet, "{$colNomor}{$row}", '-');
            $this->setDateCell($sheet, "{$colTanggal}{$row}", null);
        }
    }

    /**
     * Membantu mengisi nomor dan tanggal tahapan P-18 dan P-19.
     */
    protected function populateP18P19Cells($sheet, int $row, string $colNomor, string $colTanggal, Perkara $perkara): void
    {
        $p18List = $this->getTahapanCollection($perkara, JenisTahap::P18P);
        $p19List = $this->getTahapanCollection($perkara, JenisTahap::P19);

        $items = [];
        foreach ($p18List as $p18) {
            $items[] = [
                'label' => 'P-18',
                'nomor' => $p18->nomor_surat,
                'tanggal' => $p18->tanggal_surat,
            ];
        }
        foreach ($p19List as $p19) {
            $items[] = [
                'label' => 'P-19',
                'nomor' => $p19->nomor_surat,
                'tanggal' => $p19->tanggal_surat,
            ];
        }

        if (count($items) > 1) {
            $nomorList = [];
            $tanggalList = [];
            foreach ($items as $idx => $it) {
                $pfx = ($idx + 1).'. ';
                $nomorList[] = $pfx."{$it['label']}: ".($it['nomor'] ?: '-');
                $tanggalList[] = $pfx."{$it['label']}: ".($it['tanggal'] ? $it['tanggal']->format('d/m/Y') : '-');
            }
            $this->setTextCell($sheet, "{$colNomor}{$row}", implode("\n", $nomorList));
            $this->setTextCell($sheet, "{$colTanggal}{$row}", implode("\n", $tanggalList));
        } elseif (count($items) === 1) {
            $it = $items[0];
            $this->setTextCell($sheet, "{$colNomor}{$row}", $it['nomor'] ? "{$it['label']}: {$it['nomor']}" : '-');
            $this->setDateCell($sheet, "{$colTanggal}{$row}", $it['tanggal']);
        } else {
            $this->setTextCell($sheet, "{$colNomor}{$row}", '-');
            $this->setDateCell($sheet, "{$colTanggal}{$row}", null);
        }
    }

    /**
     * Ambil koleksi tahapan tertentu dari seluruh berkas perkara.
     */
    protected function getTahapanCollection(Perkara $perkara, JenisTahap $jenis)
    {
        return $perkara->berkasPerkaras
            ->flatMap(fn ($b) => $b->tahapan)
            ->filter(fn ($t) => $t->jenis_tahap === $jenis)
            ->values();
    }

    /**
     * Format list bernomor jika > 1 item, atau tanpa nomor jika 1 item.
     */
    protected function formatNumberedList(array $items): string
    {
        $filtered = array_values(array_filter($items, fn ($i) => $i !== null && $i !== ''));
        if (empty($filtered)) {
            return '-';
        }
        if (count($filtered) === 1) {
            return (string) $filtered[0];
        }
        $lines = [];
        foreach ($filtered as $idx => $val) {
            $num = $idx + 1;
            $lines[] = "{$num}. {$val}";
        }

        return implode("\n", $lines);
    }

    /**
     * Format tampilan jenis kelamin.
     */
    protected function formatJenisKelamin(?string $jk): string
    {
        return match (strtolower((string) $jk)) {
            'laki-laki', 'laki_laki', 'l' => 'Laki-laki',
            'perempuan', 'p' => 'Perempuan',
            default => $jk ? ucfirst((string) $jk) : '-',
        };
    }

    /**
     * Membersihkan string alamat tersangka untuk ekspor Excel.
     * Menghapus awalan "Tempat tinggal tersangka di " jika ada.
     */
    protected function sanitizeAlamatTersangka(?string $alamat): string
    {
        if (! $alamat || $alamat === '-') {
            return '-';
        }

        $cleaned = preg_replace('/^\s*tempat\s+tinggal\s+(?:tersangka\s+)?(?:di\s*)?[:,-]?\s*/iu', '', $alamat);
        $cleaned = preg_replace('/tempat\s+tinggal\s+tersangka\s+di\s*/iu', '', (string) $cleaned);
        $cleaned = trim((string) $cleaned, " \t\n\r\0\x0B,");

        return $cleaned !== '' ? $cleaned : '-';
    }

    /**
     * Membersihkan string alamat TKP / sarana untuk ekspor Excel.
     * Menghapus nama sarana / TKP yang berulang di kolom alamat karena sudah memiliki kolom tersendiri (Kolom O).
     */
    protected function sanitizeAlamatTkp(?string $alamat, ?string $namaTkp): string
    {
        if (! $alamat || $alamat === '-') {
            return '-';
        }

        $cleaned = $alamat;

        if (! empty($namaTkp) && $namaTkp !== '-') {
            $escapedNama = preg_quote(trim($namaTkp), '/');
            $cleaned = preg_replace('/^\s*'.$escapedNama.'\s*[,:\-–—]?\s*/iu', '', $cleaned);
        }

        $cleaned = trim((string) $cleaned, " \t\n\r\0\x0B,");

        return $cleaned !== '' ? $cleaned : '-';
    }

    /**
     * Set sel tanggal Excel asli dengan format tampilan dd/mm/yyyy.
     *
     * @param  Worksheet  $sheet
     */
    protected function setDateCell($sheet, string $cellCoord, mixed $dateValue): void
    {
        if (! empty($dateValue)) {
            $carbon = $dateValue instanceof Carbon ? $dateValue : Carbon::parse($dateValue);
            $excelDate = Date::dateTimeToExcel($carbon);
            $sheet->setCellValue($cellCoord, $excelDate);
        } else {
            $sheet->setCellValueExplicit($cellCoord, '-', DataType::TYPE_STRING);
        }
    }

    /**
     * Set sel teks eksplisit sebagai String (mencegah formula injection).
     *
     * @param  Worksheet  $sheet
     */
    protected function setTextCell($sheet, string $cellCoord, mixed $value, ?string $alignH = null): void
    {
        $val = $value !== null && $value !== '' ? (string) $value : '-';

        // Netralisir formula injection untuk karakter =, +, @, dan - (bukan dash tunggal)
        if (preg_match('/^([=+@]|-(?!$))/', $val)) {
            $val = "'".$val;
        }

        $sheet->setCellValueExplicit($cellCoord, $val, DataType::TYPE_STRING);

        if ($alignH !== null && $alignH !== Alignment::HORIZONTAL_LEFT) {
            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal($alignH);
        }
    }
}
