<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Branch::create([
            'name' => 'Head Office Semarang',
            'type' => 'PUSAT',
            'address' => 'No. 1, Jalan 1/1, Taman 1, 12345, Semarang',
        ]);

        Branch::create([
            'name' => 'Branch Office Jakarta',
            'type' => 'CABANG',
            'address' => 'No. 2, Jalan 2/2, Taman 2, 12345, Semarang',
        ]);

        Branch::create([
            'name' => 'Branch Office Surabaya',
            'type' => 'CABANG',
            'address' => 'No. 3, Jalan 3/3, Taman 3, 12345, Semarang',
        ]);

        Branch::create([
            'name' => 'Stockist Office Bandung',
            'type' => 'STOKIST',
            'address' => 'No. 4, Jalan 4/4, Taman 4, 12345, Semarang',
        ]);

        Branch::create([
            'name' => 'Stockist Office Yogyakarta',
            'type' => 'STOKIST',
            'address' => 'No. 5, Jalan 5/5, Taman 5, 12345, Semarang',
        ]);
    }
}
