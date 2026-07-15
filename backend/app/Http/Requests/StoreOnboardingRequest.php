<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isStudent = $this->boolean('is_student');

        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                Rule::unique('users', 'username')->ignore($this->user()?->id),
            ],
            'is_student' => ['required', 'boolean'],
            'city' => [$isStudent ? 'required' : 'nullable', 'string', 'max:255'],
            'school' => [$isStudent ? 'required' : 'nullable', 'string', 'max:255'],
            'area' => [$isStudent ? 'required' : 'nullable', 'string', 'max:255'],
            'year' => [$isStudent ? 'required' : 'nullable', 'string', 'max:255'],
        ];
    }
}
