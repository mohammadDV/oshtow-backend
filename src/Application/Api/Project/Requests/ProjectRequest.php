<?php

namespace Application\Api\Project\Requests;

use Core\Http\Requests\BaseRequest;

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
            'type' => ['required', 'string', 'in:passenger,sender'],
            'path_type' => ['nullable', 'string', 'in:land,sea,air'],
            'amount' => ['required', 'integer', 'min:0'],
            'weight' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', 'integer', 'in:0,1'],
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
