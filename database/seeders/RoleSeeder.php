<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            [
                'name' => 'Super Admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'Member',
                'guard_name' => 'web',
            ]
        ];

        foreach ($datas as $key => $value) {
            $exists = Role::where('name', $value['name'])
                        ->where('guard_name', $value['guard_name'])
                        ->first();

            if (!$exists) {
                Role::create([
                    'name' => $value['name'],
                    'guard_name' => $value['guard_name'],
                ]);
            }
        }

    }
}
