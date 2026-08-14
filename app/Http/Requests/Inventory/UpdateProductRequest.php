<?php

namespace App\Http\Requests\Inventory;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'category_id' => [
                'nullable', 'integer',
                Rule::exists('inventory_categories', 'id')->where('tenant_id', $tenantId)->whereNull('deleted_at'),
            ],
            'sku' => [
                'nullable', 'string', 'max:40',
                Rule::unique('products', 'sku')->where('tenant_id', $tenantId)->whereNull('deleted_at')->ignore($this->route('product')),
            ],
            'reorder_level' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
