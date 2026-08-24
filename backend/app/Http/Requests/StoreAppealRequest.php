<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'explanation' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'explanation.required' => 'Напиши зошто поднесуваш жалба.',
            'explanation.min' => 'Напиши зошто поднесуваш жалба.',
            'explanation.max' => 'Жалбата може да има најмногу 2000 знаци.',
        ];
    }
}
