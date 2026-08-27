<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('message') || ! is_string($this->input('message'))) {
            return;
        }

        $trimmed = trim($this->input('message'));
        $this->merge([
            'message' => $trimmed === '' ? null : $trimmed,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Оцени ја страната од 1 до 5.',
            'rating.integer' => 'Оцени ја страната од 1 до 5.',
            'rating.min' => 'Оцени ја страната од 1 до 5.',
            'rating.max' => 'Оцени ја страната од 1 до 5.',
            'message.max' => 'Пораката може да има најмногу 2000 знаци.',
        ];
    }
}
