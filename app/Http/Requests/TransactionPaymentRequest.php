<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class TransactionPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'transaction_id' => 'required|exists:transactions,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_account_number' => 'nullable',
            'photo_url' => 'nullable',
            'type' => 'required|in:Full,Partial',
            'amount' => function ($attribute, $value, $fail) {
                $typePayment = $this->input('type');
                $transaction = Transaction::with(['transactionProducts', 'transactionPayments'])->find($this->input('transaction_id'));
                if ($typePayment = 'Partial') {
                    $totalBill = $transaction->transactionProducts->sum(function($item){
                        return $item->price * $item->quantity;
                    }) - $transaction->transactionPayments->sum('amount');
                    if ($totalBill < $value) {
                        $fail('The amount must be less than the total bill');
                    }
                } else {
                    $total = $transaction->transactionProducts->sum(function ($item) {
                        return $item->price * $item->quantity;
                    });
                    if ($total != $value) {
                        $fail('The amount must be equal to the total transaction');
                    }
                }
            },
            'payment_date' => 'required|date',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
