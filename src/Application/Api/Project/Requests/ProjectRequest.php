<?php

namespace Application\Api\Project\Requests;

use Core\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends BaseRequest
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
            'title' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'string', 'in:passenger,sender'],
            'path_type' => ['nullable', 'string', 'in:land,sea,air'],
            'amount' => ['required', 'integer', 'min:0'],
            'weight' => ['required', 'integer', 'min:0'],
            'dimensions' => ['nullable', 'string'],
            'status' => ['nullable', 'integer', 'in:0,1'],
            'vip' => ['nullable', 'integer', 'in:0,1'],
            'priority' => ['nullable', 'integer', 'max:101'],
            'send_date' => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::when(function ($input) {
                    return $input->has('receive_date');
                }, ['before_or_equal:receive_date'])
            ],
            'receive_date' => [
                Rule::when(function ($input) {
                    return $input->type === 'sender';
                }, [
                    'required',
                    'date',
                    'after_or_equal:send_date',
                    'after_or_equal:today'
                ])
            ],
            'o_country_id' => ['required', 'exists:countries,id'],
            'o_province_id' => ['required', 'exists:provinces,id'],
            'o_city_id' => ['required', 'exists:cities,id'],
            'd_country_id' => ['required', 'exists:countries,id'],
            'd_province_id' => ['required', 'exists:provinces,id'],
            'd_city_id' => ['required', 'exists:cities,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:project_categories,id'],
        ];
    }
}