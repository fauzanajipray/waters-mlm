<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'transaction_date' => ['required', function ($attribute, $value, $fail) {
                $d = date('Y-m-d', strtotime($value));
                $date = explode('-', $d);
                $month = date('m');
                $year = date('Y');
                if ($date[1] != $month) {
                    $fail('Transaction date must be in this month');
                } else if ($date[0] != $year) {
                    $fail('Transaction date must be in this year');
                }
            }],
            'member_id' => 'required|exists:members,id',
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1|in:1',
            'shipping_address' => 'required|max:255',
            'is_nsi' => 'nullable|boolean',
            'nsi' => ['nullable','numeric', function ($attribute, $value, $fail) {
                if ($this->is_nsi == "1" && $value == null) {
                    $fail('Member NSI is required');
                }
                if ($this->is_nsi == "1" && $value != null){
                    $member = \App\Models\Member::find($value);
                    if ($member->member_type != 'NSI') {
                        $fail('Member is not NSI');
                    }
                }
            }],
            // 'products' => 'required|array',
            // 'products.*.product_id' => 'required|exists:products,id',
            // 'products.*.quantity' => 'required|numeric|min:1',
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
