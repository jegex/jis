<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NumberCounter;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class DocumentNumberGenerator
{
    abstract protected function defaultPattern(): string;

    abstract protected function defaultPadding(): int;

    abstract protected function patternSetting(): string;

    abstract protected function paddingSetting(): string;

    abstract protected function counterKeyPrefix(): string;

    abstract protected function label(): string;

    abstract protected function numberExists(string $number): bool;

    abstract protected function highestExistingSequence(string $pattern, CarbonInterface $date): int;

    final public function generate(?CarbonInterface $date = null): string
    {
        $date ??= now();
        [$pattern, $padding] = $this->resolvedFormat();

        return DB::transaction(function () use ($pattern, $padding, $date) {
            $counter = $this->lockCounter($pattern, $date);

            $counter->value = max($counter->value, $this->highestExistingSequence($pattern, $date));

            do {
                $counter->value++;
                $number = NumberFormat::render($pattern, $padding, $date, $counter->value);
            } while ($this->numberExists($number));

            $counter->save();

            return $number;
        });
    }

    /**
     * @return list<string>
     */
    final public function preview(int $count = 3, ?string $pattern = null, ?int $padding = null, ?CarbonInterface $date = null): array
    {
        $date ??= now();
        $pattern ??= $this->pattern();
        $padding ??= $this->padding();

        if (NumberFormat::validate($pattern, $padding) !== null) {
            return [];
        }

        $last = $this->currentSequence($pattern, $date);

        return array_map(
            fn (int $sequence): string => NumberFormat::render($pattern, $padding, $date, $last + $sequence),
            range(1, $count),
        );
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function resolvedFormat(): array
    {
        $pattern = $this->pattern();
        $padding = $this->padding();

        if (NumberFormat::validate($pattern, $padding) === null) {
            return [$pattern, $padding];
        }

        report(new RuntimeException("Invalid {$this->label()} number format [{$pattern}] with padding [{$padding}]; falling back to defaults."));

        return [$this->defaultPattern(), $this->defaultPadding()];
    }

    private function pattern(): string
    {
        $pattern = setting($this->patternSetting());

        return is_string($pattern) && $pattern !== '' ? $pattern : $this->defaultPattern();
    }

    private function padding(): int
    {
        $padding = setting($this->paddingSetting());

        return is_numeric($padding) ? (int) $padding : $this->defaultPadding();
    }

    private function currentSequence(string $pattern, CarbonInterface $date): int
    {
        $value = (int) NumberCounter::query()
            ->where('key', $this->counterKey($pattern, $date))
            ->value('value');

        return max($value, $this->highestExistingSequence($pattern, $date));
    }

    private function lockCounter(string $pattern, CarbonInterface $date): NumberCounter
    {
        $key = $this->counterKey($pattern, $date);

        try {
            NumberCounter::query()->firstOrCreate(['key' => $key], ['value' => 0]);
        } catch (UniqueConstraintViolationException) {
            // Another request created the row first; the lock below picks it up.
        }

        return NumberCounter::query()
            ->where('key', $key)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function counterKey(string $pattern, CarbonInterface $date): string
    {
        return $this->counterKeyPrefix().NumberFormat::counterKey($pattern, $date);
    }
}
