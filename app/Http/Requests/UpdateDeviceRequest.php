<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        $deviceId = $this->route('device')->id;

        //dd($this->route('device'));
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'sim_card_id' => ['required', 'integer', 'exists:sim_cards,id', Rule::unique('devices')->ignore($deviceId)],
            'serial_number' => ['required',  Rule::unique('devices')->ignore($deviceId)],
            'imei' => ['required',  Rule::unique('devices')->ignore($deviceId)],
            'operating_system' => ['required', Rule::in(['ios', 'andriod'])],
            'type' => ['required', Rule::in(['mobile', 'tablet'])],
            'manufacturer' => ['string'],
            'model' => ['string'],
            'is_active' => ['integer', Rule::in([0, 1])]
        ];
    }
}
