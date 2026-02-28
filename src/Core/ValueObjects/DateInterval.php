<?php

declare(strict_types=1);

namespace Bgl\Core\ValueObjects;

final class DateInterval
{
    private ?\DateInterval $value;

    /**
     * @param \DateInterval|int|string|null $value Принимает интервал PHP, ISO-8601, timestamp
     */
    public function __construct($value = null)
    {
        if ($value instanceof \DateInterval) {
            $this->value = $value;
        } elseif (null === $value) {
            $this->value = null;
        } else {
            if (is_numeric($value)) {
                $value = "PT{$value}S";
            }

            try {
                $this->value = new \DateInterval($value);
            } catch (\Exception) {
                $this->value = null;
            }
        }
    }

    public function isNull(): bool
    {
        return null === $this->value;
    }

    public function getValue(): \DateInterval
    {
        if (null === $this->value) {
            throw new \InvalidArgumentException('Incorrect date time');
        }

        return $this->value;
    }

    public function getNullableValue(): ?\DateInterval
    {
        return $this->value;
    }

    public function getSeconds(): int
    {
        $value = 0;

        if ($this->value !== null) {
            $epoch = new \DateTime('@0');
            $value = $epoch->add($this->value)->getTimestamp();
        }

        return $value;
    }

    public function getDays(): int
    {
        $value = 0;

        if ($this->value) {
            if (false === $this->value->days) {
                $value = (int)($this->getSeconds() / 86400);
            } else {
                $value = $this->value->days;
            }
        }

        return $value;
    }

    /**
     * Рассчитываем ночи, как кол-во дней минус 1
     *
     * @return int
     */
    public function getNights(): int
    {
        return max($this->getDays() - 1, 0);
    }

    public function getIso(): ?string
    {
        if ($this->value === null) {
            return null;
        }

        $date = self::joinParts([[$this->value->y, 'Y'], [$this->value->m, 'M'], [$this->value->d, 'D']]);
        $time = self::joinParts([[$this->value->h, 'H'], [$this->value->i, 'M'], [$this->value->s, 'S']]);

        $iso = 'P' . $date . ($time !== '' ? 'T' . $time : '');

        return $iso === 'P' ? 'PT0S' : $iso;
    }

    /**
     * @param list<array{int, string}> $parts
     */
    private static function joinParts(array $parts): string
    {
        $result = '';
        foreach ($parts as [$val, $suffix]) {
            if ($val) {
                $result .= $val . $suffix;
            }
        }

        return $result;
    }
}
