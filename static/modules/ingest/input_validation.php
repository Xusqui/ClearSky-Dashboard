<?php

function clearsky_payload_scalar_to_trimmed_string(array $data, string $key): ?string
{
    if (!array_key_exists($key, $data)) {
        return null;
    }

    $raw = $data[$key];
    if (is_array($raw) || is_object($raw)) {
        return null;
    }

    return trim((string)$raw);
}

function clearsky_validate_required_numeric_fields(array $data, array $requiredFields, array $ranges = []): array
{
    $errors = [];

    foreach ($requiredFields as $key => $label) {
        $value = clearsky_payload_scalar_to_trimmed_string($data, $key);

        if ($value === null || $value === '') {
            $errors[] = $label . " ({$key})";
            continue;
        }

        if (!is_numeric($value)) {
            $errors[] = $label . " ({$key})";
            continue;
        }

        if (isset($ranges[$key]) && is_array($ranges[$key])) {
            $min = $ranges[$key]['min'] ?? null;
            $max = $ranges[$key]['max'] ?? null;
            $num = (float)$value;

            if (($min !== null && $num < $min) || ($max !== null && $num > $max)) {
                $errors[] = $label . " ({$key})";
            }
        }
    }

    return array_values(array_unique($errors));
}

function clearsky_parse_payload_utc_datetime(array $data, string $key = 'dateutc'): array
{
    $value = clearsky_payload_scalar_to_trimmed_string($data, $key);

    if ($value === null || $value === '') {
        return [null, $key . ' vacío o ausente'];
    }

    try {
        $utc = new DateTime($value, new DateTimeZone('UTC'));
        return [$utc, null];
    } catch (Throwable $e) {
        return [null, $key . ' inválido'];
    }
}
