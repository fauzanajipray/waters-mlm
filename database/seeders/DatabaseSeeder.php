<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Level;
use App\Models\Member;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(BranchSeeder::class);
        $this->user();
        $this->product();
        $this->level();
        // $this->call(MemberSeeder::class);
        $this->call(ConfigurationSeeder::class);
    }

    private function user()
    {
        $user = User::updateOrCreate([
            'email' => 'kevin@rectmedia.com',
        ], [
            'email' => 'kevin@rectmedia.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Kevin',
            'role_id' => 1,
        ]);
        $user->assignRole('Super Admin');

        $user = User::updateOrCreate([
            'email' => 'benny@gmail.com'
        ], [
            'email' => 'benny@gmail.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Benny',
            'role_id' => 2,
        ]);
        $user->assignRole('Admin');
        

        $user = User::updateOrCreate([
            'email' => 'andi@gmail.com'
        ], [
            'email' => 'andi@gmail.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Andi',
            'role_id' => 2,
        ]);
        $user->assignRole('Admin');

        $user = User::updateOrCreate([
            'email' => 'budi@gmail.com'
        ], [
            'email' => 'budi@gmail.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Budi',
            'role_id' => 2,
        ]);
        $user->assignRole('Admin');

        $user = User::updateOrCreate([
            'email' => 'fauzan@gmail.com'
        ], [
            'email' => 'fauzan@gmail.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Fauzan',
            'role_id' => 1,
        ]);
        $user->assignRole('Super Admin');
        
    }

    private function product()
    {
        Product::updateOrCreate([
            'name' => 'BIO Mineral Pot',
            'model' => 'BMP 3000'
        ], [
            'name' => 'BIO Mineral Pot',
            'model' => 'BMP 3000',
            'capacity' => "22 Liter",
            'price' => 23000000,
        ]);

        Product::updateOrCreate([
            'name' => 'BIO Mineral Pot',
            'model' => 'BMP 2000'
        ], [
            'name' => 'BIO Mineral Pot',
            'model' => 'BMP 2000',
            'capacity' => "14 Liter",
            'price' => 18000000,
        ]);

        Product::updateOrCreate([
            'name' => 'BIO Mineral Pot',
            'model' => 'BMP 1100'
        ], [
            'name' => 'BIO Mineral Pot',
            'model' => 'BMP 1100',
            'capacity' => "11 Liter",
            'price' => 15000000,
        ]);

        Product::updateOrCreate([
            'name' => 'BIO Mineral Pot',
            'model' => 'BMP 1000'
        ], [
            'name' => 'BIO Mineral Pot',
            'model' => 'BMP 1000',
            'capacity' => "10 Liter",
            'price' => 14000000,
        ]);
    }

    private function level()
    {
        /**
         * Notes :
         * - BP = Bonus Penjualan
         * - GM = Goldmine (Bonus dari downline level 1)
         * - OR = Overriding (Bonus dari downline level 2)
         */
        Level::updateOrCreate([
            'code' => 'BC',
            'name' => 'Business Consultant'
        ], [
            'code' => 'BC',
            'name' => 'Business Consultant',
            'description' => 'desc',
            'minimum_downline' => 0,
            'minimum_sold_by_downline' => 0,
            'minimum_sold' => 1, // minimum sold untuk mendapatkan GM (Goldmine) dan OR (Overriding)
            'ordering_level' => 1,
            'bp_percentage' => 18, // in percent
            'gm_percentage' => 0,  // in percent
            'or_percentage' => 0,// in percent
        ]);

        Level::updateOrCreate([
            'code' => 'TL',
            'name' => 'Team Leader'
        ], [
            'code' => 'TL',
            'name' => 'Team Leader',
            'description' => 'desc',
            'minimum_downline' => 3, // minimum downline untuk mendapatkan TL
            'minimum_sold_by_downline' => 1, // minimum sold by downline untuk mendapatkan TL
            'minimum_sold' => 1, // minimum sold untuk mendapatkan GM dan OR
            'ordering_level' => 2,
            'bp_percentage' => 18, // in percent (18)
            'gm_percentage' => 2,  // in percent
            'or_percentage' => 0.5,// in percent
        ]);

        Level::updateOrCreate([
            'code' => 'SM',
            'name' => 'Sales Manager'
        ], [
            'code' => 'SM',
            'name' => 'Sales Manager',
            'description' => 'desc',
            'minimum_downline' => 3, // minimum downline berlevel TM untuk mendapatkan SM
            'minimum_sold_by_downline' => 4, // minimum sold by downline untuk mendapatkan SM
            'minimum_sold' => 1, // minimum sold untuk mendapatkan GM dan OR
            'ordering_level' => 3, 
            'bp_percentage' => 20, // in percent (18+2)
            'gm_percentage' => 3,  // in percent
            'or_percentage' => 1,// in percent
        ]);

        Level::updateOrCreate([
            'code' => 'GM',
            'name' => 'Group Manager'
        ], [
            'code' => 'GM',
            'name' => 'Group Manager',
            'description' => 'desc',
            'minimum_downline' => 6, // minimum downline berlevel SM untuk mendapatkan GM
            'minimum_sold_by_downline' => 4, // minimum sold by downline untuk mendapatkan GM
            'minimum_sold' => 1, // minimum sold untuk mendapatkan GM dan OR
            'ordering_level' => 4,
            'bp_percentage' => 21, // in percent (18+3)
            'gm_percentage' => 4,  // in percent
            'or_percentage' => 1,// in percent
        ]);

        Level::updateOrCreate([
            'code' => 'SD',
            'name' => 'Sales Director'
        ], [
            'code' => 'SD',
            'name' => 'Sales Director',
            'description' => 'desc ',
            'minimum_downline' => 6, // minimum downline 6 berlevel GM untuk mendapatkan SD
            'minimum_sold_by_downline' => 4, // minimum sold by downline untuk mendapatkan SD
            'minimum_sold' => 1, // minimum sold untuk mendapatkan GM dan OR
            'ordering_level' => 5,
            'bp_percentage' => 23, // in percent (18+5)
            'gm_percentage' => 5,  // in percent
            'or_percentage' => 1.5,// in percent
        ]);

        Level::updateOrCreate([
            'code' => 'GD',
            'name' => 'Group Director'
        ], [
            'code' => 'GD',
            'name' => 'Group Director',
            'description' => 'desc',
            'minimum_downline' => 6, // minimum downline 6 berlevel SD untuk mendapatkan GD
            'minimum_sold_by_downline' => 4, // minimum sold by downline untuk mendapatkan GD
            'minimum_sold' => 1, // minimum sold untuk mendapatkan GM dan OR
            'ordering_level' => 6,
            'bp_percentage' => 26, // in percent (18+8)
            'gm_percentage' => 5,  // in percent
            'or_percentage' => 2,// in percent
        ]);
    }
}