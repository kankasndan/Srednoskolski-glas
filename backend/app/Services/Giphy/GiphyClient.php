<?php

namespace App\Services\Giphy;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class GiphyClient
{
    /**
     * Trending GIFs when `$query` is empty, search results otherwise.
     *
     * @return list<array{id: string, url: string, title: string}>
     */
    public function search(string $query = ''): array
    {
        $apiKey = (string) config('services.giphy.key');

        if ($apiKey === '') {
            throw new RuntimeException('Missing Giphy configuration value [key]. Set GIPHY_API_KEY in your .env file.');
        }

        $base = rtrim((string) config('services.giphy.base_url', 'https://api.giphy.com/v1'), '/');
        $query = trim($query);
        $endpoint = $query === '' ? 'gifs/trending' : 'gifs/search';
        $timeout = max(3, (int) config('services.giphy.timeout', 5));

        $queryParams = [
            'api_key' => $apiKey,
            'limit' => max(1, (int) config('services.giphy.limit', 24)),
            'rating' => (string) config('services.giphy.rating', 'g'),
        ];

        if ($query !== '') {
            $queryParams['q'] = $query;
        }

        try {
            $response = Http::connectTimeout(3)
                ->timeout($timeout)
                ->retry(2, 250, function (Throwable $exception): bool {
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    return $exception instanceof RequestException
                        && $exception->response->serverError();
                }, throw: false)
                ->get("{$base}/{$endpoint}", $queryParams);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Giphy request failed (connection).', previous: $exception);
        }

        if ($response->failed()) {
            throw new RuntimeException('Giphy request failed ('.$response->status().').');
        }

        return $this->mapGifs((array) $response->json('data', []));
    }

    /**
     * @param  list<mixed>  $items
     * @return list<array{id: string, url: string, title: string}>
     */
    private function mapGifs(array $items): array
    {
        $gifs = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = $item['id'] ?? null;
            $url = data_get($item, 'images.fixed_width.url');
            $title = $item['title'] ?? '';

            if (! is_string($id) || $id === '' || ! is_string($url) || $url === '') {
                continue;
            }

            $gifs[] = [
                'id' => $id,
                'url' => $url,
                'title' => is_string($title) ? $title : '',
            ];
        }

        return $gifs;
    }
}
