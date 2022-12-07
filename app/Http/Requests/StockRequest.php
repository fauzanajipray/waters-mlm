<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockRequest extends FormRequest
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
            'branch_id' => ['required','exists:branches,id', function ($attribute, $value, $fail) {
                if($value != 1 && !$this->origin_branch_id) {
                    $fail('The origin branch field is required.');
                }
            }],
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'origin_branch_id' => ['nullable', 'exists:branches,id']
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
