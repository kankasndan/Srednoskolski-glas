<?php

namespace App\Http\Requests;

use App\Support\Username;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'username' => Username::rules($this->user()?->id),
            'is_student' => ['required', 'boolean'],
            'school' => [$isStudent ? 'required' : 'nullable', 'string', 'max:255'],
            'area' => [$isStudent ? 'required' : 'nullable', 'string', 'max:255'],
            'year' => [$isStudent ? 'required' : 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'Псевдонимот може да содржи само букви, бројки, точка, црта и долна црта.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (Username::isReserved($this->input('username'))) {
                $validator->errors()->add('username', 'Ова корисничко име е резервирано.');
            }
        });
    }
}
