<?php

namespace App\Http\Requests;

use App\Models\Thread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class UpdateThreadRequest extends FormRequest
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
        /** @var Thread $thread */
        $thread = $this->route('thread');

        return $this->user() !== null
            && (int) $this->user()->id === (int) $thread->user_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'link' => ['nullable', 'url', 'max:2048'],
            'files' => ['nullable', 'array', 'max:11'],
            'files.*' => ['bail', 'file', 'max:'.self::MAX_FILE_KILOBYTES],
            'remove_attachment_ids' => ['nullable', 'array'],
            'remove_attachment_ids.*' => ['integer', 'distinct'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Thread $thread */
            $thread = $this->route('thread');
            $removeIds = collect($this->input('remove_attachment_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $ownedIds = $thread->threadAttachment()->pluck('id');
            $invalidIds = $removeIds->diff($ownedIds);

            if ($invalidIds->isNotEmpty()) {
                $validator->errors()->add(
                    'remove_attachment_ids',
                    'Можеш да отстраниш само прилози од оваа дискусија.',
                );

                return;
            }

            $remaining = $thread->threadAttachment
                ->whereNotIn('id', $removeIds->all())
                ->values();

            $imageCount = $remaining->where('slug', 'image')->count();
            $videoCount = $remaining->where('slug', 'video')->count();
            $docCount = $remaining->where('slug', 'file')->count();
            $linkCount = $remaining->where('slug', 'link')->count();
            $hasPoll = $thread->poll()->exists();

            $files = $this->file('files', []);
            if (! is_array($files)) {
                $files = $files ? [$files] : [];
            }

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

            $addingLink = filled($this->input('link'));

            if ($addingLink) {
                $linkCount++;
            }

            if ($linkCount > 1) {
                $validator->errors()->add('link', 'Можеш да имаш најмногу 1 линк. Прво отстрани го постоечкиот.');
            }

            if (($addingLink || $remaining->where('slug', 'link')->isNotEmpty()) && ($imageCount > 0 || $videoCount > 0)) {
                $validator->errors()->add('files', 'Линк не може да се комбинира со слика или видео.');
            }

            if ($docCount > 0 && $hasPoll) {
                $validator->errors()->add('files', 'Датотека и анкета не може да се комбинираат.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $removeIds = $this->input('remove_attachment_ids', []);

        if (! is_array($removeIds)) {
            $removeIds = $removeIds !== null && $removeIds !== '' ? [$removeIds] : [];
        }

        $this->merge([
            'remove_attachment_ids' => array_values(array_filter(
                array_map(static fn ($id) => is_numeric($id) ? (int) $id : null, $removeIds),
                static fn ($id) => $id !== null,
            )),
        ]);

        if ($this->filled('link')) {
            $this->merge([
                'link' => $this->normalizeEmbedLink((string) $this->input('link')),
            ]);
        }
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
