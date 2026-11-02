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

class AdminPimpinanSeeder extends Seeder
{
    /**
     * Run the database seeds for Administrator and Pimpinan (Umum).
     */
    public function run(): void
    {
        $alamat = Alamat::firstOrCreate(
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

        $jabatanKepala = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Balai Besar POM']);
        $jabatanKabagTu = Jabatan::firstOrCreate(['nama_jabatan' => 'Kepala Bagian Tata Usaha']);
        $jabatanPrakom = Jabatan::firstOrCreate(['nama_jabatan' => 'Pranata Komputer Ahli Pertama']);

        $bidangTu = Bidang::firstOrCreate(['nama_bidang' => 'Bagian Tata Usaha']);

        $panggolPembinaUtamaMuda = Panggol::firstOrCreate(['pangkat' => 'Pembina Utama Muda', 'golongan' => 'IV/c']);
        $panggolPembina = Panggol::firstOrCreate(['pangkat' => 'Pembina', 'golongan' => 'IV/a']);
        $panggolPenataMudaTk1 = Panggol::firstOrCreate(['pangkat' => 'Penata Muda Tk. I', 'golongan' => 'III/b']);
        $panggolPenataMuda = Panggol::firstOrCreate(['pangkat' => 'Penata Muda', 'golongan' => 'III/a']);

        $users = [
            // 1. Admin Utama
            [
                'email' => 'admin@baptwo.test',
                'nama' => 'Administrator BAPTwo',
                'nip' => '199001012015011001',
                'role' => UserRole::ADMINISTRATOR,
                'jabatan_id' => $jabatanPrakom->id,
                'bidang_id' => $bidangTu->id,
                'panggol_id' => $panggolPenataMudaTk1->id,
                'tempat_lahir' => 'Banjarmasin',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'laki-laki',
                'status_pernikahan' => 'kawin',
                'no_telp' => '081251110001',
            ],
            // 2. Admin Tambahan (IT Officer)
            [
                'email' => 'admin.it@baptwo.test',
                'nama' => 'Fauzan Pratama, S.Kom.',
                'nip' => '199307152018011002',
                'role' => UserRole::ADMINISTRATOR,
                'jabatan_id' => $jabatanPrakom->id,
                'bidang_id' => $bidangTu->id,
                'panggol_id' => $panggolPenataMuda->id,
                'tempat_lahir' => 'Banjarbaru',
                'tanggal_lahir' => '1993-07-15',
                'jenis_kelamin' => 'laki-laki',
                'status_pernikahan' => 'belum_kawin',
                'no_telp' => '081251110011',
            ],
            // 3. Pimpinan Utama (Kepala Balai Besar POM)
            [
                'email' => 'pimpinan@baptwo.test',
                'nama' => 'Drs. H. M. Zainuddin, Apt., M.Kes.',
                'nip' => '197204121998031001',
                'role' => UserRole::UMUM,
                'jabatan_id' => $jabatanKepala->id,
                'bidang_id' => $bidangTu->id,
                'panggol_id' => $panggolPembinaUtamaMuda->id,
                'tempat_lahir' => 'Banjarmasin',
                'tanggal_lahir' => '1972-04-12',
                'jenis_kelamin' => 'laki-laki',
                'status_pernikahan' => 'kawin',
                'no_telp' => '081251110002',
            ],
            // 4. Pimpinan Tambahan (Kepala Bagian Tata Usaha)
            [
                'email' => 'kabag.tu@baptwo.test',
                'nama' => 'Dra. Hj. Nurul Hidayah, M.Si.',
                'nip' => '197508202000032002',
                'role' => UserRole::UMUM,
                'jabatan_id' => $jabatanKabagTu->id,
                'bidang_id' => $bidangTu->id,
                'panggol_id' => $panggolPembina->id,
                'tempat_lahir' => 'Martapura',
                'tanggal_lahir' => '1975-08-20',
                'jenis_kelamin' => 'perempuan',
                'status_pernikahan' => 'kawin',
                'no_telp' => '081251110022',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'alamat_id' => $alamat->id,
                    'password' => Hash::make('password'),
                    'status' => 'pns',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ])
            );

            $this->command->info("User tersimpan: {$user->nama} ({$user->email}) - Role: {$user->role->value}");
        }
    }
}
