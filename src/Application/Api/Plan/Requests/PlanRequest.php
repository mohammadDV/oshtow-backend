<?php

namespace Application\Api\Plan\Requests;

use Core\Http\Requests\BaseRequest;

class PlanRequest extends BaseRequest
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
            'title'          => ['required', 'string', 'max:255'],
            'priod'          => ['required', 'in:monthly,yearly'],
            'status'         => ['nullable', 'integer', 'in:0,1'],
            'amount'         => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'period_count'   => ['required', 'integer', 'min:1'],
            'claim_count'    => ['required', 'integer', 'min:0', 'max:1000'],
            'project_count'  => ['required', 'integer', 'min:0', 'max:1000'],
        ];
    }
}