<?php

declare(strict_types=1);

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        jsonResponse([
            'ok' => false,
            'message' => 'Corps JSON invalide.'
        ], 400);
    }

    return $decoded;
}

function ensureSessionStarted(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function isEmailFormat(string $value): bool
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

function generateId(string $prefix, array $existingItems): string
{
    $max = 0;
    foreach ($existingItems as $item) {
        if (!isset($item['id']) || !is_string($item['id'])) {
            continue;
        }

        if (str_starts_with($item['id'], $prefix)) {
            $number = (int) substr($item['id'], strlen($prefix));
            if ($number > $max) {
                $max = $number;
            }
        }
    }

    $next = $max + 1;
    return sprintf('%s%03d', $prefix, $next);
}

function nowIso(): string
{
    return (new DateTimeImmutable())->format(DateTimeInterface::ATOM);
}
