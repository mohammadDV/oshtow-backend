<?php

namespace Application\Api\IdentityRecord\Requests;

use Core\Http\Requests\BaseRequest;

class IdentityRecordRequest extends BaseRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'national_code' => 'required|string|max:20',
            'mobile' => 'required|string|max:11|min:11',
            'birthday' => 'required|date',
            'email' => 'required|email|max:255|unique:identity_records,email',
            'country_id' => ['required', 'exists:countries,id'],
            'province_id' => ['required', 'exists:provinces,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'postal_code' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'image_national_code_front' => 'required|string|max:255',
            'image_national_code_back' => 'required|string|max:255',
            'video' => 'required|string|max:255',
        ];
    }
}