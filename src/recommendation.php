<?php

declare(strict_types=1);

function computeRecommendations(array $films, array $user, array $history): array
{
    $watched = array_values(array_unique($user['watchedFilmIds'] ?? []));
    $favorites = $user['favoriteGenres'] ?? [];
    $userAge = (int) ($user['age'] ?? 0);

    $historyRatings = [];
    foreach ($history as $entry) {
        if (($entry['userId'] ?? '') === ($user['id'] ?? '')) {
            $historyRatings[$entry['filmId']] = (float) ($entry['ratingGiven'] ?? 0);
        }
    }

    $scored = [];

    foreach ($films as $film) {
        if (in_array($film['id'], $watched, true)) {
            continue;
        }

        $score = 0;

        if ($userAge >= (int) $film['ageLimit']) {
            $score += 20;
        } else {
            $score -= 30;
        }

        foreach ($film['genres'] as $genre) {
            if (in_array($genre, $favorites, true)) {
                $score += 12;
            }
        }

        $score += (float) $film['rating'] * 4;

        if (isset($historyRatings[$film['id']])) {
            $score += $historyRatings[$film['id']];
        }

        $scored[] = [
            'film' => $film,
            'score' => round($score, 2)
        ];
    }

    usort($scored, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);

    return array_map(static fn(array $item): array => $item['film'] + ['recommendationScore' => $item['score']], array_slice($scored, 0, 6));
}

function chatbotRecommend(array $films, array $user, array $answers): array
{
    $mood = strtolower(trim((string) ($answers['mood'] ?? '')));
    $desiredGenre = strtolower(trim((string) ($answers['genre'] ?? '')));
    $maxDuration = (int) ($answers['duration'] ?? 0);
    $language = strtoupper(trim((string) ($answers['language'] ?? '')));

    $moodToGenres = [
        'joyeux' => ['comedy', 'animation'],
        'triste' => ['drama'],
        'excite' => ['action', 'thriller'],
        'calme' => ['drama', 'animation']
    ];

    $priorityGenres = $moodToGenres[$mood] ?? [];
    if ($desiredGenre !== '') {
        $priorityGenres[] = $desiredGenre;
    }

    $watched = $user['watchedFilmIds'] ?? [];
    $userAge = (int) ($user['age'] ?? 0);

    $filtered = [];
    foreach ($films as $film) {
        if (in_array($film['id'], $watched, true)) {
            continue;
        }

        if ($userAge < (int) $film['ageLimit']) {
            continue;
        }

        if ($maxDuration > 0 && (int) $film['durationMin'] > $maxDuration) {
            continue;
        }

        if ($language !== '' && $language !== 'ANY' && strtoupper((string) $film['language']) !== $language) {
            continue;
        }

        if ($priorityGenres !== []) {
            $match = false;
            foreach ($film['genres'] as $g) {
                if (in_array($g, $priorityGenres, true)) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                continue;
            }
        }

        $filtered[] = $film;
    }

    usort($filtered, static fn(array $a, array $b): int => ((float) $b['rating'] <=> (float) $a['rating']));

    return array_slice($filtered, 0, 5);
}
