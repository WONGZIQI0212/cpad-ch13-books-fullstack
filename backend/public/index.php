<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use App\Middleware\Cors;
use App\Middleware\SecurityHeaders;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

(require __DIR__ . '/../src/routes.php')($app);

$app->add(new Cors());
$app->add(new SecurityHeaders());
$app->addRoutingMiddleware();
$app->addErrorMiddleware(
    filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
    true,
    true
);

$app->run();
