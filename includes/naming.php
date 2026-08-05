<?php
/**
 * Общая функция именования по конвенции «Дата - Спикер - Тема (до двоеточия)» —
 * используется и для папки документов вебинара, и для имени ZIP-архива, и
 * везде, где в будущем понадобится такое же человекочитаемое имя.
 */

declare(strict_types=1);

/**
 * "Дата - Спикер - Тема (до первого двоеточия)", без кавычек/лишних пробелов.
 * НЕ содержит символов, недопустимых в путях — безопасно для имени файла/папки.
 */
function webinar_slug_name(array $webinar): string {
    $date = (string)($webinar['date'] ?? '');
    $speaker = trim((string)($webinar['speaker'] ?? ''));
    $title = trim((string)($webinar['title'] ?? ''), " \t\n\r\0\x0B«»");
    $colonPos = mb_strpos($title, ':');
    if ($colonPos !== false) $title = mb_substr($title, 0, $colonPos);
    $title = trim($title);

    $raw = trim("{$date} - {$speaker} - {$title}", ' -');
    if ($raw === '') $raw = 'webinar';

    $safe = preg_replace('/[\/\\\\:\*\?"<>\|]+/u', '_', $raw);
    $safe = preg_replace('/\s+/u', ' ', $safe ?? $raw);
    $safe = trim((string)$safe);
    if (mb_strlen($safe) > 150) $safe = trim(mb_substr($safe, 0, 150));
    return $safe;
}

/** Безопасное имя файла — только basename, без выхода за пределы папки. */
function safe_file_name(string $name, string $fallback = 'file'): string {
    $name = basename(str_replace('\\', '/', $name));
    $name = preg_replace('/[\/\\\\:\*\?"<>\|]+/u', '_', $name) ?? $name;
    $name = trim($name);
    if ($name === '' || $name === '.' || $name === '..') $name = $fallback;
    return $name;
}
