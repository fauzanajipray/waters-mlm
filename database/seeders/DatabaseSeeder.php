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
        $this->user();
        $this->product();
        $this->level();
        $this->member();
    }

    private function user()
    {
        User::updateOrCreate([
            'email' => 'kevin@rectmedia.com',
            'member_id' => 1,
        ], [
            'email' => 'kevin@rectmedia.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Kevin',
            'member_id' => 1,
        ]);

        User::updateOrCreate([
            'email' => 'benny@gmail.com'
        ], [
            'email' => 'benny@gmail.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Benny',
            'member_id' => 2,
        ]);

        User::updateOrCreate([
            'email' => 'andi@gmail.com'
        ], [
            'email' => 'andi@gmail.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Andi',
            'member_id' => 3,
        ]);

        User::updateOrCreate([
            'email' => 'budi@gmail.com'
        ], [
            'email' => 'budi@gmail.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Budi'
        ]);

        User::updateOrCreate([
            'email' => 'fauzan@gmail.com'
        ], [
            'email' => 'fauzan@gmail.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Fauzan',
            'member_id' => 4,
        ]);
    }

    private function product()
    {
        Product::updateOrCreate([
            'name' => 'Product 1',
            'model' => 'Model 1'
        ], [
            'name' => 'Product 1',
            'model' => 'Model 1',
            'price' => 2500000
        ]);

        Product::updateOrCreate([
            'name' => 'Product 1',
            'model' => 'Model 2'
        ], [
            'name' => 'Product 1',
            'model' => 'Model 2',
            'price' => 2700000
        ]);

        Product::updateOrCreate([
            'name' => 'Product B',
            'model' => 'Model A'
        ], [
            'name' => 'Product B',
            'model' => 'Model A',
            'price' => 3000000
        ]);
    }


    private function level()
    {
        Level::updateOrCreate([
            'code' => 'BC',
            'name' => 'Business Consultant'
        ], [
            'code' => 'BC',
            'name' => 'Business Consultant',
            'description' => 'desc ',
            'minimum_downline' => 1,
            'minimum_sold_by_downline' => 1,
            'minimum_sold' => 1,
            'ordering_level' => 1,
            'bp_percentage' => 18, // in percent
            'bs_percentage' => 2,  // in percent
            'or_percentage' => 0.5,// in percent
        ]);

        Level::updateOrCreate([
            'code' => 'EM',
            'name' => 'Executive Manager'
        ], [
            'code' => 'EM',
            'name' => 'Executive Manager',
            'description' => 'desc ',
            'minimum_downline' => 3,
            'minimum_sold_by_downline' => 4,
            'minimum_sold' => 1,
            'ordering_level' => 2,
            'bp_percentage' => 20, // in percent (18+2)
            'bs_percentage' => 2,  // in percent
            'or_percentage' => 0.5,// in percent
        ]);

        Level::updateOrCreate([
            'code' => 'MM',
            'name' => 'Marketing Manager'
        ], [
            'code' => 'MM',
            'name' => 'Marketing Manager',
            'description' => 'desc ',
            'minimum_downline' => 6,
            'minimum_sold_by_downline' => 1,
            'minimum_sold' => 1,
            'ordering_level' => 3,
            'bp_percentage' => 21, // in percent (18+3)
            'bs_percentage' => 2,  // in percent
            'or_percentage' => 1,// in percent
        ]);

        Level::updateOrCreate([
            'code' => 'SM',
            'name' => 'Sales Manager'
        ], [
            'code' => 'SM',
            'name' => 'Sales Manager',
            'description' => 'desc ',
            'minimum_downline' => 6,
            'minimum_sold_by_downline' => 1,
            'minimum_sold' => 1,
            'ordering_level' => 4,
            'bp_percentage' => 23, // in percent (18+5)
            'bs_percentage' => 3,  // in percent
            'or_percentage' => 1,// in percent
        ]);

        Level::updateOrCreate([
            'code' => 'SD',
            'name' => 'Sales Director'
        ], [
            'code' => 'SD',
            'name' => 'Sales Director',
            'description' => 'desc ',
            'minimum_downline' => 6,
            'minimum_sold_by_downline' => 1,
            'minimum_sold' => 1,
            'ordering_level' => 5,
            'bp_percentage' => 26, // in percent (18+8)
            'bs_percentage' => 3,  // in percent
            'or_percentage' => 1,// in percent
        ]);
    }

    private function member()
    {
        Member::updateOrCreate([
            'id' => 1,
            'member_numb' => 'M-001',
        ], [
            'member_numb' => 'M-001',
            'id_card' => '12345',
            'name' => 'Kevin Andianto',
            'level_id' => 2,
            'gender' => 'M',
            'phone' => '08291029320',
            'email' => 'kevin@rectmedia.com',
            'address' => 'Jalan Shukumura', 
            'upline_id' => null
        ]);
        Member::updateOrCreate([
            'id' => 2,
            'member_numb' => 'M-002',
        ], [
            'member_numb' => 'M-002',
            'id_card' => '12346',
            'name' => 'Benny Michael Amazon',
            'level_id' => 1,
            'gender' => 'M',
            'phone' => '082123456',
            'email' => 'benny@gmail.com',
            'address' => 'Jalan Pramuka', 
            'upline_id' => 1
        ]);
        Member::updateOrCreate([
            'id' => 3,
            'member_numb' => 'M-003',
        ], [
            'member_numb' => 'M-003',
            'id_card' => '12347',
            'name' => 'Andi Devina Putri',
            'level_id' => 1,
            'gender' => 'F',
            'phone' => '082129832232',
            'email' => 'andi@gmail.com',
            'address' => 'Jalan Manggis', 
            'upline_id' => 1
        ]);
        Member::updateOrCreate([
            'id' => 4,
            'member_numb' => 'M-004',
        ], [
            'member_numb' => 'M-004',
            'id_card' => '12348',
            'name' => 'Fauzan Mahaletou',
            'level_id' => 1,
            'gender' => 'F',
            'phone' => '08212938292',
            'email' => 'fauzan@gmail.com',
            'address' => 'Jalan Beku', 
            'upline_id' => 1
        ]);
    }


    // private function transaction()
    // {
    //     Member::updateOrCreate([
    //         'code' => 'TR-001',
    //     ], [
    //         'member_numb' => 'M-004',
    //         'name' => 'Angela Mahaletou',
    //         'level_id' => 1,
    //         'gender' => 'F',
    //         'phone' => '08212938292',
    //         'email' => 'amahal@wt.com',
    //         'address' => 'Jalan Beku', 
    //         'upline_id' => 1
    //     ]);
    // }
}