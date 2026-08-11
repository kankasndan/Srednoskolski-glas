<?php

namespace App\Http\Requests;

use App\Models\Comment;
use App\Models\Thread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        /** @var Thread|null $thread */
        $thread = $this->route('thread');
        if (! $thread instanceof Thread) {
            return false;
        }

        return $user->can('create', [Comment::class, $thread]);
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'Немаш дозвола да коментираш. Заврши го onboarding процесот.');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $parentId = $this->integer('parent_id') ?: null;

            if ($parentId === null) {
                return;
            }

            /** @var Thread $thread */
            $thread = $this->route('thread');
            $parent = Comment::query()->find($parentId);

            if ($parent === null || $parent->thread_id !== $thread->id) {
                $validator->errors()->add(
                    'parent_id',
                    'Родителскиот коментар мора да припаѓа на истата дискусија.',
                );
            }
        });
    }
}
