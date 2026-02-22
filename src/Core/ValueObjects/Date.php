<?php

declare(strict_types=1);

namespace Bgl\Core\ValueObjects;

final class Date
{
    private readonly ?\DateTimeInterface $date;

    /**
     * Дата
     *
     * @param \DateTimeInterface|string|int $date Значения даты без времени в любом типе данных:
     *      timestamp, объект даты, строка с представлением даты
     * @param string|null $format если известен формат строки данных, то можно передать этот формат
     * @param \DateTimeZone|null $zone Часовой пояс даты
     *
     * @throws \Exception
     */
    public function __construct($date = null, ?string $format = null, ?\DateTimeZone $zone = null)
    {
        /** @var \DateTimeInterface|null $res */
        $res = $date;

        switch (true) {
            case is_int($date):
                $res = new \DateTimeImmutable()->setTimestamp($date);
                break;

            case is_string($date) && $format !== null:
                $res = \DateTimeImmutable::createFromFormat($format, $date) ?: null;
                break;

            case is_string($date):
                $res = new \DateTimeImmutable($date);
                break;
        }

        if (
            $zone instanceof \DateTimeZone
            && $res instanceof \DateTimeInterface
            && method_exists($res, 'setTimezone')
        ) {
            /** @var \DateTimeInterface $res */
            $res = $res->setTimezone($zone);
        }

        if ($res instanceof \DateTimeInterface) {
            if (method_exists($res, 'setTime')) {
                $res->setTime(0, 0);
            }
        } else {
            $res = null;
        }

        $this->date = $res ?: null;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getValue(): \DateTimeInterface
    {
        if (null === $this->date) {
            throw new \InvalidArgumentException('Incorrect date time');
        }

        return $this->date;
    }

    /**
     * @return null|\DateTimeInterface
     */
    public function getNullableValue(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function getFormattedValue(string $format): string
    {
        if (null === $this->date) {
            throw new \InvalidArgumentException('Incorrect date time');
        }

        return $this->date->format($format);
    }

    /**
     * @param string $format
     *
     * @return null|string
     */
    public function getNullableFormattedValue(string $format): ?string
    {
        if ($this->date instanceof \DateTimeInterface) {
            return $this->date->format($format);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->date === null;
    }
}
