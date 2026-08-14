<?php

namespace App\Http\Requests;

use App\Support\AvatarUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProfileRequest extends FormRequest
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
        return [
            'image_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'school' => ['required_with:area,year', 'nullable', 'string', 'max:255'],
            'area' => ['required_with:school,year', 'nullable', 'string', 'max:255'],
            'year' => ['required_with:school,area', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->exists('image_url')) {
                return;
            }

            $imageUrl = $this->input('image_url');

            if ($imageUrl === null || $imageUrl === '') {
                return;
            }

            if (! AvatarUrl::isAllowed((string) $imageUrl, $this->user())) {
                $validator->errors()->add('image_url', 'Избраната слика не е валидна.');
            }
        });
    }
}
