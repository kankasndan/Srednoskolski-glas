<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Comment $comment */
        $comment = $this->route('comment');

        return $this->user() !== null
            && (int) $this->user()->id === (int) $comment->user_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $hasGif = filled($this->route('comment')?->gif_url);

        return [
            'content' => $hasGif
                ? ['nullable', 'string', 'max:1000']
                : ['required', 'string', 'min:1', 'max:1000'],
        ];
    }
}
