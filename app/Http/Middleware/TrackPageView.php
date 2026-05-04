<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    /**
     * User-agent patterns that identify bots/crawlers.
     * We filter them out so only real browser visits are counted.
     */
    private const BOT_PATTERNS = [
        'bot', 'crawler', 'spider', 'slurp', 'facebookexternalhit',
        'whatsapp', 'telegrambot', 'twitterbot', 'linkedinbot',
        'googlebot', 'bingbot', 'yandex', 'baidu', 'duckduck',
        'semrush', 'ahrefs', 'mj12bot', 'dotbot', 'rogerbot',
        'curl', 'wget', 'python-requests', 'go-http-client',
        'java/', 'libwww', 'okhttp', 'apache-httpclient',
        'uptimerobot', 'pingdom', 'datadome', 'headlesschrome',
        'phantomjs', 'selenium',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests that return HTML (2xx)
        if (
            $request->isMethod('GET') &&
            $response->isSuccessful() &&
            $this->isHtmlResponse($response) &&
            ! $this->isBot($request->userAgent())
        ) {
            $this->record($request);
        }

        return $response;
    }

    private function record(Request $request): void
    {
        $ua = (string) $request->userAgent();

        PageView::create([
            'url'          => substr($request->fullUrl(), 0, 500),
            'path'         => substr($request->path(), 0, 500),
            'referer'      => $request->header('referer') ? substr($request->header('referer'), 0, 500) : null,
            'utm_source'   => $request->query('utm_source'),
            'utm_medium'   => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'ip'           => $request->ip(),
            'user_agent'   => substr($ua, 0, 500),
            'device'       => $this->detectDevice($ua),
            'session_id'   => session()->getId(),
            'user_id'      => auth()->id(),
        ]);
    }

    private function isBot(?string $ua): bool
    {
        if (empty($ua)) {
            return true;
        }

        $ua = strtolower($ua);

        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function detectDevice(string $ua): string
    {
        $ua = strtolower($ua);

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        if (
            str_contains($ua, 'mobile') ||
            str_contains($ua, 'android') ||
            str_contains($ua, 'iphone')
        ) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html');
    }
}
