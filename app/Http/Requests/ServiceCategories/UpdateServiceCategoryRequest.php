<?php

namespace App\Http\Requests\ServiceCategories;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceCategoryRequest extends FormRequest
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
            'name' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('service_categories', 'name')->where('tenant_id', $tenantId)->whereNull('deleted_at')->ignore($this->route('category')),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
