<?php

namespace App\Services;

use Illuminate\Support\Str;

class MediaEmbedService
{
    /**
     * Hosts allowed for 360° virtual tour embeds.
     *
     * @var array<int, string>
     */
    public const VirtualTourHosts = [
        'my.matterport.com',
        'matterport.com',
        'kuula.co',
        'momento360.com',
        'app.cloudpano.com',
        'cloudpano.com',
        'roundme.com',
        'www.google.com',
        'google.com',
    ];

    /**
     * Convert a YouTube or Vimeo page link into an embeddable player URL.
     */
    public function videoEmbedUrl(?string $url): ?string
    {
        $parts = $this->parse($url);

        if ($parts === null) {
            return null;
        }

        [$host, $path, $query] = $parts;

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtube-nocookie.com'], true)) {
            $videoId = match (true) {
                $host === 'youtu.be' => trim($path, '/'),
                Str::startsWith($path, ['/embed/', '/shorts/', '/live/']) => Str::of($path)->explode('/')->get(2),
                default => $query['v'] ?? null,
            };

            return $this->isSafeIdentifier($videoId)
                ? 'https://www.youtube-nocookie.com/embed/'.$videoId
                : null;
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            $videoId = collect(explode('/', $path))->filter(fn (string $segment): bool => ctype_digit($segment))->last();

            return $videoId ? 'https://player.vimeo.com/video/'.$videoId : null;
        }

        return null;
    }

    /**
     * Return the tour URL when it points to a supported 360° provider over HTTPS.
     */
    public function virtualTourEmbedUrl(?string $url): ?string
    {
        $parts = $this->parse($url);

        if ($parts === null) {
            return null;
        }

        [$host, $path] = $parts;

        if (! in_array($host, self::VirtualTourHosts, true)) {
            return null;
        }

        if (in_array($host, ['www.google.com', 'google.com'], true) && ! Str::startsWith($path, '/maps/embed')) {
            return null;
        }

        return $url;
    }

    /**
     * @return array{0: string, 1: string, 2: array<string, mixed>}|null
     */
    private function parse(?string $url): ?array
    {
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $components = parse_url($url);

        if (($components['scheme'] ?? null) !== 'https' || empty($components['host'])) {
            return null;
        }

        parse_str($components['query'] ?? '', $query);

        return [Str::lower($components['host']), $components['path'] ?? '/', $query];
    }

    private function isSafeIdentifier(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[A-Za-z0-9_-]{6,20}$/', $value) === 1;
    }
}
