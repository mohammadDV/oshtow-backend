<?php

namespace Application\Api\Chat\Requests;

use Core\Http\Requests\BaseRequest;

class ChatRequest extends BaseRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:255'],
            'file' => !empty($this->get('file')) ? ['required','string'] : ['sometimes'],
        ];
    }
}