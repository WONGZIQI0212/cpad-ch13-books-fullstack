<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class RateLimit implements MiddlewareInterface
{
    public function __construct(
        private int $limit,
        private int $window,
        private string $bucket = 'default',
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = (string)($request->getServerParams()['REMOTE_ADDR'] ?? 'unknown');
        $file = sys_get_temp_dir() . '/books-api-rate-' . preg_replace('/\W+/', '_', $this->bucket) . '.json';
        $now = time();
        $data = json_decode((string)@file_get_contents($file), true) ?: [];
        $bucket = $data[$ip] ?? ['count' => 0, 'reset' => $now + $this->window];

        if ($bucket['reset'] <= $now) {
            $bucket = ['count' => 0, 'reset' => $now + $this->window];
        }

        $bucket['count']++;
        $data[$ip] = $bucket;
        @file_put_contents($file, json_encode($data), LOCK_EX);

        if ($bucket['count'] > $this->limit) {
            $response = new Response(429);
            $response->getBody()->write(json_encode(
                ['error' => 'Too many requests'],
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            ));

            return $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withHeader('Retry-After', (string)max(1, $bucket['reset'] - $now));
        }

        return $handler->handle($request);
    }
}
