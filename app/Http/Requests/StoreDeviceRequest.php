<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'sim_card_id' => ['required', 'integer','unique:devices', 'exists:sim_cards,id'],
            'serial_number' => ['required', 'unique:devices'],
            'imei' => ['required', 'unique:devices'],
            'operating_system' => ['required', Rule::in(['ios', 'andriod'])],
            'type' => ['required', Rule::in(['mobile', 'tablet'])],
            'manufacturer' => ['string'],
            'model' => ['string'],
            'is_active' => ['integer']
        ];
    }
}
