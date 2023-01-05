<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MemberRequestUpdate extends FormRequest
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
            'name' => 'required|min:4|max:255',
            'member_numb' => 'required|min:5|max:255',
            'address' => 'required|min:5|max:255',
            'phone' => 'required|min:5|max:255',
            'id_card' => 'required|min:5|max:255',
            'gender' => 'required|min:1|in:M,F',
            'email' => 'required|min:5|max:255|email',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'join_date' => 'required|date',
            'dob' => 'required|date',
            'id_card_type' => 'required|in:KTP,SIM',
            'postal_code' => 'nullable|min:1|max:255',
            'npwp' => 'nullable|min:1|max:255',
            'member_type' => 'required|in:DEFAULT,STOKIST,CABANG,NIS',
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
