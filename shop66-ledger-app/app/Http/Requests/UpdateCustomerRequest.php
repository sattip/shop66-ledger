<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer')?->id ?? $this->customer?->id ?? 'NULL';
        $storeId = $this->store_id ?? ($this->route('customer')?->store_id ?? 1);

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:customers,slug,' . $customerId . ',id,store_id,' . $storeId,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}


