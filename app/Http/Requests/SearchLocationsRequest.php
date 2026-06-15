<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchLocationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search'    => ['nullable', 'string', 'min:3', 'max:100', 'regex:/^[\p{L}\p{N}\s\'\-\.]+$/u'],
            'type'      => ['nullable', 'string', 'max:100'],
            'dimension' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'search.min'   => 'Search must be at least 3 characters.',
            'search.max'   => 'Search must be no more than 100 characters.',
            'search.regex' => 'Search may only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
        ];
    }
}
