<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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
        
        Member::updateOrCreate([
            'id' => 5,
            'member_numb' => 'M-005',
        ], [
            'member_numb' => 'M-005',
            'id_card' => '12349',
            'name' => 'Rizky Billar',
            'level_id' => 1,
            'gender' => 'M',
            'phone' => '08212938293',
            'email' => 'rizkybillar@gmail.com',
            'address' => 'Jalan Arjuna',
            'upline_id' => 4
        ]);

        Member::updateOrCreate([
            'id' => 6,
            'member_numb' => 'M-006',
        ], [
            'member_numb' => 'M-006',
            'id_card' => '12350',
            'name' => 'Noelle Farmer',
            'level_id' => 1,
            'gender' => 'M',
            'phone' => '08212938294',
            'email' => 'noellefarmer@gmail.com',
            'address' => 'Jalan Arjuna',
            'upline_id' => 4
        ]);

        Member::updateOrCreate([
            'id' => 7,
            'member_numb' => 'M-007',
        ], [
            'member_numb' => 'M-007',
            'id_card' => '12351',
            'name' => 'Tsubasa Ozora',
            'level_id' => 1,
            'gender' => 'M',
            'phone' => '08212938295',
            'email' => 'tsubas@gmail.com',
            'address' => 'Jalan Arjuna',
            'upline_id' => 4
        ]);

        Member::updateOrCreate([
            'id' => 8,
            'member_numb' => 'M-008',
        ], [
            'member_numb' => 'M-008',
            'id_card' => '12352',
            'name' => 'Monkey D Luffy',
            'level_id' => 1, 
            'gender' => 'M',
            'phone' => '08212938296',
            'email' => 'monkeyluffy@gmail.com',
            'address' => 'Jalan Arjuna',
            'upline_id' => 5
        ]);

        Member::updateOrCreate([
            'id' => 9,
            'member_numb' => 'M-009',
        ], [
            'member_numb' => 'M-009',
            'id_card' => '12353',
            'name' => 'Roronoa Zoro',
            'level_id' => 1,
            'gender' => 'M',
            'phone' => '08212938297',
            'email' => 'rzoro@gmail.com',
            'address' => 'Jalan Arjuna',
            'upline_id' => 8
        ]);
    }
}
