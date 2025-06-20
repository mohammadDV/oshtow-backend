<?php

namespace Application\Api\Claim\Requests;

use Core\Http\Requests\BaseRequest;
use Domain\Project\Models\Project;
use Illuminate\Validation\Rule;


class ClaimRequest extends BaseRequest
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
            'description' => ['required', 'string', 'max:1000'],
            'weight' => ['required', 'integer', 'min:1', 'max:1000'],
            'address_type' => ['required', 'string', 'in:me,other'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'amount' => [
                Rule::when(function ($input) {
                    if (!$input->project_id) {
                        return false;
                    }
                    $project = Project::find($input->project_id);
                    return $project && $project->type === Project::PASSENGER;
                }, [
                    'required', 'numeric', 'min:10000', 'max:9999999999.99'
                ], [
                    'nullable', 'numeric', 'min:10000', 'max:9999999999.99'
                ])
            ],
        ];
    }
}