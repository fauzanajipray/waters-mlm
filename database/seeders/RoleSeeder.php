<?php

namespace Database\Seeders;

use App\Models\Permission;
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
        // Seeding Role
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

        Log::info('RoleSeeder: Roles seeded');

        // Seeding Permission

        $permissions = [
            'Product' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
            'Branch Product' => [
                'Read :object',
                'Update :object',
            ],
            'Member' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
            'Activation Payment' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
            'Stock' => [
                'Read :object',
                'Create :object',
            ],
            'Stock Card' => [
                'Read :object',
                'Detail :object',
                'Adjustment :object',
            ],
            'Normal Transaction' => [
                'Create :object',
                'Read :object',
                'Delete :object',
                'Detail :object',
            ],
            'Display Transaction' => [
                'Create :object',
                'Read :object',
                'Delete :object',
                'Detail :object',
            ],
            'Demokit Transaction' => [
                'Create :object',
                'Read :object',
                'Delete :object',
                'Detail :object',
            ],
            'Bebas Putus Transaction' => [
                'Create :object',
                'Read :object',
                'Delete :object',
                'Detail :object',
            ],
            'Sparepart Transaction' => [
                'Create :object',
                'Read :object',
                'Delete :object',
                'Detail :object',
            ],
            'Stock Transaction' => [
                'Create :object',
                'Read :object',
                'Delete :object',
                'Detail :object',
            ],
            'Payment Transaction' => [
                'Create :object',
                'Read :object',
            ],
            'Payment Method' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
            'Bonus History' => [
                'Read :object',
            ],
            'Level History' => [
                'Read :object',
            ],
            'Branch' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
                'Add Owner :object',
            ],
            'Customer' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
            'Config Level Member' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
            'Config Level NSI' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
            'Config' => [
                'Read :object',
                'Update :object',
            ],
            'Role' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
            'Permission' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
            'User' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
            'Area' => [
                'Create :object',
                'Read :object',
                'Update :object',
                'Delete :object',
            ],
        ];

        foreach ($permissions as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $permissionName = str_replace(':object', $key,  $value2);
                $exists = Permission::where('name', $permissionName)
                            ->where('guard_name', 'web')
                            ->first();
                if (!$exists) {
                    Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ]);
                }
            }
        }
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $allPermission = Permission::select('id', 'guard_name')->get();
        $superAdmin->permissions()->detach();
        $superAdmin->permissions()->attach($allPermission);
        Log::info('RoleSeeder: Permissions seeded');
    }
}
