<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InjectFilamentNotificationScripts
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only inject for HTML responses under /panel
        if (! $request->is('panel*')) {
            return $response;
        }

        if (! $response instanceof Response) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type');
        if (! str_contains($contentType ?? '', 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || ! str_contains($content, '</body>')) {
            return $response;
        }

        $manifestPath = public_path('build/manifest.json');
        if (! file_exists($manifestPath)) {
            return $response;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (! is_array($manifest)) {
            return $response;
        }

        $scripts = [];
        $keys = [
            'resources/js/filament-vendor-order-polling.js',
            'resources/js/filament-vendor-order-notification.js',
        ];

        foreach ($keys as $key) {
            if (isset($manifest[$key]['file'])) {
                $scripts[] = '<script type="module" src="' . asset('build/' . $manifest[$key]['file']) . '"></script>';
            }
        }

        if (empty($scripts)) {
            return $response;
        }

        $injection = implode("\n", $scripts) . "\n";
        $content = str_replace('</body>', $injection . '</body>', $content);
        $response->setContent($content);

        return $response;
    }
}
