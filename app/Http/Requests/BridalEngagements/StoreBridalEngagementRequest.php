<?php

namespace App\Http\Requests\BridalEngagements;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBridalEngagementRequest extends FormRequest
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
            'client_id' => [
                'required', 'integer',
                Rule::exists('clients', 'id')->where('tenant_id', $tenantId),
            ],
            'event_date' => ['required', 'date'],
            'venue' => ['nullable', 'string', 'max:255'],
            'event_is_on_location' => ['sometimes', 'boolean'],

            'trial_staff_profile_id' => [
                'required', 'integer',
                Rule::exists('staff_profiles', 'id')->where('tenant_id', $tenantId),
            ],
            'trial_start_at' => ['required', 'date'],
            'trial_services' => ['required', 'array', 'min:1'],
            'trial_services.*.service_id' => [
                'required', 'integer',
                Rule::exists('services', 'id')->where('tenant_id', $tenantId),
            ],

            'event_staff_profile_id' => [
                'required', 'integer',
                Rule::exists('staff_profiles', 'id')->where('tenant_id', $tenantId),
            ],
            'event_start_at' => ['required', 'date'],
            'event_services' => ['required', 'array', 'min:1'],
            'event_services.*.service_id' => [
                'required', 'integer',
                Rule::exists('services', 'id')->where('tenant_id', $tenantId),
            ],

            'traveling_staff_profile_ids' => ['sometimes', 'array'],
            'traveling_staff_profile_ids.*' => [
                'integer',
                Rule::exists('staff_profiles', 'id')->where('tenant_id', $tenantId),
            ],
        ];
    }
}
