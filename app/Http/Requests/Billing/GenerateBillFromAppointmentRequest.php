<?php

namespace App\Http\Requests\Billing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GenerateBillFromAppointmentRequest extends FormRequest
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
        return [
            'manual_items' => ['sometimes', 'array'],
            'manual_items.*.description' => ['required_with:manual_items', 'string', 'max:255'],
            'manual_items.*.unit_price' => ['required_with:manual_items', 'numeric', 'min:0'],
            'manual_items.*.quantity' => ['sometimes', 'integer', 'min:1'],
            'manual_items.*.tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
