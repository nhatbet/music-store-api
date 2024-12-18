<?php

namespace App\Http\Requests\Tab;

use Illuminate\Foundation\Http\FormRequest;

class TabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'author' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'youtube_url' => 'nullable|string',
            'images' => 'required|array|max:5',
            'images.*' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:4096',
            'pdf' => 'required|mimes:pdf|max:2048'
        ];
    }
}
