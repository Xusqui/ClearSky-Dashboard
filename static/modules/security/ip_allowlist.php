<?php

function clearsky_normalize_ip($ip)
{
    $ip = trim((string)$ip);

    // Normaliza IPv4 mapeada en IPv6 (::ffff:x.x.x.x)
    if (stripos($ip, '::ffff:') === 0) {
        $mapped = substr($ip, 7);
        if (filter_var($mapped, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $mapped;
        }
    }

    return $ip;
}

function clearsky_parse_cidr_list($rawCidrs)
{
    if (is_array($rawCidrs)) {
        $rawCidrs = implode(',', $rawCidrs);
    }

    $parts = preg_split('/[\s,;]+/', (string)$rawCidrs, -1, PREG_SPLIT_NO_EMPTY);
    if (!is_array($parts)) {
        return [];
    }

    $clean = [];
    foreach ($parts as $part) {
        $cidr = trim($part);
        if ($cidr !== '') {
            $clean[$cidr] = true;
        }
    }

    return array_keys($clean);
}

function clearsky_is_valid_cidr_entry($cidr)
{
    $cidr = trim((string)$cidr);
    if ($cidr === '') {
        return false;
    }

    // Entrada sin prefijo: IP exacta (IPv4 o IPv6).
    if (strpos($cidr, '/') === false) {
        return @inet_pton(clearsky_normalize_ip($cidr)) !== false;
    }

    [$subnet, $prefix] = explode('/', $cidr, 2);
    $subnet = clearsky_normalize_ip($subnet);

    if ($subnet === '' || !ctype_digit((string)$prefix)) {
        return false;
    }

    $subnetBin = @inet_pton($subnet);
    if ($subnetBin === false) {
        return false;
    }

    $prefix = (int)$prefix;
    $maxBits = strlen($subnetBin) * 8;

    return $prefix >= 0 && $prefix <= $maxBits;
}

function clearsky_ip_in_cidr($ip, $cidr)
{
    $ip = clearsky_normalize_ip($ip);
    $cidr = trim((string)$cidr);

    if ($ip === '' || $cidr === '') {
        return false;
    }

    // Si no hay prefijo, se interpreta como IP exacta.
    if (strpos($cidr, '/') === false) {
        $ipBin = @inet_pton($ip);
        $cidrBin = @inet_pton($cidr);
        if ($ipBin !== false && $cidrBin !== false && strlen($ipBin) === strlen($cidrBin)) {
            return hash_equals($cidrBin, $ipBin);
        }
        return hash_equals($cidr, $ip);
    }

    [$subnet, $prefix] = explode('/', $cidr, 2);
    $subnet = trim($subnet);

    if ($subnet === '' || !ctype_digit((string)$prefix)) {
        return false;
    }

    $ipBin = @inet_pton($ip);
    $subnetBin = @inet_pton($subnet);

    if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
        return false;
    }

    $prefix = (int)$prefix;
    $maxBits = strlen($ipBin) * 8;

    if ($prefix < 0 || $prefix > $maxBits) {
        return false;
    }

    $fullBytes = intdiv($prefix, 8);
    $remainingBits = $prefix % 8;

    if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
        return false;
    }

    if ($remainingBits === 0) {
        return true;
    }

    $mask = (0xFF << (8 - $remainingBits)) & 0xFF;

    return (ord($ipBin[$fullBytes]) & $mask) === (ord($subnetBin[$fullBytes]) & $mask);
}

function clearsky_default_allowed_cidrs($serverIp)
{
    $cidrs = ['127.0.0.1/32', '::1/128'];
    $serverIp = clearsky_normalize_ip($serverIp);

    // Si el servidor esta en una IPv4 privada/reservada, permite su /24 por defecto.
    if (filter_var($serverIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $isPublic = filter_var(
            $serverIp,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;

        if (!$isPublic) {
            $parts = explode('.', $serverIp);
            if (count($parts) === 4) {
                $cidrs[] = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0/24';
            }
        }
    }

    return array_values(array_unique($cidrs));
}

function clearsky_is_request_ip_allowed($requestIp, $configuredCidrs = '', $serverIp = '')
{
    $requestIp = clearsky_normalize_ip($requestIp);

    if ($requestIp === '' || @inet_pton($requestIp) === false) {
        return false;
    }

    $cidrs = clearsky_parse_cidr_list($configuredCidrs);

    if (empty($cidrs)) {
        $cidrs = clearsky_default_allowed_cidrs($serverIp);
    }

    foreach ($cidrs as $cidr) {
        if (clearsky_ip_in_cidr($requestIp, $cidr)) {
            return true;
        }
    }

    return false;
}
