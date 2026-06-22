<?php
declare(strict_types=1);

use App\Auth\JwtService;
use App\Controllers\AuthController;
use App\Controllers\BookController;
use App\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimit;
use App\Repositories\AuditLogRepository;
use App\Repositories\BookRepository;
use App\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app): void {
    $pdo = Database::get();
    $jwt = new JwtService();
    $auth = new AuthMiddleware($jwt);
    $audit = new AuditLogRepository($pdo);
    $loginRateLimit = new RateLimit(
        (int)($_ENV['LOGIN_RATE_LIMIT'] ?? 5),
        (int)($_ENV['LOGIN_WINDOW_SECONDS'] ?? 60),
        'login'
    );

    $bookCtrl = new BookController(new BookRepository($pdo), $audit);
    $authCtrl = new AuthController(new UserRepository($pdo), $jwt, $audit);

    $app->get('/', function (Request $request, Response $response): Response {
        $response->getBody()->write(json_encode(
            [
                'name' => 'Books REST API',
                'version' => '4.0.0 (backend security)',
                'security' => [
                    'jwt' => true,
                    'security_headers' => true,
                    'rate_limit_login' => true,
                    'cors_allow_list' => true,
                    'idor_protection' => true,
                    'audit_log' => true,
                ],
            ],
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        ));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    });

    $app->post('/auth/register', [$authCtrl, 'register']);
    $app->post('/auth/login', [$authCtrl, 'login'])->add($loginRateLimit);

    $app->get('/api/books', [$bookCtrl, 'index']);
    $app->get('/api/books/{id}', [$bookCtrl, 'show']);

    $app->get('/auth/me', [$authCtrl, 'me'])->add($auth);
    $app->get('/admin/audit', function (Request $request, Response $response) use ($audit): Response {
        $auth = (array)$request->getAttribute('auth', []);

        if (($auth['role'] ?? 'member') !== 'admin') {
            $response->getBody()->write(json_encode(
                ['error' => 'Admins only'],
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            ));

            return $response
                ->withHeader('Content-Type', 'application/json; charset=utf-8')
                ->withStatus(403);
        }

        $response->getBody()->write(json_encode(
            $audit->latest(),
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        ));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    })->add($auth);

    $app->group('/api/books', function ($group) use ($bookCtrl): void {
        $group->post('', [$bookCtrl, 'create']);
        $group->put('/{id}', [$bookCtrl, 'update']);
        $group->delete('/{id}', [$bookCtrl, 'delete']);
    })->add($auth);

    $app->options('/{routes:.+}', fn(Request $request, Response $response): Response => $response);
};
