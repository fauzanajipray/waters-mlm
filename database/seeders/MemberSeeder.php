<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Member;
use Carbon\Carbon;
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
            'level_id' => 1,
            'gender' => 'M',
            'phone' => '08291029320',
            'email' => 'kevin@rectmedia.com',
            'address' => 'Jalan Shukumura', 
            'join_date' => Carbon::now(),
            'dob' => fake()->date(),
            'postal_code' => fake()->numberBetween(10000, 99999),
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
            'join_date' => Carbon::now(),
            'dob' => fake()->date(),
            'postal_code' => fake()->numberBetween(10000, 99999),
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
            'join_date' => Carbon::now(),
            'dob' => fake()->date(),
            'postal_code' => fake()->numberBetween(10000, 99999),
            'upline_id' => 1
        ]);

        Member::updateOrCreate([
            'id' => 4,
            'member_numb' => 'M-004',
        ], [
            'member_numb' => 'M-004',
            'id_card' => '12352',
            'name' => 'Monkey D Luffy',
            'level_id' => 1, 
            'gender' => 'M',
            'phone' => '08212938296',
            'email' => 'monkeyluffy@gmail.com',
            'address' => 'Jalan Arjuna',
            'join_date' => Carbon::now(),
            'dob' => fake()->date(),
            'postal_code' => fake()->numberBetween(10000, 99999),
            'upline_id' => 2
        ]);

        Member::updateOrCreate([
            'id' => 5,
            'member_numb' => 'M-005',
        ], [
            'member_numb' => 'M-005',
            'id_card' => '12353',
            'name' => 'Roronoa Zoro',
            'level_id' => 1,
            'gender' => 'M',
            'phone' => '08212938297',
            'email' => 'rzoro@gmail.com',
            'address' => 'Jalan Arjuna',
            'join_date' => Carbon::now(),
            'dob' => fake()->date(),
            'postal_code' => fake()->numberBetween(10000, 99999),
            'upline_id' => 2
        ]);

        Member::updateOrCreate([
            'id' => 6,
            'member_numb' => 'M-006',
        ], [
            'member_numb' => 'M-006',
            'id_card' => '12355',
            'name' => 'Usopp',
            'level_id' => 1, 
            'gender' => 'M',
            'phone' => '08212938299',
            'email' => 'usopp@gmail.com',
            'address' => 'Jalan Arjuna',
            'join_date' => Carbon::now(),
            'dob' => fake()->date(),
            'postal_code' => fake()->numberBetween(10000, 99999),
            'upline_id' => 3
        ]);

        Member::updateOrCreate([
            'id' => 7,
            'member_numb' => 'M-007',
        ], [
            'member_numb' => 'M-007',
            'id_card' => '12356',
            'name' => 'Nami',
            'level_id' => 1,
            'gender' => 'F',
            'phone' => '08212938298',
            'email' => 'nami@gmail.com',
            'address' => 'Jalan Arjuna',
            'join_date' => Carbon::now(),
            'dob' => fake()->date(),
            'postal_code' => fake()->numberBetween(10000, 99999),
            'upline_id' => 3
        ]);


        // Customer from Member

        Customer::updateOrCreate([
            'id' => 1,
            'member_id' => 1,
        ], [
            'name' => 'Kevin Andianto',
            'address' => 'Jalan Shukumura',
            'city' => 'Jakarta',
            'phone' => '08291029320',
            'is_member' => "1",
            'member_id' => 1,
        ]);

        Customer::updateOrCreate([
            'id' => 2,
            'member_id' => 2,
        ], [
            'name' => 'Benny Michael Amazon',
            'address' => 'Jalan Pramuka',
            'city' => 'Jakarta',
            'phone' => '082123456',
            'is_member' => "1",
            'member_id' => 2,
        ]);

        Customer::updateOrCreate([
            'id' => 3,
            'member_id' => 3,
        ], [
            'name' => 'Andi Devina Putri',
            'address' => 'Jalan Manggis',
            'city' => 'Jakarta',
            'phone' => '082129832232',
            'is_member' => "1",
            'member_id' => 3,
        ]);

        Customer::updateOrCreate([
            'id' => 4,
            'member_id' => 4,
        ], [
            'name' => 'Monkey D Luffy',
            'address' => 'Jalan Arjuna',
            'city' => 'Jakarta',
            'phone' => '08212938296',
            'is_member' => "1",
            'member_id' => 4,
        ]);

        Customer::updateOrCreate([
            'id' => 5,
            'member_id' => 5,
        ], [
            'name' => 'Roronoa Zoro',
            'address' => 'Jalan Arjuna',
            'city' => 'Jakarta',
            'phone' => '08212938297',
            'is_member' => "1",
            'member_id' => 5,
        ]);

        Customer::updateOrCreate([
            'id' => 6,
            'member_id' => 6,
        ], [
            'name' => 'Usopp',
            'address' => 'Jalan Arjuna',
            'city' => 'Jakarta',
            'phone' => '08212938299',
            'is_member' => "1",
            'member_id' => 6,
        ]);

        Customer::updateOrCreate([
            'id' => 7,
            'member_id' => 7,
        ], [
            'name' => 'Nami',
            'address' => 'Jalan Arjuna',
            'city' => 'Jakarta',
            'phone' => '08212938298',
            'is_member' => "1",
            'member_id' => 7,
        ]);

        // Customer from Non Member

        Customer::updateOrCreate([
            'id' => 8,
            'member_id' => null,
        ], [
            'name' => fake()->name(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'phone' => fake()->numerify('0###########'),
            'member_id' => fake()->numberBetween(1, 7),
        ]);

        for ($i = 0; $i < 30; $i++) {
            Customer::updateOrCreate([
                'id' => $i + 9,
                'member_id' => null,
            ], [
                'name' => fake()->name(),
                'address' => fake()->address(),
                'city' => fake()->city(),
                'phone' => fake()->numerify('0###########'),
                'member_id' => fake()->numberBetween(1, 7),
            ]);
        }
        
    }
}
