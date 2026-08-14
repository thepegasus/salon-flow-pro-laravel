<?php

namespace App\Http\Requests\Staff;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->get()->id;
        $designation = $this->route('designation');

        return [
            'name' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('designations', 'name')->where('tenant_id', $tenantId)->ignore($designation),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
