<?php

declare(strict_types=1);

const SCHEMA_DIR = __DIR__ . '/../schema';

function loadSchema(string $filename): array
{
    $path = SCHEMA_DIR . '/' . $filename;
    $raw = file_get_contents($path);
    if ($raw === false) {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function validateBySchema(array $data, string $schemaFile): array
{
    $schema = loadSchema($schemaFile);
    if ($schema === []) {
        return ['ok' => false, 'errors' => ['Schema introuvable ou invalide: ' . $schemaFile]];
    }

    $errors = [];
    validateNode($data, $schema, '$', $errors);

    return [
        'ok' => count($errors) === 0,
        'errors' => $errors
    ];
}

function validateNode(mixed $value, array $schema, string $path, array &$errors): void
{
    if (isset($schema['type'])) {
        if (!matchType($value, (string) $schema['type'])) {
            $errors[] = sprintf('%s doit etre de type %s.', $path, $schema['type']);
            return;
        }
    }

    if (isset($schema['enum']) && is_array($schema['enum'])) {
        if (!in_array($value, $schema['enum'], true)) {
            $errors[] = sprintf('%s contient une valeur non autorisee.', $path);
        }
    }

    if (is_string($value)) {
        if (isset($schema['minLength']) && mb_strlen($value) < (int) $schema['minLength']) {
            $errors[] = sprintf('%s est trop court.', $path);
        }
        if (isset($schema['maxLength']) && mb_strlen($value) > (int) $schema['maxLength']) {
            $errors[] = sprintf('%s est trop long.', $path);
        }
        if (isset($schema['pattern'])) {
            $pattern = '/' . $schema['pattern'] . '/';
            if (@preg_match($pattern, $value) !== 1) {
                $errors[] = sprintf('%s ne respecte pas le motif attendu.', $path);
            }
        }
        if (($schema['format'] ?? '') === 'email' && !isEmailFormat($value)) {
            $errors[] = sprintf('%s doit etre un email valide.', $path);
        }
        if (($schema['format'] ?? '') === 'date-time') {
            try {
                new DateTimeImmutable($value);
            } catch (Exception) {
                $errors[] = sprintf('%s doit etre une date-time ISO valide.', $path);
            }
        }
    }

    if (is_int($value) || is_float($value)) {
        if (isset($schema['minimum']) && $value < $schema['minimum']) {
            $errors[] = sprintf('%s est inferieur au minimum.', $path);
        }
        if (isset($schema['maximum']) && $value > $schema['maximum']) {
            $errors[] = sprintf('%s est superieur au maximum.', $path);
        }
    }

    if (is_array($value)) {
        $isList = array_is_list($value);

        if ($isList) {
            if (isset($schema['minItems']) && count($value) < (int) $schema['minItems']) {
                $errors[] = sprintf('%s doit contenir au moins %d elements.', $path, (int) $schema['minItems']);
            }
            if (isset($schema['items']) && is_array($schema['items'])) {
                foreach ($value as $index => $item) {
                    validateNode($item, $schema['items'], $path . '[' . $index . ']', $errors);
                }
            }
            return;
        }

        if (($schema['additionalProperties'] ?? true) === false && isset($schema['properties'])) {
            foreach ($value as $key => $_val) {
                if (!array_key_exists($key, $schema['properties'])) {
                    $errors[] = sprintf('%s.%s est une propriete non autorisee.', $path, $key);
                }
            }
        }

        if (isset($schema['required']) && is_array($schema['required'])) {
            foreach ($schema['required'] as $requiredKey) {
                if (!array_key_exists($requiredKey, $value)) {
                    $errors[] = sprintf('%s.%s est obligatoire.', $path, $requiredKey);
                }
            }
        }

        if (isset($schema['properties']) && is_array($schema['properties'])) {
            foreach ($schema['properties'] as $property => $propertySchema) {
                if (array_key_exists($property, $value) && is_array($propertySchema)) {
                    validateNode($value[$property], $propertySchema, $path . '.' . $property, $errors);
                }
            }
        }
    }
}

function matchType(mixed $value, string $type): bool
{
    return match ($type) {
        'string' => is_string($value),
        'integer' => is_int($value),
        'number' => is_int($value) || is_float($value),
        'boolean' => is_bool($value),
        'array' => is_array($value) && array_is_list($value),
        'object' => is_array($value) && !array_is_list($value),
        default => true,
    };
}
