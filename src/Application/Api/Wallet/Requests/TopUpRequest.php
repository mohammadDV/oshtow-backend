<?php

namespace Application\Api\Wallet\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
            'payment_method' => ['required', 'string', 'in:credit_card,bank_transfer'],
            'payment_details' => ['required', 'array'],
            'payment_details.card_number' => ['required_if:payment_method,credit_card', 'string', 'size:16'],
            'payment_details.expiry_date' => ['required_if:payment_method,credit_card', 'string', 'regex:/^\d{2}\/\d{2}$/'],
            'payment_details.cvv' => ['required_if:payment_method,credit_card', 'string', 'size:3'],
            'payment_details.bank_account' => ['required_if:payment_method,bank_transfer', 'string'],
        ];
    }
}