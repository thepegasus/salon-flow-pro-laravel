<?php

namespace App\Http\Requests\Billing;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettleQuickBillRequest extends FormRequest
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
            'codes' => ['required', 'array', 'min:1'],
            'codes.*' => ['required', 'string', 'max:20'],
            'staff_profile_ids' => ['sometimes', 'array'],
            'staff_profile_ids.*' => [
                'nullable', 'integer',
                Rule::exists('staff_profiles', 'id')->where('tenant_id', $tenantId)->whereNull('deleted_at'),
            ],
            'client_phone' => ['nullable', 'string', 'max:20'],
            'payment_method' => ['required', Rule::in(['cash', 'card', 'upi'])],
        ];
    }
}
