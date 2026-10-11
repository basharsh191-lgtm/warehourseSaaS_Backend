<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $phoneRule = Rule::unique('parties', 'phone')
            ->where(fn ($query) => $query
                ->where('company_id', $this->user()->company_id)
                ->where('type', $this->input('type')));

        if ($partyId = $this->route('party')) {
            $phoneRule->ignore($partyId);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50', $phoneRule],
            'type' => ['required', Rule::in(['customer', 'supplier'])],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'رقم الهاتف هذا مستخدم بالفعل لطرف آخر',
        ];
    }
}
