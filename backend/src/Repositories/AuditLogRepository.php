<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;

final class AuditLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function record(
        string $action,
        ?int $actorId = null,
        ?string $target = null,
        ?Request $request = null,
        ?string $detail = null,
    ): void {
        $ip = $request
            ? (string)($request->getServerParams()['REMOTE_ADDR'] ?? null)
            : null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_log (actor_id, action, target, ip_address, detail)
             VALUES (:actor_id, :action, :target, :ip_address, :detail)'
        );
        $stmt->execute([
            ':actor_id' => $actorId,
            ':action' => $action,
            ':target' => $target,
            ':ip_address' => $ip ?: null,
            ':detail' => $detail ? mb_substr($detail, 0, 500) : null,
        ]);
    }

    public function latest(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, occurred_at, actor_id, action, target, ip_address, detail
             FROM audit_log ORDER BY id DESC LIMIT :limit'
        );
        $stmt->bindValue(':limit', max(1, min($limit, 100)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
