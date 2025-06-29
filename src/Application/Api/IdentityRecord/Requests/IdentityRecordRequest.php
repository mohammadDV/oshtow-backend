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
            'fullname' => 'required|string|max:255',
            'national_code' => 'required|string|max:20|unique:identity_records,national_code',
            'mobile' => 'required|string|max:11|min:11|unique:identity_records,email',
            'birthday' => 'required|date',
            'amountemail' => 'required|email|max:255|unique:identity_records,email',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'image_national_code_front' => 'required|string|max:255',
            'image_national_code_back' => 'required|string|max:255',
            'video' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
        ];
    }
}