<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Http\Controllers\Admin\StockCrudController;
use App\Http\Controllers\Admin\TransactionCrudController;
use App\Http\Controllers\Admin\TransactionPaymentCrudController;
use App\Models\ActivationPayments;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Configuration;
use App\Models\Customer;
use App\Models\Level;
use App\Models\LevelNsi;
use App\Models\Member;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
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
        $this->user();
        $this->product();
        $this->level();
        $this->levelNSI();
        $this->branch();
        $this->member();
        $this->branchProduct();
        $this->customer();
        $this->transaction();
        $this->paymentMethod();
        $this->stocks();
        $this->transactionPayment();
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
            'email' => 'benny@rectmedia.com'
        ], [
            'email' => 'benny@rectmedia.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Benny',
        ]);
        $user->assignRole('Super Admin');

        $user = User::updateOrCreate([
            'email' => 'kevin@rectmedia.com'
        ], [
            'email' => 'kevin@rectmedia.com',
            'password' => bcrypt('qwerty'),
            'name' => 'Kevin',
        ]);
        $user->assignRole('Admin');

        $user = User::updateOrCreate([
            'email' => 'fauzan@rectmedia.id'
        ], [
            'email' => 'fauzan@rectmedia.id',
            'password' => bcrypt('qwerty'),
            'name' => 'Fauzan',
        ]);
        $user->assignRole('Member');
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
                "type" => $csvData["Type"],
                "min_stock_pusat" => $csvData["Min Stock Pusat"],
            ]);
        }

        $this->command->line("Completed --> Product");
    }

    private function branchProduct()
    {
        $branch = Branch::all();
        // create branch product
        foreach ($branch as $b) {
            foreach (Product::all() as $p) {
                BranchProduct::updateOrCreate([
                    'branch_id' => $b->id,
                    'product_id' => $p->id,
                ], [
                    'branch_id' => $b->id,
                    'product_id' => $p->id,
                    'additional_price' => 0,
                ]);
            }
        }
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

    private function levelNSI()
    {
        $filename = Storage::path('sample/level_nsi.csv');
        $csvDatas = $this->csvToArray($filename);

        foreach ($csvDatas as $csvData) {
            LevelNsi::updateOrCreate([
                'id' => $csvData["ID"],
            ], [
                'id' => $csvData["ID"],
                'min_sold' => $csvData["Min Sold"],
                'bonus_percentage' => $csvData["Percentage"],
            ]);
        }

        $this->command->line("Completed --> Level NSI");
    }


    private function branch()
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
            try {
                $memberMst = Member::where("id", $csvData["Upline ID"])->first();
                $expiredDate = ($csvData['Expired At']) ? Carbon::createFromFormat('d/m/Y', $csvData['Expired At'])->format("Y-m-d") : date("Y-m-d", strtotime($csvData['Expired At']));

                $memberType = strtoupper($csvData["Member Type"]);

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
                    "dob" => ($csvData['DOB']) ? Carbon::createFromFormat('d/m/Y', $csvData['DOB'])->format("Y-m-d") : null,
                    "phone" => $csvData["Phone"],
                    "email" => $csvData["Email"],
                    "address" => $csvData["Address"],
                    "join_date" => ($csvData['Join Date']) ? Carbon::createFromFormat('d/m/Y', $csvData['Join Date'])->format("Y-m-d") : Carbon::now(),
                    "expired_at" => $expiredDate,
                    "upline_id" => (isset($memberMst)) ? $memberMst->id : null,
                    "member_type" => $memberType,
                    "branch_id" => $csvData["Office ID"],
                    "lastpayment_status" => $csvData["Last Payment Status"] == "Paid" ? "1" : "0",
                    "npwp" => $csvData["NPWP Number"],
                ]);

                $branch = Branch::with('member')->find($csvData["Office ID"]);
                if($memberType == 'STOKIST' || $memberType == 'CABANG') {
                    if($branch->member->id != $member->id) {
                        $member->update([ "member_type" => "Personal" ]);
                    }
                }

                if (strtolower($csvData["Last Payment Status"]) == "paid") {
                    $dateNow = Carbon::now()->format("ymd");
                    $codeDate = ($csvData['Join Date']) ? Carbon::createFromFormat('d/m/Y', $csvData['Join Date'])->format("ymd") : $dateNow;
                    $code = "PYM-". $codeDate .'-'.str_pad($member->id, 4, '0', STR_PAD_LEFT);
                    ActivationPayments::updateOrCreate([
                            "code" => $code,
                            "member_id" => $member->id
                        ],[
                            "code" => $code,
                            "payment_date" => ($csvData['Join Date']) ? Carbon::createFromFormat('d/m/Y', $csvData['Join Date'])->format("Y-m-d") : Carbon::now(),
                            "member_id" => $member->id,
                            "total" => $config->value
                        ]);

                }
            } catch (Exception $e) {
                $this->command->line("Error --> Member ID : ".$csvData["ID"]);
                $this->command->line($e->getMessage());
            }
        }
        $this->command->line("Completed --> Member");
    }

    private function  customer()
    {
        $filename = Storage::path('sample/customer.csv');
        $csvDatas = $this->csvToArray($filename);

        foreach ($csvDatas as $csvData) {
            try {
                if($csvData["Member ID"]){
                    $cust = Customer::updateOrCreate([
                        "id" => $csvData["ID"],
                        "name" => $csvData["Name"],
                    ],[
                        "id" => $csvData["ID"],
                        "name" => $csvData["Name"],
                        "address" => $csvData["Address"],
                        "city" => $csvData["City"],
                        "phone" => $csvData["HP"],
                        "member_id" => $csvData["Member ID"],
                        "is_member" => $csvData["Is Member"] ?? 0,
                    ]);
                } else {
                    throw new Exception("Member ID is null");
                }
            } catch (Exception $e) {
                $this->command->line("Error --> Customer ID : ".$csvData["ID"]);
                $this->command->line($e->getMessage());
            }
        }
        $members = Member::all();
        foreach ($members as $member) {

            $customer = Customer::where("member_id", $member->id)->where("is_member", "1")->first();
            // Create Customer From Member
            if(isset($customer)) continue;
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
        }

        $this->command->line("Completed --> Customer");
    }


    private function transaction()
    {
        $filename = Storage::path('sample/transaction.csv');
        $csvDatas = $this->csvToArray($filename);
        $transCrud = new TransactionCrudController();

        foreach ($csvDatas as $csvData) {
            try {

                if($csvData["Member ID"] ) {
                    // $existTrans = Transaction::where("code", $csvData['Code'] ?? 0)->exists();
                    $customer = Customer::where("id", $csvData['Customer ID'])->first();
                    $requests = [
                        "id" => $csvData["ID"],
                        "transaction_date" => Carbon::createFromFormat('d/m/Y', $csvData['Transaction Date'])->format("Y-m-d"),
                        "customer_id" => $csvData['Customer ID'],
                        "shipping_address" => $csvData['Shipping Address'],
                        "is_member" => (isset($customer)) ? $customer->is_member : 0,
                        "member_id" => $csvData['Member ID'],
                        "product_id" => $csvData['Product ID'],
                        "discount_percentage" => $csvData['Discount Percentage'],
                        "discount_amount" => $csvData['Discount Amount'],
                        "quantity" => $csvData['Qty'],
                        "created_by" => 1,
                        "updated_by" => 1,
                        "type" => $csvData['Tipe Penjualan'] ,
                        "branch_id" => $csvData['Branch ID'],
                        "stock_from" => Branch::find($csvData['Branch ID'])->name,
                    ];

                    // if (!$existTrans) {
                        $transCrud->createByImport($requests);
                    // }
                }
            } catch (Exception $e) {
                $this->command->line("Error --> Transaction ID : ".$csvData["ID"]);
                $this->command->line($e->getMessage());
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

        PaymentMethod::updateOrCreate([
            "name" => "Tunai",
        ],[
            "name" => "Tunai",
            "description" => "",
        ]);
    }

    private function stocks(){
        $filename = Storage::path('sample/stock.csv');
        $csvDatas = $this->csvToArray($filename);
        $stockCrud = new StockCrudController();

        foreach ($csvDatas as $csvData) {
            $requests = [
                "branch_id" => $csvData['Branch ID'],
                "product_id" => $csvData['Product ID'],
                "quantity" => $csvData['Quantity'],
                "created_at" => Carbon::createFromFormat('d/m/Y', $csvData['Date'])->format("Y-m-d"),
            ];
            $stockCrud->createByImport($requests);
        }
        $this->command->line("Completed --> Stock");
    }

    private function transactionPayment()
    {
        $filename = Storage::path('sample/payment.csv');
        $csvDatas = $this->csvToArray($filename);
        $transPaymentCrud = new TransactionPaymentCrudController();
        foreach ($csvDatas as $csvData) {
            if($csvData['Nominal']){
                $requests = [
                    "id" => (int) $csvData["ID"],
                    "transaction_id" => $csvData['Transaction ID'],
                    "payment_date" => Carbon::createFromFormat('d/m/Y', $csvData['Tanggal Payment'])->format("Y-m-d"),
                    "payment_method" => $csvData['Payment Method'],
                    "amount" => $csvData['Nominal'],
                    "type" => ucwords(strtolower($csvData['Status'])),
                ];
                try {
                    $transPaymentCrud->createByImport($requests);
                } catch (Exception $e) {
                    $this->command->line("Error --> Transaction Payment, ID " . $csvData['Transaction ID']);
                    $this->command->line($e->getMessage());
                }
            }
        }

        $this->command->line("Completed --> Transaction Payments");
    }

}
