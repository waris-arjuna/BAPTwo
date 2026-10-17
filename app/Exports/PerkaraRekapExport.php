<?php

namespace App\Exports;

use App\Enums\JenisTahap;
use App\Models\Perkara;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerkaraRekapExport
{
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
        $spreadsheet = $this->generateSpreadsheet();
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Membangun objek Spreadsheet lengkap dengan 44 kolom, 4 baris header, format tanggal, border, dan freeze pane.
     */
    public function generateSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Perkara');

        // Bangun 4 baris Header (Baris 1–4)
        $this->buildHeaders($sheet);

        // Ambil data perkara terurut tanggal LK
        $perkaras = $this->query()->get();

        // Isi baris data (mulai baris 5)
        $currentRow = 5;
        foreach ($perkaras as $index => $perkara) {
            $this->populateRow($sheet, $currentRow, $index + 1, $perkara);
            $currentRow++;
        }

        $lastRow = max(5, $currentRow - 1);

        // Pastikan font seluruh sel adalah Arial
        $sheet->getStyle("A1:AR{$lastRow}")->getFont()->setName('Arial');

        // Border tipis ke seluruh sel data dan header
        $sheet->getStyle("A1:AR{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Freeze panes di bawah baris header (di bawah baris 4)
        $sheet->freezePane('A5');

        // Atur auto-width pada setiap kolom 1–44
        for ($col = 1; $col <= 44; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Query data perkara dengan filter periode berdasarkan tanggal LK (Laporan Kejadian).
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
                'tersangkas.alamat',
                'berkasPerkaras.pasals',
                'berkasPerkaras.tahapan',
                'berkasPerkaras.dpos',
                'berkasPerkaras.sp3',
            ]);

        if ($this->mode === 'semua') {
            // Tanpa batasan tanggal LK, seluruh perkara diekspor
        } elseif ($this->mode === 'bulan') {
            $query->whereYear('tanggal_laporan_kejadian', $this->tahun)
                ->whereMonth('tanggal_laporan_kejadian', $this->bulan);
        } elseif ($this->mode === 'rentang' && $this->tanggalMulai && $this->tanggalSelesai) {
            $query->whereBetween('tanggal_laporan_kejadian', [$this->tanggalMulai, $this->tanggalSelesai]);
        } else {
            // Default mode tahun
            $query->whereYear('tanggal_laporan_kejadian', $this->tahun);
        }

        // Satu baris = satu perkara. Urutkan menurut tanggal LK.
        return $query->orderBy('tanggal_laporan_kejadian', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Membangun 4 baris header sesuai spesifikasi format rekap perkara 44 kolom.
     *
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $sheet
     */
    protected function buildHeaders($sheet): void
    {
        // Tinggi baris header
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getRowDimension(3)->setRowHeight(24);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // Styling umum header: Tebal, Rata Tengah Horizontal & Vertikal, Wrap Text
        $sheet->getStyle('A1:AR4')->getFont()->setBold(true);
        $sheet->getStyle('A1:AR4')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // Latar belakang header baris 1-3
        $sheet->getStyle('A1:AR3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF1F5F9'); // Slate 100

        // Latar belakang baris 4 (Nomor Kolom)
        $sheet->getStyle('A4:AR4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0'); // Slate 200

        // ==========================================
        // 1. Kolom tanpa sub-level (Merge Vertikal Baris 1–3)
        // ==========================================
        $sheet->mergeCells('A1:A3');
        $sheet->setCellValue('A1', 'No.');

        $sheet->mergeCells('F1:F3');
        $sheet->setCellValue('F1', 'Pasal yang disangkakan');

        $sheet->mergeCells('G1:G3');
        $sheet->setCellValue('G1', 'PPNS Utama');

        $sheet->mergeCells('H1:H3');
        $sheet->setCellValue('H1', 'PPNS Anggota');

        $sheet->mergeCells('AQ1:AQ3');
        $sheet->setCellValue('AQ1', 'Pengadilan');

        $sheet->mergeCells('AR1:AR3');
        $sheet->setCellValue('AR1', 'Kendala dan Rencana Tindak Lanjut');

        // ==========================================
        // 2. Grup level 2 (Merge Horizontal Baris 1–2, Header Kolom Baris 3)
        // ==========================================
        // Laporan Kejadian (Cols B, C)
        $sheet->mergeCells('B1:C2');
        $sheet->setCellValue('B1', 'Laporan Kejadian');
        $sheet->setCellValue('B3', 'Nomor');
        $sheet->setCellValue('C3', 'Tanggal');

        // Surat Perintah Penyidikan (Cols D, E)
        $sheet->mergeCells('D1:E2');
        $sheet->setCellValue('D1', 'Surat Perintah Penyidikan');
        $sheet->setCellValue('D3', 'Nomor');
        $sheet->setCellValue('E3', 'Tanggal');

        // Informasi Pelaku/Tersangka (Cols I, J, K, L, M, N)
        $sheet->mergeCells('I1:N2');
        $sheet->setCellValue('I1', 'Informasi Pelaku/Tersangka');
        $sheet->setCellValue('I3', 'Nama');
        $sheet->setCellValue('J3', 'Jenis Kelamin');
        $sheet->setCellValue('K3', 'Agama');
        $sheet->setCellValue('L3', 'Alamat');
        $sheet->setCellValue('M3', 'Pekerjaan');
        $sheet->setCellValue('N3', 'Kewarganegaraan');

        // Informasi Sarana/TKP (Cols O, P, Q, R, S)
        $sheet->mergeCells('O1:S2');
        $sheet->setCellValue('O1', 'Informasi Sarana/TKP');
        $sheet->setCellValue('O3', 'Nama');
        $sheet->setCellValue('P3', 'Alamat');
        $sheet->setCellValue('Q3', 'Klasifikasi');
        $sheet->setCellValue('R3', 'Legalitas');
        $sheet->setCellValue('S3', 'Modus');

        // Penuntut Umum (Cols AO, AP)
        $sheet->mergeCells('AO1:AP2');
        $sheet->setCellValue('AO1', 'Penuntut Umum');
        $sheet->setCellValue('AO3', 'Nama Instansi');
        $sheet->setCellValue('AP3', 'Nama Jaksa');

        // ==========================================
        // 3. Grup level 3: Proses Penyidikan (Cols T s.d. AN)
        // ==========================================
        $sheet->mergeCells('T1:AN1');
        $sheet->setCellValue('T1', 'Proses Penyidikan');

        $sheet->mergeCells('T2:U2');
        $sheet->setCellValue('T2', 'SPDP');
        $sheet->setCellValue('T3', 'Nomor');
        $sheet->setCellValue('U3', 'Tanggal');

        $sheet->mergeCells('V2:W2');
        $sheet->setCellValue('V2', 'P-16');
        $sheet->setCellValue('V3', 'Nomor');
        $sheet->setCellValue('W3', 'Tanggal');

        $sheet->mergeCells('X2:Y2');
        $sheet->setCellValue('X2', 'P-17');
        $sheet->setCellValue('X3', 'Nomor');
        $sheet->setCellValue('Y3', 'Tanggal');

        $sheet->mergeCells('Z2:AA2');
        $sheet->setCellValue('Z2', 'Tahap 1');
        $sheet->setCellValue('Z3', 'Nomor');
        $sheet->setCellValue('AA3', 'Tanggal');

        $sheet->mergeCells('AB2:AC2');
        $sheet->setCellValue('AB2', 'P-18/P-19');
        $sheet->setCellValue('AB3', 'Nomor');
        $sheet->setCellValue('AC3', 'Tanggal');

        $sheet->mergeCells('AD2:AE2');
        $sheet->setCellValue('AD2', 'P-21/P-21a');
        $sheet->setCellValue('AD3', 'Nomor');
        $sheet->setCellValue('AE3', 'Tanggal');

        $sheet->mergeCells('AF2:AG2');
        $sheet->setCellValue('AF2', 'Tahap 2');
        $sheet->setCellValue('AF3', 'Nomor');
        $sheet->setCellValue('AG3', 'Tanggal');

        $sheet->mergeCells('AH2:AJ2');
        $sheet->setCellValue('AH2', 'Putusan');
        $sheet->setCellValue('AH3', 'Nomor');
        $sheet->setCellValue('AI3', 'Tanggal');
        $sheet->setCellValue('AJ3', 'Keterangan');

        $sheet->mergeCells('AK2:AL2');
        $sheet->setCellValue('AK2', 'DPO');
        $sheet->setCellValue('AK3', 'Nomor');
        $sheet->setCellValue('AL3', 'Tanggal');

        $sheet->mergeCells('AM2:AN2');
        $sheet->setCellValue('AM2', 'SP3');
        $sheet->setCellValue('AM3', 'Nomor');
        $sheet->setCellValue('AN3', 'Tanggal');

        // ==========================================
        // 4. Baris 4: Nomor Kolom 1 s.d. 44
        // ==========================================
        for ($col = 1; $col <= 44; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$colLetter}4", $col);
        }
    }

    /**
     * Mengisi baris data untuk satu perkara pada baris tertentu.
     *
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $sheet
     */
    protected function populateRow($sheet, int $row, int $no, Perkara $perkara): void
    {
        $berkas = $perkara->berkasPerkaras->first() ?? $perkara->tersangkas->first()?->berkasPerkara;
        $tersangka = $perkara->tersangkas->first();
        $tkp = $perkara->tkp;

        $tahapanMap = $berkas?->tahapan?->keyBy('jenis_tahap') ?? collect();
        $spdp = $tahapanMap->get(JenisTahap::SPDP->value);
        $p16 = $tahapanMap->get(JenisTahap::P16->value);
        $p17p = $tahapanMap->get(JenisTahap::P17P->value);
        $tahap1 = $tahapanMap->get(JenisTahap::TAHAP_1->value);
        $p18p = $tahapanMap->get(JenisTahap::P18P->value);
        $p19 = $tahapanMap->get(JenisTahap::P19->value);
        $p21 = $tahapanMap->get(JenisTahap::P21->value);
        $tahap2 = $tahapanMap->get(JenisTahap::TAHAP_2->value);
        $putusan = $tahapanMap->get(JenisTahap::KEPUTUSAN_PENGADILAN->value);
        $dpo = $berkas?->dpos?->sortByDesc('id')->first();
        $sp3 = $berkas?->sp3;

        // 1. No.
        $this->setTextCell($sheet, "A{$row}", $no, Alignment::HORIZONTAL_CENTER);

        // 2. Laporan Kejadian - Nomor
        $this->setTextCell($sheet, "B{$row}", $perkara->nomor_laporan_kejadian);

        // 3. Laporan Kejadian - Tanggal (Sel tanggal Excel)
        $this->setDateCell($sheet, "C{$row}", $perkara->tanggal_laporan_kejadian);

        // 4. Sprindik - Nomor
        $this->setTextCell($sheet, "D{$row}", $perkara->nomor_surat_perintah);

        // 5. Sprindik - Tanggal (Sel tanggal Excel)
        $this->setDateCell($sheet, "E{$row}", $perkara->tanggal_surat_perintah);

        // 6. Pasal yang disangkakan (Format tata bahasa: koma, 'dan' di tengah untuk 2 item, dan titik di akhir)
        $pasalList = $berkas?->pasals->pluck('pasal')->filter()->values()->all() ?? [];
        $pasalVal = $this->formatList($pasalList);
        $this->setTextCell($sheet, "F{$row}", $pasalVal);

        // 7. PPNS Utama
        $ppnsUtama = $perkara->ppnsUtama->first()?->name ?? '-';
        $this->setTextCell($sheet, "G{$row}", $ppnsUtama);

        // 8. PPNS Anggota (Format tata bahasa: koma, 'dan' di tengah untuk 2 item, dan titik di akhir)
        $ppnsAnggotaList = $perkara->ppnsAnggota->pluck('name')->filter()->values()->all() ?? [];
        $ppnsAnggotaVal = $this->formatList($ppnsAnggotaList);
        $this->setTextCell($sheet, "H{$row}", $ppnsAnggotaVal);

        // 9. Tersangka - Nama
        $this->setTextCell($sheet, "I{$row}", $tersangka?->nama);

        // 10. Tersangka - Jenis Kelamin
        $jk = $tersangka?->jenis_kelamin;
        $jkVal = match (strtolower((string) $jk)) {
            'laki-laki', 'laki_laki', 'l' => 'Laki-laki',
            'perempuan', 'p' => 'Perempuan',
            default => $jk ? ucfirst((string) $jk) : '-',
        };
        $this->setTextCell($sheet, "J{$row}", $jkVal, Alignment::HORIZONTAL_CENTER);

        // 11. Tersangka - Agama
        $this->setTextCell($sheet, "K{$row}", $tersangka?->agama ? ucfirst($tersangka->agama) : '-', Alignment::HORIZONTAL_CENTER);

        // 12. Tersangka - Alamat
        $alamatTersangka = $tersangka?->alamat?->alamat_lengkap
            ?? $tersangka?->alamat?->desa_nama
            ?? $tersangka?->alamat?->kecamatan_nama
            ?? $tersangka?->alamat?->kabupaten_nama
            ?? '-';
        $this->setTextCell($sheet, "L{$row}", $alamatTersangka);

        // 13. Tersangka - Pekerjaan
        $this->setTextCell($sheet, "M{$row}", $tersangka?->pekerjaan);

        // 14. Tersangka - Kewarganegaraan
        $this->setTextCell($sheet, "N{$row}", $tersangka?->kewarganegaraan ?? 'WNI', Alignment::HORIZONTAL_CENTER);

        // 15. TKP - Nama
        $namaTkp = $tkp?->nama_sarana ?? $perkara->deskripsi ?? 'TKP '.($tkp?->alamat?->kabupaten_nama ?? 'Kalsel');
        $this->setTextCell($sheet, "O{$row}", $namaTkp);

        // 16. TKP - Alamat
        $alamatTkp = $tkp?->alamat?->alamat_lengkap
            ?? $tkp?->alamat?->desa_nama
            ?? $tkp?->alamat?->kecamatan_nama
            ?? $tkp?->alamat?->kabupaten_nama
            ?? '-';
        $this->setTextCell($sheet, "P{$row}", $alamatTkp);

        // 17. TKP - Klasifikasi
        $this->setTextCell($sheet, "Q{$row}", $tkp?->klasifikasi ? ucfirst($tkp->klasifikasi) : '-', Alignment::HORIZONTAL_CENTER);

        // 18. TKP - Legalitas
        $this->setTextCell($sheet, "R{$row}", $tkp?->legalitas ? ucfirst($tkp->legalitas) : '-', Alignment::HORIZONTAL_CENTER);

        // 19. TKP - Modus
        $this->setTextCell($sheet, "S{$row}", $tkp?->modus ? ucfirst($tkp->modus) : '-', Alignment::HORIZONTAL_CENTER);

        // 20. SPDP - Nomor
        $this->setTextCell($sheet, "T{$row}", $spdp?->nomor_surat);

        // 21. SPDP - Tanggal
        $this->setDateCell($sheet, "U{$row}", $spdp?->tanggal_surat);

        // 22. P-16 - Nomor
        $this->setTextCell($sheet, "V{$row}", $p16?->nomor_surat);

        // 23. P-16 - Tanggal
        $this->setDateCell($sheet, "W{$row}", $p16?->tanggal_surat);

        // 24. P-17 - Nomor
        $this->setTextCell($sheet, "X{$row}", $p17p?->nomor_surat);

        // 25. P-17 - Tanggal
        $this->setDateCell($sheet, "Y{$row}", $p17p?->tanggal_surat);

        // 26. Tahap 1 - Nomor
        $this->setTextCell($sheet, "Z{$row}", $tahap1?->nomor_surat);

        // 27. Tahap 1 - Tanggal
        $this->setDateCell($sheet, "AA{$row}", $tahap1?->tanggal_surat);

        // 28. P-18/P-19 - Nomor
        $p1819Nomor = [];
        if (! empty($p18p?->nomor_surat)) {
            $p1819Nomor[] = "P-18: {$p18p->nomor_surat}";
        }
        if (! empty($p19?->nomor_surat)) {
            $p1819Nomor[] = "P-19: {$p19->nomor_surat}";
        }
        $p1819NomorVal = ! empty($p1819Nomor) ? implode("\n", $p1819Nomor) : '-';
        $this->setTextCell($sheet, "AB{$row}", $p1819NomorVal);

        // 29. P-18/P-19 - Tanggal (Gunakan tanggal surat P-19 atau P-18P)
        $tglP1819 = $p19?->tanggal_surat ?? $p18p?->tanggal_surat;
        $this->setDateCell($sheet, "AC{$row}", $tglP1819);

        // 30. P-21/P-21a - Nomor
        $this->setTextCell($sheet, "AD{$row}", $p21?->nomor_surat);

        // 31. P-21/P-21a - Tanggal
        $this->setDateCell($sheet, "AE{$row}", $p21?->tanggal_surat);

        // 32. Tahap 2 - Nomor
        $this->setTextCell($sheet, "AF{$row}", $tahap2?->nomor_surat);

        // 33. Tahap 2 - Tanggal
        $this->setDateCell($sheet, "AG{$row}", $tahap2?->tanggal_surat);

        // 34. Putusan - Nomor
        $this->setTextCell($sheet, "AH{$row}", $putusan?->nomor_surat);

        // 35. Putusan - Tanggal
        $this->setDateCell($sheet, "AI{$row}", $putusan?->tanggal_surat);

        // 36. Putusan - Keterangan
        $this->setTextCell($sheet, "AJ{$row}", $putusan?->catatan);

        // 37. DPO - Nomor
        $this->setTextCell($sheet, "AK{$row}", $dpo?->nomor_surat);

        // 38. DPO - Tanggal
        $this->setDateCell($sheet, "AL{$row}", $dpo?->tanggal_surat);

        // 39. SP3 - Nomor
        $this->setTextCell($sheet, "AM{$row}", $sp3?->nomor_surat);

        // 40. SP3 - Tanggal
        $this->setDateCell($sheet, "AN{$row}", $sp3?->tanggal_surat);

        // 41. Penuntut Umum - Nama Instansi
        $namaInstansi = '-';
        if (! empty($p16?->catatan)) {
            // Cek apakah catatan memuat nama Kejaksaan
            if (preg_match('/(Kejari|Kejati|Kejaksaan)[^\n\r,.]+/i', $p16->catatan, $m)) {
                $namaInstansi = trim($m[0]);
            } else {
                $namaInstansi = 'Kejaksaan Tinggi Kalimantan Selatan';
            }
        }
        $this->setTextCell($sheet, "AO{$row}", $namaInstansi);

        // 42. Penuntut Umum - Nama Jaksa (Multi-nilai baris baru)
        $namaJaksaVal = '-';
        if (! empty($p16?->catatan)) {
            $namaJaksaVal = $p16->catatan;
        }
        $this->setTextCell($sheet, "AP{$row}", $namaJaksaVal);

        // 43. Pengadilan
        $pengadilanVal = '-';
        if ($putusan !== null) {
            if (preg_match('/(PN|Pengadilan Negeri)[^\n\r,.]+/i', (string) $putusan->catatan, $mp)) {
                $pengadilanVal = trim($mp[0]);
            } else {
                $pengadilanVal = 'Pengadilan Negeri Banjarmasin';
            }
        }
        $this->setTextCell($sheet, "AQ{$row}", $pengadilanVal);

        // 44. Kendala dan Rencana Tindak Lanjut
        $kendalaList = [];
        if ($berkas?->is_dpo) {
            $kendalaList[] = 'Tersangka DPO: '.($dpo?->keterangan ?? 'Dalam pencarian buron.');
        }
        if ($berkas?->hasSp3()) {
            $kendalaList[] = 'SP3: '.($sp3?->keterangan ?? 'Penghentian penyidikan.');
        }
        if (! empty($p18p?->catatan)) {
            $kendalaList[] = 'P-18: '.$p18p->catatan;
        }
        if (! empty($p19?->catatan)) {
            $kendalaList[] = 'P-19: '.$p19->catatan;
        }
        if (! empty($berkas?->sla_deadline) && $berkas?->status_formal !== 'selesai') {
            $sisaHari = (int) now()->diffInDays(Carbon::parse($berkas->sla_deadline), false);
            if ($sisaHari < 0) {
                $kendalaList[] = 'Melebihi batas SLA ('.abs($sisaHari).' hari lewat).';
            }
        }
        $kendalaVal = ! empty($kendalaList) ? implode("\n", $kendalaList) : '-';
        $this->setTextCell($sheet, "AR{$row}", $kendalaVal);
    }

    /**
     * Set sel tanggal Excel asli dengan format dd-mmm-yy.
     *
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $sheet
     */
    protected function setDateCell($sheet, string $cellCoord, mixed $dateValue): void
    {
        if (! empty($dateValue)) {
            $carbon = $dateValue instanceof Carbon ? $dateValue : Carbon::parse($dateValue);
            $excelDate = Date::dateTimeToExcel($carbon);
            $sheet->setCellValue($cellCoord, $excelDate);
            $sheet->getStyle($cellCoord)->getNumberFormat()->setFormatCode('dd-mmm-yy');
            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cellCoord)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        } else {
            $sheet->setCellValue($cellCoord, '-');
            $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cellCoord)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }
    }

    /**
     * Set sel teks dengan default wrap text dan horizontal alignment.
     *
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $sheet
     */
    protected function setTextCell($sheet, string $cellCoord, mixed $value, string $align = Alignment::HORIZONTAL_LEFT): void
    {
        $val = (string) ($value !== null && $value !== '' ? $value : '-');
        $sheet->setCellValue($cellCoord, $val);
        $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal($align);
        $sheet->getStyle($cellCoord)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle($cellCoord)->getAlignment()->setWrapText(true);
    }

    /**
     * Memformat daftar multi-nilai (Pasal atau PPNS Anggota) dengan tata bahasa Indonesia:
     * - 0 item: '-'
     * - 1 item: 'Item 1.'
     * - 2 item: 'Item 1 dan Item 2.'
     * - >= 3 item: 'Item 1, Item 2, dan Item 3.'
     *
     * @param  array<int, string>  $items
     */
    public function formatList(array $items): string
    {
        $items = array_values(array_filter(array_map('trim', $items)));

        $count = count($items);

        if ($count === 0) {
            return '-';
        }

        // Pastikan string berakhiran tanda titik tunggal
        $ensureDot = function (string $str): string {
            return str_ends_with($str, '.') ? $str : $str.'.';
        };

        if ($count === 1) {
            return $ensureDot($items[0]);
        }

        if ($count === 2) {
            $first = rtrim($items[0], '.');
            $second = $ensureDot($items[1]);

            return "{$first} dan {$second}";
        }

        // 3 atau lebih item (Format PUEBI: A, B, dan C.)
        $lastItem = $ensureDot(array_pop($items));
        $allPrevious = array_map(fn ($item) => rtrim($item, '.'), $items);

        return implode(', ', $allPrevious).', dan '.$lastItem;
    }
}
