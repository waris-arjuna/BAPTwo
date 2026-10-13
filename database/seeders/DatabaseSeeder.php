<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Alamat;
use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\Panggol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $jabatanKepala = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Balai Besar POM']);
        $jabatanPenyidik = Jabatan::firstOrCreate(['nama_jabatan' => 'Penyidik Pegawai Negeri Sipil (PPNS)']);
        $jabatanAdmin = Jabatan::firstOrCreate(['nama_jabatan' => 'Pranata Komputer Ahli Pertama']);

        $bidangPenindakan = Bidang::firstOrCreate(['nama_bidang' => 'Bidang Penindakan']);
        $bidangTu = Bidang::firstOrCreate(['nama_bidang' => 'Bagian Tata Usaha']);

        $panggolPembina = Panggol::firstOrCreate(['pangkat' => 'Pembina', 'golongan' => 'IV/a']);
        $panggolPenata = Panggol::firstOrCreate(['pangkat' => 'Penata Tk. I', 'golongan' => 'III/d']);
        $panggolPenataMuda = Panggol::firstOrCreate(['pangkat' => 'Penata Muda Tk. I', 'golongan' => 'III/b']);

        $alamatDefault = Alamat::firstOrCreate(
            [
                'provinsi_kode' => '63',
                'kabupaten_kode' => '6371',
                'kecamatan_kode' => '637104',
                'desa_kode' => '6371041006',
                'alamat_detail' => 'Jl. Brigjend H. Hasan Basri No. 40',
            ],
            [
                'provinsi_nama' => 'Kalimantan Selatan',
                'kabupaten_nama' => 'Kota Banjarmasin',
                'kecamatan_nama' => 'Banjarmasin Utara',
                'desa_nama' => 'Pangeran',
                'rt' => '004',
                'rw' => '001',
            ]
        );

        $devUsers = [
            [
                'nama' => 'Administrator BAPTwo',
                'nip' => '199001012015011001',
                'email' => 'admin@baptwo.test',
                'role' => UserRole::ADMINISTRATOR,
                'jabatan_id' => $jabatanAdmin->id,
                'bidang_id' => $bidangTu->id,
                'panggol_id' => $panggolPenataMuda->id,
                'tempat_lahir' => 'Banjarmasin',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'laki-laki',
                'status_pernikahan' => 'kawin',
                'no_telp' => '081251110001',
            ],
            [
                'nama' => 'Pimpinan BPOM Kalsel',
                'nip' => '198502022010011002',
                'email' => 'pimpinan@baptwo.test',
                'role' => UserRole::PIMPINAN,
                'jabatan_id' => $jabatanKepala->id,
                'bidang_id' => $bidangPenindakan->id,
                'panggol_id' => $panggolPembina->id,
                'tempat_lahir' => 'Banjarbaru',
                'tanggal_lahir' => '1985-02-02',
                'jenis_kelamin' => 'laki-laki',
                'status_pernikahan' => 'kawin',
                'no_telp' => '081251110002',
            ],
            [
                'nama' => 'Penyidik PPNS',
                'nip' => '199203032018011003',
                'email' => 'penyidik@baptwo.test',
                'role' => UserRole::PENYIDIK,
                'jabatan_id' => $jabatanPenyidik->id,
                'bidang_id' => $bidangPenindakan->id,
                'panggol_id' => $panggolPenata->id,
                'tempat_lahir' => 'Martapura',
                'tanggal_lahir' => '1992-03-03',
                'jenis_kelamin' => 'perempuan',
                'status_pernikahan' => 'belum_kawin',
                'no_telp' => '081251110003',
            ],
        ];

        foreach ($devUsers as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'nama' => $data['nama'],
                    'nip' => $data['nip'],
                    'role' => $data['role'],
                    'jabatan_id' => $data['jabatan_id'],
                    'bidang_id' => $data['bidang_id'],
                    'panggol_id' => $data['panggol_id'],
                    'alamat_id' => $alamatDefault->id,
                    'tempat_lahir' => $data['tempat_lahir'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'status_pernikahan' => $data['status_pernikahan'],
                    'no_telp' => $data['no_telp'],
                    'password' => Hash::make('password'),
                    'status' => 'pns',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
