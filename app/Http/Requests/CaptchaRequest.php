<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class CaptchaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email:rfc',
            'password' => 'required',
            'captcha' => 'required|captcha_api:'. request('key') . ',flat'
        ];
    }

    public function messages()
    {
        return [
            'captcha.captcha_api' =>  'Incurrent!!'
        ];
    }
}
