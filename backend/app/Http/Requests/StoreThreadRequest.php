<?php

namespace App\Http\Requests;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class StoreThreadRequest extends FormRequest
{
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'video/mp4',
        'video/quicktime',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /** Laravel file max rule uses kilobytes (100 MB). */
    private const MAX_FILE_KILOBYTES = 102400;

    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        $forumId = $this->input('forum_id');
        if (! is_numeric($forumId)) {
            // Let validation return a field error for missing/invalid forum_id.
            return true;
        }

        $forum = Forum::query()->find((int) $forumId);
        if ($forum === null) {
            return true;
        }

        return $user->can('create', [Thread::class, $forum]);
    }

    protected function failedAuthorization(): void
    {
        abort(403, 'Немаш дозвола да започнеш дискусија во овој форум.');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'forum_id' => ['required', 'integer', 'exists:forums,id'],
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'is_anonymous' => ['sometimes', 'boolean'],
            'link' => ['nullable', 'url', 'max:2048'],
            // Do not use mimetypes here: failed PHP uploads have an empty path and
            // Symfony FileinfoMimeTypeGuesser throws "The \"\" file does not exist...".
            'files' => ['nullable', 'array', 'max:11'],
            'files.*' => ['bail', 'file', 'max:'.self::MAX_FILE_KILOBYTES],
            'poll' => ['nullable', 'array'],
            'poll.question' => ['required_with:poll', 'string', 'min:1', 'max:255'],
            'poll.options' => ['required_with:poll', 'array', 'min:2', 'max:4'],
            'poll.options.*' => ['required', 'string', 'min:1', 'max:100'],
            // How long the poll stays open after creation (1–30 days).
            'poll.duration_days' => ['required_with:poll', 'integer', 'min:1', 'max:30'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $files = $this->file('files', []);
            if (! is_array($files)) {
                $files = $files ? [$files] : [];
            }

            $imageCount = 0;
            $videoCount = 0;
            $docCount = 0;

            foreach ($files as $index => $file) {
                if ($file === null) {
                    continue;
                }

                if (! $file->isValid()) {
                    $validator->errors()->add(
                        "files.{$index}",
                        $this->uploadFailureMessage($file)
                    );

                    continue;
                }

                $mime = (string) $file->getMimeType();

                if (! in_array($mime, self::ALLOWED_MIMES, true)) {
                    $validator->errors()->add(
                        "files.{$index}",
                        'Дозволени се слики (jpeg, png, webp, gif), видео (mp4), или документи (pdf, doc, docx).'
                    );

                    continue;
                }

                if (str_starts_with($mime, 'image/')) {
                    $imageCount++;
                } elseif (str_starts_with($mime, 'video/')) {
                    $videoCount++;
                } else {
                    $docCount++;
                }
            }

            if ($imageCount > 10) {
                $validator->errors()->add('files', 'Можеш да прикачиш најмногу 10 слики.');
            }

            if ($videoCount > 1) {
                $validator->errors()->add('files', 'Можеш да прикачиш најмногу 1 видео.');
            }

            if ($docCount > 1) {
                $validator->errors()->add('files', 'Можеш да прикачиш најмногу 1 датотека.');
            }

            // Link cannot combine with uploaded images/videos. Images + one video are allowed.
            if (filled($this->input('link')) && ($imageCount > 0 || $videoCount > 0)) {
                $validator->errors()->add('files', 'Линк не може да се комбинира со слика или видео.');
            }
            // Match UI exclusivity: document / poll are mutually exclusive.
            if ($docCount > 0 && $this->filled('poll.question')) {
                $validator->errors()->add('poll', 'Датотека и анкета не може да се комбинираат.');
            }
        });
    }

    private function uploadFailureMessage(UploadedFile $file): string
    {
        $phpLimit = ini_get('upload_max_filesize') ?: '2M';

        return match ($file->getError()) {
            \UPLOAD_ERR_INI_SIZE, \UPLOAD_ERR_FORM_SIZE => "Фајлот е преголем. Максимум на серверот е {$phpLimit} (подеси upload_max_filesize / post_max_size во php.ini).",
            \UPLOAD_ERR_PARTIAL => 'Фајлот беше само делумно прикачен. Обиди се повторно.',
            \UPLOAD_ERR_NO_FILE => 'Не е прикачен фајл.',
            \UPLOAD_ERR_NO_TMP_DIR => 'Недостига привремен фолдер за upload на серверот.',
            \UPLOAD_ERR_CANT_WRITE => 'Неуспешно запишување на фајлот на диск.',
            default => 'Неуспешен upload на фајлот. Провери ја големината и обиди се повторно.',
        };
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_anonymous')) {
            $this->merge([
                'is_anonymous' => filter_var($this->input('is_anonymous'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->filled('link')) {
            $this->merge([
                'link' => $this->normalizeEmbedLink((string) $this->input('link')),
            ]);
        }
    }

    /**
     * Users often paste full iframe embed HTML. Extract a canonical embed URL
     * so the `url` rule still passes.
     */
    private function normalizeEmbedLink(string $link): string
    {
        $text = trim($link);

        if (preg_match('/youtube(?:-nocookie)?\.com\/embed\/([\w-]{11})/', $text, $matches) === 1) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        $tiktokId = null;
        if (
            preg_match('/tiktok-embed|tiktok\.com\/(?:embed|player)/', $text) === 1
            || (preg_match('/data-video-id=/', $text) === 1 && preg_match('/tiktok\.com/', $text) === 1)
        ) {
            if (preg_match('/data-video-id="(\d+)"/', $text, $matches) === 1) {
                $tiktokId = $matches[1];
            } elseif (preg_match('/tiktok\.com\/(?:embed\/v2\/|player\/v1\/)(\d+)/', $text, $matches) === 1) {
                $tiktokId = $matches[1];
            } elseif (preg_match('/tiktok\.com\/@[\w.-]+\/video\/(\d+)/', $text, $matches) === 1) {
                $tiktokId = $matches[1];
            }
        }

        if ($tiktokId !== null) {
            return 'https://www.tiktok.com/player/v1/'.$tiktokId;
        }

        return $text;
    }
}
