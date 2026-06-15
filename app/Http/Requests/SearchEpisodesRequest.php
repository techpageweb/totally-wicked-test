<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchEpisodesRequest extends FormRequest
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
            'search'  => ['nullable', 'string', 'min:3', 'max:100', 'regex:/^[\p{L}\p{N}\s\'\-\.]+$/u'],
            'episode' => ['nullable', 'string', 'min:3', 'max:10',  'regex:/^[A-Za-z0-9]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'search.min'    => 'Search must be at least 3 characters.',
            'search.max'    => 'Search must be no more than 100 characters.',
            'search.regex'  => 'Search may only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
            'episode.min'   => 'Episode code must be at least 3 characters.',
            'episode.max'   => 'Episode code must be no more than 10 characters.',
            'episode.regex' => 'Episode code may only contain letters and numbers (e.g. S01E01).',
        ];
    }
}
