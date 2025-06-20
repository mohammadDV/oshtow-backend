<?php

namespace Application\Api\Claim\Requests;

use Core\Http\Requests\BaseRequest;


class DeliveryConfirmationRequest extends BaseRequest
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
            'confirmation_code' => ['required', 'exists:claims,delivery_code'],
        ];
    }
}
