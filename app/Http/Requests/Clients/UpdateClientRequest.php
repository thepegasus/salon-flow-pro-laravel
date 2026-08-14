<?php

namespace App\Http\Requests\Clients;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
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
        $client = $this->route('client');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => [
                'sometimes', 'string', 'max:30',
                Rule::unique('clients', 'phone')->where('tenant_id', $tenantId)->ignore($client),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'family_link' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
