<?php

declare(strict_types=1);

const DATA_DIR = __DIR__ . '/../data';

function readDataFile(string $filename): array
{
    $path = DATA_DIR . '/' . $filename;
    if (!file_exists($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function writeDataFile(string $filename, array $payload): bool
{
    $path = DATA_DIR . '/' . $filename;
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        return false;
    }

    return file_put_contents($path, $json . PHP_EOL, LOCK_EX) !== false;
}

function getFilmsData(): array
{
    $data = readDataFile('films.json');
    if (!isset($data['films']) || !is_array($data['films'])) {
        $data['films'] = [];
    }
    return $data;
}

function saveFilmsData(array $data): bool
{
    return writeDataFile('films.json', $data);
}

function getUsersData(): array
{
    $data = readDataFile('users.json');
    if (!isset($data['users']) || !is_array($data['users'])) {
        $data['users'] = [];
    }
    return $data;
}

function saveUsersData(array $data): bool
{
    return writeDataFile('users.json', $data);
}

function getCategoriesData(): array
{
    $data = readDataFile('categories.json');
    if (!isset($data['categories']) || !is_array($data['categories'])) {
        $data['categories'] = [];
    }
    return $data;
}

function getWatchHistoryData(): array
{
    $data = readDataFile('watch_history.json');
    if (!isset($data['history']) || !is_array($data['history'])) {
        $data['history'] = [];
    }
    return $data;
}

function saveWatchHistoryData(array $data): bool
{
    return writeDataFile('watch_history.json', $data);
}
