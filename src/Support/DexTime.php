<?php

declare(strict_types=1);

namespace Dex\Support;

use DateTimeImmutable;
use DateTimeZone;
use Throwable;

final class DexTime
{
    private const STORAGE_FORMAT = 'Y-m-d H:i:s';
    private const DISPLAY_FORMAT = 'M j, Y, H:i:s';

    private static ?DateTimeZone $utcTimezone = null;

    /**
     * @var array<string, DateTimeZone>
     */
    private static array $timezoneCache = [];

    public static function nowUtc(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', self::utcTimezone());
    }

    public static function nowUtcString(): string
    {
        return self::nowUtc()->format(self::STORAGE_FORMAT);
    }

    public static function secondsAgoUtcString(int $seconds): string
    {
        $seconds = max(0, $seconds);

        return self::fromTimestamp(time() - $seconds)->format(self::STORAGE_FORMAT);
    }

    public static function formatForDisplay(?string $utcDateTime, ?object $config = null): string
    {
        $dateTime = self::parseUtc($utcDateTime);
        if ($dateTime === null) {
            return '-';
        }

        $timezoneName = self::displayTimezoneName($config);
        $formatted = $dateTime
            ->setTimezone(self::displayTimezone($config))
            ->format(self::DISPLAY_FORMAT);

        if ($timezoneName === 'UTC') {
            return $formatted . ' UTC';
        }

        return $formatted;
    }

    public static function timeAgo(?string $utcDateTime, ?object $config = null): string
    {
        $dateTime = self::parseUtc($utcDateTime);
        if ($dateTime === null) {
            return '-';
        }

        $displayTimezone = self::displayTimezone($config);
        $targetTimestamp = $dateTime->setTimezone($displayTimezone)->getTimestamp();
        $currentTimestamp = self::nowUtc()->setTimezone($displayTimezone)->getTimestamp();
        $difference = $currentTimestamp - $targetTimestamp;

        $future = $difference < 0;
        $difference = abs($difference);

        $units = [
            'y' => 365 * 24 * 3600,
            'mo' => 30 * 24 * 3600,
            'd' => 24 * 3600,
            'h' => 3600,
            'm' => 60,
            's' => 1,
        ];

        foreach ($units as $label => $seconds) {
            if ($difference >= $seconds) {
                $value = (int) floor($difference / $seconds);

                return $future ? "in {$value}{$label}" : "{$value}{$label} ago";
            }
        }

        return $future ? 'in 0s' : '0s ago';
    }

    public static function age(?string $fromUtcDateTime, ?string $toUtcDateTime = null, ?object $config = null): string
    {
        $fromDateTime = self::parseUtc($fromUtcDateTime);
        if ($fromDateTime === null) {
            return '-';
        }

        $toDateTime = self::parseUtc($toUtcDateTime) ?? self::nowUtc();
        $displayTimezone = self::displayTimezone($config);
        $difference = abs(
            $toDateTime->setTimezone($displayTimezone)->getTimestamp()
            - $fromDateTime->setTimezone($displayTimezone)->getTimestamp()
        );

        $days = (int) floor($difference / 86400);
        $difference -= $days * 86400;
        $hours = (int) floor($difference / 3600);
        $difference -= $hours * 3600;
        $minutes = (int) floor($difference / 60);

        if ($days > 0) {
            return $days . 'd ' . $hours . 'h';
        }

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return $minutes . 'm';
    }

    public static function displayTimezoneName(?object $config = null): string
    {
        $resolvedConfig = $config ?? ConfigResolver::resolve();
        $timezoneName = trim((string) ($resolvedConfig->displayTimezone ?? 'UTC'));

        return self::isSupportedTimezone($timezoneName) ? $timezoneName : 'UTC';
    }

    public static function displayTimezone(?object $config = null): DateTimeZone
    {
        return self::timezone(self::displayTimezoneName($config));
    }

    public static function hourBucketKey(?string $utcDateTime, ?object $config = null): ?string
    {
        $dateTime = self::parseUtc($utcDateTime);
        if ($dateTime === null) {
            return null;
        }

        return $dateTime
            ->setTimezone(self::displayTimezone($config))
            ->format('Y-m-d H:00:00');
    }

    public static function last24HourBucketKeys(?object $config = null): array
    {
        $displayNow = self::nowUtc()->setTimezone(self::displayTimezone($config));
        $bucketKeys = [];

        for ($hoursAgo = 23; $hoursAgo >= 0; $hoursAgo--) {
            $bucketKeys[] = $displayNow->modify("-{$hoursAgo} hour")->format('Y-m-d H:00:00');
        }

        return $bucketKeys;
    }

    public static function parseUtc(?string $utcDateTime): ?DateTimeImmutable
    {
        $utcDateTime = trim((string) $utcDateTime);
        if ($utcDateTime === '') {
            return null;
        }

        $timezone = self::utcTimezone();
        $parsed = DateTimeImmutable::createFromFormat(self::STORAGE_FORMAT, $utcDateTime, $timezone);
        if ($parsed instanceof DateTimeImmutable) {
            return $parsed;
        }

        try {
            return (new DateTimeImmutable($utcDateTime, $timezone))->setTimezone($timezone);
        } catch (Throwable) {
            return null;
        }
    }

    public static function isSupportedTimezone(string $timezoneName): bool
    {
        $timezoneName = trim($timezoneName);
        if ($timezoneName === '') {
            return false;
        }

        try {
            new DateTimeZone($timezoneName);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private static function fromTimestamp(int $timestamp): DateTimeImmutable
    {
        return (new DateTimeImmutable('@' . $timestamp))->setTimezone(self::utcTimezone());
    }

    private static function timezone(string $timezoneName): DateTimeZone
    {
        if (! isset(self::$timezoneCache[$timezoneName])) {
            self::$timezoneCache[$timezoneName] = new DateTimeZone($timezoneName);
        }

        return self::$timezoneCache[$timezoneName];
    }

    private static function utcTimezone(): DateTimeZone
    {
        return self::$utcTimezone ??= new DateTimeZone('UTC');
    }
}
