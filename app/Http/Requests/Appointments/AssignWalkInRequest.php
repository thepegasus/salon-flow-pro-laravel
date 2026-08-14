<?php

namespace App\Http\Requests\Appointments;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignWalkInRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = app(TenantContext::class)->get()->id;

        return [
            'staff_profile_id' => [
                'required', 'integer',
                Rule::exists('staff_profiles', 'id')->where('tenant_id', $tenantId),
            ],
            'client_id' => [
                'required', 'integer',
                Rule::exists('clients', 'id')->where('tenant_id', $tenantId),
            ],
        ];
    }
}
