<?php

namespace Application\Api\Claim\Requests;

use Core\Http\Requests\BaseRequest;


class ConfirmationRequest extends BaseRequest
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
            'confirmation_description' => ['nullable', 'string', 'max:1000'],
            'confirmation_image' => ['required', 'string', 'max:255'],
        ];
    }
}