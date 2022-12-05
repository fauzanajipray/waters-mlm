<?php
namespace App\Http\Traits;

use App\Models\BonusHistory;
use App\Models\Customer;
use App\Models\Level;
use App\Models\LevelUpHistories;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;
use Nasution\Terbilang;

trait TransactionTrait {
    protected function generateCode() 
    {
        $lastTransaction = Transaction::withTrashed()->orderBy('id', 'desc')->first();
        $lastTransactionCode = $lastTransaction->code ?? 'TRX-000000-0000';
        $transactionCode = explode('-', $lastTransactionCode)[2] + 1;
        $transactionCode = 'INV-' . date('ymd') . '-' . str_pad($transactionCode, 4, '0', STR_PAD_LEFT);
        return $transactionCode;
    }


    public function downloadLetterRoad($id){
        $transaction = Transaction::with(['transactionProducts', 'member', 'customer'])->find($id);
        if (!$transaction) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
        $transaction->letter_road_code = str_replace('INV', 'SJ', $transaction->code);
        $pdf = Pdf::loadView('exports.pdf.print-letter-road', [
            'data' => $transaction,
        ]);
        $customPaper = array(0,0,612.00,396.80);
        $pdf->set_paper($customPaper);
        return $pdf->stream('Surat Jalan '.$transaction->code.'.pdf');
        // return view('exports.pdf.print-letter-road', [
        //     'data' => $transaction,
        // ]);

        // return $pdf->download('Surat Jalan '.$transaction->code.'.pdf');
    }

    public function downloadInvoice($id){
        $transaction = Transaction::with(['transactionProducts', 'customer', 'member', 'transactionPayments'])
            ->join('transaction_payments', 'transaction_payments.transaction_id', '=', 'transactions.id')
            ->join('transaction_products', 'transaction_products.transaction_id', '=', 'transactions.id')
            ->select('transactions.*', 'transaction_payments.type as payment_type',
                DB::raw('SUM(transaction_payments.amount) as total_paid'),
            )
            ->find($id);
        if (!$transaction) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
        $convert = new Terbilang();
        $terbilang = $convert->convert($transaction->total_price) . ' rupiah';
        $transaction->terbilang = ucwords($terbilang);
        if($transaction->total_price == $transaction->total_paid){
            $transaction->shipping_notes = 'Lunas, Pembayaran dilakukan dengan metode '. $transaction->payment_type . ', '.$transaction->shipping_notes;
        }else{
            $text = ($transaction->payment_type) ? 'Pembayaran dilakukan dengan metode '. $transaction->payment_type . ', ' : '';
            $transaction->shipping_notes = 'Belum Lunas, ' . $text . $transaction->shipping_notes;
        }
        $pdf = Pdf::loadView('exports.pdf.print-letter-invoice', [
            'transaction' => $transaction,
        ]);
        $customPaper = array(0,0,612.00,396.80); // 8.5 x 5.5 inch
        $pdf->set_paper($customPaper);
        return $pdf->stream('Invoice '.$transaction->code.'.pdf');
        // return view('exports.pdf.print-letter-invoice', [
        //     'transaction' => $transaction,
        // ]);
        // return $pdf->download('Surat Jalan '.$transaction->code.'.pdf');
    }

    public function checkCustomer(Request $request)
    {
        $customer = Customer::
            where('member_id', $request->member_id)
            ->where('id', $request->customer_id)
            ->first();
        if ($customer) {
            return response()->json([
                'status' => true,
                'data' => $customer,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found',
            ]);
        }
    }
}
