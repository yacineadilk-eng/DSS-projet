<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/storage.php';

function currentUser(): ?array
{
    ensureSessionStarted();
    if (!isset($_SESSION['userId'])) {
        return null;
    }

    $usersData = getUsersData();
    foreach ($usersData['users'] as $user) {
        if ($user['id'] === $_SESSION['userId']) {
            return $user;
        }
    }

    return null;
}

function requireLogin(): array
{
    $user = currentUser();
    if ($user === null) {
        jsonResponse(['ok' => false, 'message' => 'Authentification requise.'], 401);
    }

    return $user;
}

function requireAdmin(): array
{
    $user = requireLogin();
    if (($user['role'] ?? '') !== 'admin') {
        jsonResponse(['ok' => false, 'message' => 'Acces admin requis.'], 403);
    }

    return $user;
}
