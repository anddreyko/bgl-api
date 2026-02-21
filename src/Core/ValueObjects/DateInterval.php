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
        }
        elseif (null === $value) {
            $this->value = null;
        }
        else {
            if (is_numeric($value)) {
                $value = "PT{$value}S";
            }

            try {
                $this->value = new \DateInterval($value);
            }
            catch (\Exception $e) {
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

        if ($this->value) {
            $value = date_create('@0')->add($this->value)->getTimestamp();
        }

        return $value;
    }

    public function getDays(): int
    {
        $value = 0;

        if ($this->value) {
            if (false === $this->value->days) {
                $value = (int)($this->getSeconds() / 86400);
            }
            else {
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
        $value = null;

        if ($this->value) {
            $value = 'P';
            if ($this->value->y) {
                $value .= $this->value->y . 'Y';
            }
            if ($this->value->m) {
                $value .= $this->value->m . 'M';
            }
            if ($this->value->d) {
                $value .= $this->value->d . 'D';
            }

            $time = '';
            if ($this->value->h) {
                $time .= $this->value->h . 'H';
            }
            if ($this->value->i) {
                $time .= $this->value->i . 'M';
            }
            if ($this->value->s) {
                $time .= $this->value->s . 'S';
            }
            if ($time) {
                $value .= 'T' . $time;
            }

            if ('P' === $value) {
                $value .= 'T0S';
            }
        }

        return $value;
    }
}
