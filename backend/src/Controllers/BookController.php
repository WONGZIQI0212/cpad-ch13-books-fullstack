<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\BookRepository;
use App\Repositories\AuditLogRepository;
use App\Validation\Validator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class BookController
{
    public function __construct(
        private BookRepository $books,
        private AuditLogRepository $audit,
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->json($response, $this->books->all());
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $book = $this->books->find((int)$args['id']);

        return $book
            ? $this->json($response, $book)
            : $this->json($response, ['error' => 'Book not found'], 404);
    }

    public function create(Request $request, Response $response): Response
    {
        $body = (array)$request->getParsedBody();
        $errors = $this->validate($body);

        if ($errors) {
            return $this->json($response, ['errors' => $errors], 400);
        }

        $auth = (array)$request->getAttribute('auth', []);
        $actorId = (int)($auth['sub'] ?? 0);
        $id = $this->books->create($body, $actorId);
        $this->audit->record('book.create', $actorId, 'books:' . $id, $request, (string)$body['title']);

        return $this->json($response, $this->books->find($id), 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];

        $book = $this->books->find($id);

        if (!$book) {
            return $this->json($response, ['error' => 'Book not found'], 404);
        }

        $auth = (array)$request->getAttribute('auth', []);
        $actorId = (int)($auth['sub'] ?? 0);
        $isOwner = (int)($book['created_by'] ?? 0) === $actorId;
        $isAdmin = ($auth['role'] ?? 'member') === 'admin';

        if (!$isOwner && !$isAdmin) {
            $this->audit->record('book.update.forbidden', $actorId, 'books:' . $id, $request);

            return $this->json($response, ['error' => 'Forbidden'], 403);
        }

        $body = array_merge($book, (array)$request->getParsedBody());
        $errors = $this->validate($body);

        if ($errors) {
            return $this->json($response, ['errors' => $errors], 400);
        }

        $this->books->update($id, $body);
        $this->audit->record('book.update', $actorId, 'books:' . $id, $request, (string)$body['title']);

        return $this->json($response, $this->books->find($id));
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $auth = (array)$request->getAttribute('auth', []);

        if (($auth['role'] ?? 'member') !== 'admin') {
            $this->audit->record('book.delete.forbidden', (int)($auth['sub'] ?? 0), 'books:' . $args['id'], $request);

            return $this->json($response, ['error' => 'Admins only'], 403);
        }

        $deleted = $this->books->delete((int)$args['id']);
        if ($deleted) {
            $this->audit->record('book.delete', (int)($auth['sub'] ?? 0), 'books:' . $args['id'], $request);
        }

        return $deleted
            ? $this->json($response, ['message' => 'Deleted'])
            : $this->json($response, ['error' => 'Book not found'], 404);
    }

    private function validate(array $data): array
    {
        return (new Validator())
            ->required('title', 'author', 'year')
            ->field('title', Validator::nonEmptyString(200), 'title must be 1-200 chars')
            ->field('author', Validator::nonEmptyString(150), 'author must be 1-150 chars')
            ->field('year', Validator::intRange(1000, (int)date('Y')), 'year must be 1000..now')
            ->field('genre', Validator::nonEmptyString(80), 'genre must be 1-80 chars')
            ->validate($data);
    }

    private function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode(
            $data,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
        ));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
}
