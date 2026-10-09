<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonInterface;

final class NumberFormat
{
    public const DEFAULT_PADDING = 4;

    public const MAX_LENGTH = 50;

    public const MIN_PADDING = 1;

    public const MAX_PADDING = 12;

    private const DATE_TOKENS = ['{YYYY}', '{YY}', '{MM}', '{DD}'];

    private const SEQUENCE_TOKENS = ['{SEQ}', '{SEQ:M}', '{SEQ:Y}'];

    public static function validate(string $pattern, int $padding = self::DEFAULT_PADDING): ?string
    {
        if ($padding < self::MIN_PADDING || $padding > self::MAX_PADDING) {
            return 'Sequence digits must be between '.self::MIN_PADDING.' and '.self::MAX_PADDING.'.';
        }

        $tokens = self::tokens($pattern);

        $outsideTokens = preg_replace('/\{[^{}]+\}/', '', $pattern) ?? '';

        if (str_contains($outsideTokens, '{') || str_contains($outsideTokens, '}')) {
            return 'The pattern contains unbalanced braces.';
        }

        foreach ($tokens as $token) {
            if (! in_array($token, self::DATE_TOKENS, true) && ! in_array($token, self::SEQUENCE_TOKENS, true)) {
                return "Unknown token: {$token}";
            }
        }

        $sequenceTokens = array_values(array_intersect($tokens, self::SEQUENCE_TOKENS));

        if (count($sequenceTokens) !== 1) {
            return 'Pattern must contain exactly one of: '.implode(', ', self::SEQUENCE_TOKENS).'.';
        }

        $longest = self::render($pattern, $padding, now(), 10 ** $padding - 1);

        if (mb_strlen($longest) > self::MAX_LENGTH) {
            return 'The resulting number may not exceed '.self::MAX_LENGTH.' characters.';
        }

        return null;
    }

    public static function render(string $pattern, int $padding, CarbonInterface $date, int $sequence): string
    {
        return strtr($pattern, [
            '{YYYY}' => $date->format('Y'),
            '{YY}' => $date->format('y'),
            '{MM}' => $date->format('m'),
            '{DD}' => $date->format('d'),
            '{SEQ:M}' => mb_str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT),
            '{SEQ:Y}' => mb_str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT),
            '{SEQ}' => mb_str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT),
        ]);
    }

    public static function counterKey(string $pattern, CarbonInterface $date): string
    {
        $tokens = self::tokens($pattern);

        if (in_array('{SEQ:M}', $tokens, true)) {
            return 'seq:M:'.$date->format('Y-m');
        }

        if (in_array('{SEQ:Y}', $tokens, true)) {
            return 'seq:Y:'.$date->format('Y');
        }

        return 'seq';
    }

    public static function matchingRegex(string $pattern, CarbonInterface $date): ?string
    {
        $regex = preg_quote($pattern, '~');

        $replacements = [
            '{YYYY}' => $date->format('Y'),
            '{YY}' => $date->format('y'),
            '{MM}' => $date->format('m'),
            '{DD}' => $date->format('d'),
            '{SEQ:M}' => '(\d+)',
            '{SEQ:Y}' => '(\d+)',
            '{SEQ}' => '(\d+)',
        ];

        $hasSequence = false;

        foreach ($replacements as $token => $value) {
            $quoted = preg_quote($token, '~');

            if (str_contains($regex, $quoted)) {
                if (str_starts_with($token, '{SEQ')) {
                    $hasSequence = true;
                }

                $regex = str_replace($quoted, $value, $regex);
            }
        }

        return $hasSequence ? '~^'.$regex.'$~' : null;
    }

    /**
     * @return list<string>
     */
    private static function tokens(string $pattern): array
    {
        preg_match_all('/\{[^{}]+\}/', $pattern, $matches);

        return $matches[0];
    }
}
