<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LevelRequest extends FormRequest
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
            'code' => 'required|unique:levels',
            'name' => 'required|unique:levels',
            'description' => 'required',
            'minimum_downline' => 'required|numeric',
            'minimum_sold_by_downline' => 'required|numeric',
            'minimum_sold' => 'required|numeric',
            'ordering_level' => 'required|numeric',
            'bp_percentage' => 'required|numeric|between:0,100',
            'bs_percentage' => 'required|numeric|between:0,100',
            'or_percentage' => 'required|numeric|between:0,100',
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
