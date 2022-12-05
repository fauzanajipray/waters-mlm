<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Http\Controllers\Admin\TransactionCrudController;
use App\Models\ActivationPayments;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\Customer;
use App\Models\Level;
use App\Models\Member;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ConfigurationSeeder::class);
        $this->call(RoleSeeder::class);
        // $this->call(BranchSeeder::class);
        // $this->user();
        $this->product();
        $this->level();
        $this->office();
        // $this->member();
        // $this->customer();
        // $this->transaction();
        $this->paymentMethod();
        // // $this->call(MemberSeeder::class);
    }

    public function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
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
        $filename = Storage::path('sample/product.csv');
        $csvDatas = $this->csvToArray($filename);

        foreach ($csvDatas as $csvData) {
            Product::updateOrCreate([
                "name" => $csvData["Name"],
                "model" => $csvData["Model"]
            ],[
                "name" => $csvData["Name"],
                "model" => $csvData["Model"],
                "capacity" => $csvData["Capacity"],
                "price" => $csvData["Netto Price"],
                "is_demokit" => $csvData["Is Demokit"],
            ]);
        }

        $this->command->line("Completed --> Product");
    }

    private function level()
    {
        /**
         * Notes :
         * - BP = Bonus Penjualan
         * - GM = Goldmine (Bonus dari downline level 1)
         * - OR = Overriding (Bonus dari downline level 2)
         */
        $filename = Storage::path('sample/level.csv');
        $csvDatas = $this->csvToArray($filename);

        foreach ($csvDatas as $csvData) {
            Level::updateOrCreate([
                'code' => $csvData["Code"],
                'name' => $csvData["Name"],
            ], [
                'code' => $csvData["Code"],
                'name' => $csvData["Name"],
                'description' => $csvData["Description"],
                'minimum_downline' => $csvData["Minimum Downline"],
                'minimum_sold_by_downline' => $csvData["Minimum Sold by Downline"],
                'minimum_sold' => $csvData["Minimun Sold"],
                'ordering_level' => $csvData["Ordering Level"],
                'bp_percentage' => $csvData["BP"],
                'gm_percentage' => $csvData["GM"],
                'or_percentage' => $csvData["OR"],
            ]);
        }

        $this->command->line("Completed --> Level");
    }


    private function office()
    {
        $filename = Storage::path('sample/office.csv');
        $csvDatas = $this->csvToArray($filename);

        foreach ($csvDatas as $csvData) {
            Branch::updateOrCreate([
                "name" => $csvData["Name"],
            ],[
                "name" => $csvData["Name"],
                "type" => $csvData["Type"],
                "address" => $csvData["Address"],
            ]);
        }

        $this->command->line("Completed --> Branch Office");
    }


    private function member()
    {
        $filename = Storage::path('sample/member.csv');
        $csvDatas = $this->csvToArray($filename);

        $config = Configuration::where('key', 'activation_payment_amount')->first();
        ActivationPayments::truncate();
        foreach ($csvDatas as $csvData) {
            $memberMst = Member::where("id", $csvData["Upline ID"])->first();

            $member = Member::updateOrCreate([
                        "member_numb" => $csvData["Unique Number"],
                    ],[
                        "member_numb" => $csvData["Unique Number"],
                        "id_card_type" => $csvData["ID Card Type"],
                        "id_card" => $csvData["ID Card"],
                        "name" => $csvData["Name"],
                        "level_id" => $csvData["Level ID"],
                        "gender" => $csvData["Gender"],
                        "postal_code" => $csvData["Postal Code"],
                        "dob" => date("Y-m-d", strtotime($csvData["DOB"])),
                        "phone" => $csvData["Phone"],
                        "email" => $csvData["Email"],
                        "address" => $csvData["Address"],
                        "join_date" => date("Y-m-d", strtotime($csvData["Join Date"])),
                        "expired_at" => date("Y-m-d", strtotime($csvData["Expired At"])),
                        "upline_id" => (isset($memberMst)) ? $memberMst->id : null,
                        "member_type" => strtoupper($csvData["Member Type"]),
                        "branch_id" => $csvData["Office ID"],
                        "lastpayment_status" => $csvData["Last Payment Status"],
                        "npwp" => $csvData["NPWP Number"],
                    ]);
            
            // Create Customer From Member
            $customer = Customer::updateOrCreate([
                "is_member" => "1",
                "member_id" => $member->id
            ], [
                "name" => $member->name,
                "address" => $member->address,
                "city" => null,
                "phone" => $member->phone,
                "member_id" => $member->id,
                "is_member" => "1" 
            ]);

            if (strtolower($csvData["Last Payment Status"]) == "paid") {
                ActivationPayments::updateOrCreate([
                        "payment_date" => date("Y-m-d", strtotime($csvData["Join Date"])),
                        "member_id" => $member->id
                    ],[
                        "code" => "PYM-".date("ymd", strtotime($csvData["Join Date"])).'-'.str_pad($member->id, 4, '0', STR_PAD_LEFT),
                        "payment_date" => date("Y-m-d", strtotime($csvData["Join Date"])),
                        "member_id" => $member->id,
                        "total" => $config->value
                    ]);
            }
        }
        $this->command->line("Completed --> Member");

    }


    private function customer()
    {
        $filename = Storage::path('sample/customer.csv');
        $csvDatas = $this->csvToArray($filename);

        foreach ($csvDatas as $csvData) {
            Customer::updateOrCreate([
                "name" => $csvData["Name"],
            ],[
                "name" => $csvData["Name"],
                "address" => $csvData["Address"],
                "city" => $csvData["City"],
                "phone" => $csvData["HP"],
                "member_id" => $csvData["Member ID"],
                "is_member" => $csvData["Is Member"],
            ]);
        }

        $this->command->line("Completed --> Customer");
    }


    private function transaction()
    {
        $filename = Storage::path('sample/transaction.csv');
        $csvDatas = $this->csvToArray($filename);
        $transCrud = new TransactionCrudController();

        foreach ($csvDatas as $csvData) {
            $existTrans = Transaction::where("code", $csvData['Code'])->exists();
            $customer = Customer::where("id", $csvData['Customer ID'])->first();

            $requests = [
                "transaction_date" => date("Y-m-d H:i:s", strtotime($csvData['Transaction Date'])),
                "customer_id" => $csvData['Customer ID'],
                "shipping_address" => $csvData['Shipping Address'],
                "is_member" => (isset($customer)) ? $customer->is_member : 0,
                "member_id" => $csvData['Member ID'],
                "product_id" => $csvData['Product ID'],
                "discount_percentage" => $csvData['Diskon Percentage'],
                "discount_amount" => $csvData['Diskon Amount'],
                "quantity" => $csvData['Qty'],
                "created_by" => 1,
                "updated_by" => 1,
                "type" => $csvData['Tipe Penjualan'] ?? "Normal",
            ];

            if (!$existTrans) {
                $transCrud->createByImport($requests);
            }
        }

        $this->command->line("Completed --> Transaction");
    }

    private function paymentMethod(){
        PaymentMethod::updateOrCreate([
            "name" => "HCI",
        ],[
            "name" => "HCI",
            "description" => "",
        ]);       

        PaymentMethod::updateOrCreate([
            "name" => "QRIS",
        ],[
            "name" => "QRIS",
            "description" => "",
        ]);

        PaymentMethod::updateOrCreate([
            "name" => "KK Mandiri 6 Bulan",
        ],[
            "name" => "KK Mandiri 6 Bulan",
            "description" => "",
        ]);

        PaymentMethod::updateOrCreate([
            "name" => "KK BCA 6 Bulan",
        ],[
            "name" => "KK BCA 6 Bulan",
            "description" => "",
        ]);

        PaymentMethod::updateOrCreate([
            "name" => "Debit",
        ],[
            "name" => "Debit",
            "description" => "",
        ]);

        PaymentMethod::updateOrCreate([
            "name" => "Kartu Kredit",
        ],[
            "name" => "Kartu Kredit",
            "description" => "",
        ]);

        PaymentMethod::updateOrCreate([
            "name" => "Transfer",
        ],[
            "name" => "Transfer",
            "description" => "",
        ]);
    }

}