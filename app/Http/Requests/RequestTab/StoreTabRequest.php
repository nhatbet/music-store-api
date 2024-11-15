<?php

namespace App\Http\Requests\RequestTab;

use Illuminate\Foundation\Http\FormRequest;

class StoreTabRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'user_id' => 'nullable|required|exists:users,id',
            'author' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }
}