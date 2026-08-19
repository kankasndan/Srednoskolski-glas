<?php

namespace App\Services\Moderation;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;

/**
 * Pulls a handful of JPEG stills from a video so Gemini can moderate it the
 * same way it moderates a photo, instead of ingesting the whole clip.
 */
class VideoFrameSampler
{
    /**
     * @return list<string> Raw JPEG bytes, empty when frames cannot be taken.
     */
    public function sample(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getMimeType(), 'video/')) {
            return [];
        }

        $path = $file->getRealPath();
        $ffmpeg = $this->binary();

        if (! is_string($path) || $path === '' || ! is_file($path) || $ffmpeg === null) {
            return [];
        }

        $frames = [];

        foreach ($this->timestamps($this->duration($ffmpeg, $path)) as $timestamp) {
            $jpeg = $this->grab($ffmpeg, $path, $timestamp);

            if ($jpeg !== null) {
                $frames[] = $jpeg;
            }
        }

        return $frames;
    }

    /**
     * @return list<float>
     */
    private function timestamps(?float $duration): array
    {
        $count = max(1, min(5, (int) config('moderation.drivers.gemini.video_frames', 3)));

        if ($duration === null || $duration <= 0) {
            return array_slice([0.25, 2.0, 6.0], 0, $count);
        }

        if ($duration < 1) {
            return [round(max(0.05, $duration / 2), 2)];
        }

        $start = min(0.5, $duration * 0.08);
        $end = max($start, $duration * 0.85);

        if ($count === 1) {
            return [round($start, 2)];
        }

        $stamps = [];

        for ($i = 0; $i < $count; $i++) {
            $stamps[] = round($start + ($end - $start) * ($i / ($count - 1)), 2);
        }

        return $stamps;
    }

    private function duration(string $ffmpeg, string $path): ?float
    {
        // ffmpeg -i always exits non-zero; the duration line is on stderr.
        $output = Process::timeout(15)
            ->run([$ffmpeg, '-hide_banner', '-i', $path])
            ->errorOutput();

        if (! preg_match('/Duration: (\d+):(\d+):(\d+(?:\.\d+)?)/', $output, $match)) {
            return null;
        }

        return ((int) $match[1] * 3600) + ((int) $match[2] * 60) + (float) $match[3];
    }

    private function grab(string $ffmpeg, string $path, float $timestamp): ?string
    {
        // Write to a temp file. ffmpeg's stdout pipe is not reliable on Windows
        // when captured through Symfony Process (binary JPEG gets corrupted or
        // dropped), which made every sample fail and fall back to the slow
        // Gemini Files API.
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'modframe-'.uniqid('', true).'.jpg';

        try {
            $result = Process::timeout(20)->run([
                $ffmpeg,
                '-hide_banner',
                '-loglevel', 'error',
                '-ss', (string) $timestamp,
                '-i', $path,
                '-frames:v', '1',
                '-vf', 'scale=640:-2',
                '-q:v', '5',
                '-y',
                $output,
            ]);

            if (! $result->successful() || ! is_file($output)) {
                return null;
            }

            $jpeg = file_get_contents($output);

            if (! is_string($jpeg) || strlen($jpeg) < 32 || ! str_starts_with($jpeg, "\xFF\xD8")) {
                return null;
            }

            return $jpeg;
        } finally {
            if (is_file($output)) {
                @unlink($output);
            }
        }
    }

    private function binary(): ?string
    {
        $configured = (string) config('moderation.drivers.gemini.ffmpeg_path', '');

        if ($configured !== '' && is_file($configured)) {
            return $configured;
        }

        $bundled = $this->fromNpmPackage();

        if ($bundled !== null) {
            return $bundled;
        }

        if (Process::timeout(5)->run(['ffmpeg', '-version'])->successful()) {
            return 'ffmpeg';
        }

        return null;
    }

    /**
     * The backend already has Node for Vite. ffmpeg-static ships a platform
     * binary so Windows dev machines do not need a system ffmpeg install.
     */
    private function fromNpmPackage(): ?string
    {
        foreach ([
            base_path('node_modules/ffmpeg-static/ffmpeg.exe'),
            base_path('node_modules/ffmpeg-static/ffmpeg'),
        ] as $installed) {
            if (is_file($installed)) {
                return $installed;
            }
        }

        $script = <<<'JS'
import ffmpeg from 'ffmpeg-static';
process.stdout.write(ffmpeg ?? '');
JS;

        $path = trim(Process::path(base_path())
            ->timeout(10)
            ->run(['node', '--input-type=module', '-e', $script])
            ->output());

        return $path !== '' && is_file($path) ? $path : null;
    }
}
