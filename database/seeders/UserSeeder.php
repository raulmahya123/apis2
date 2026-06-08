<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Akademik',
                'email' => 'admin@belaravel.test',
                'password' => Hash::make('password'),
                'role' => 'admin_akademik',
                'nomor_induk' => 'ADM001',
                'jurusan' => 'Teknik Informatika',
            ],
            [
                'name' => 'Kaprodi Teknik Informatika',
                'email' => 'kaprodi@belaravel.test',
                'password' => Hash::make('password'),
                'role' => 'kaprodi',
                'nomor_induk' => 'KPR001',
                'jurusan' => 'Teknik Informatika',
            ],
            [
                'name' => 'Dr. Budi Santoso',
                'email' => 'dosen1@belaravel.test',
                'password' => Hash::make('password'),
                'role' => 'dosen_pembimbing',
                'nomor_induk' => 'DSN001',
                'jurusan' => 'Teknik Informatika',
            ],
            [
                'name' => 'Dr. Siti Rahmawati',
                'email' => 'dosen2@belaravel.test',
                'password' => Hash::make('password'),
                'role' => 'dosen_pembimbing',
                'nomor_induk' => 'DSN002',
                'jurusan' => 'Sistem Informasi',
            ],
            [
                'name' => 'Dr. Agus Wijaya',
                'email' => 'penguji1@belaravel.test',
                'password' => Hash::make('password'),
                'role' => 'penguji',
                'nomor_induk' => 'PGJ001',
                'jurusan' => 'Teknik Informatika',
            ],
            [
                'name' => 'Dr. Dewi Lestari',
                'email' => 'penguji2@belaravel.test',
                'password' => Hash::make('password'),
                'role' => 'penguji',
                'nomor_induk' => 'PGJ002',
                'jurusan' => 'Sistem Informasi',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'mahasiswa1@belaravel.test',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'nomor_induk' => '202101001',
                'jurusan' => 'Teknik Informatika',
                'angkatan' => '2021',
            ],
            [
                'name' => 'Bella Putri',
                'email' => 'mahasiswa2@belaravel.test',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'nomor_induk' => '202101002',
                'jurusan' => 'Teknik Informatika',
                'angkatan' => '2021',
            ],
            [
                'name' => 'Citra Dewi',
                'email' => 'mahasiswa3@belaravel.test',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'nomor_induk' => '202102001',
                'jurusan' => 'Sistem Informasi',
                'angkatan' => '2021',
            ],
            [
                'name' => 'Dimas Pratama',
                'email' => 'mahasiswa4@belaravel.test',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'nomor_induk' => '202102002',
                'jurusan' => 'Sistem Informasi',
                'angkatan' => '2021',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('10 akun berhasil dibuat!');
    }
}
