<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Dummy;

use Bgl\Tests\Support\Helpers\StringHelper;
use Psr\Log\AbstractLogger;

/**
 * @method bool hasEmergencyRecords()
 * @method bool hasAlertRecords()
 * @method bool hasCriticalRecords()
 * @method bool hasErrorRecords()
 * @method bool hasWarningRecords()
 * @method bool hasNoticeRecords()
 * @method bool hasInfoRecords()
 * @method bool hasDebugRecords()
 * @method bool hasEmergencyThatContains(string $message)
 * @method bool hasAlertThatContains(string $message)
 * @method bool hasCriticalThatContains(string $message)
 * @method bool hasErrorThatContains(string $message)
 * @method bool hasWarningThatContains(string $message)
 * @method bool hasNoticeThatContains(string $message)
 * @method bool hasInfoThatContains(string $message)
 * @method bool hasDebugThatContains(string $message)
 * @method bool hasEmergencyThatMatches(string $regex)
 * @method bool hasAlertThatMatches(string $regex)
 * @method bool hasCriticalThatMatches(string $regex)
 * @method bool hasErrorThatMatches(string $regex)
 * @method bool hasWarningThatMatches(string $regex)
 * @method bool hasNoticeThatMatches(string $regex)
 * @method bool hasInfoThatMatches(string $regex)
 * @method bool hasDebugThatMatches(string $regex)
 * @method bool hasEmergencyThatPasses(callable $matcher)
 * @method bool hasAlertThatPasses(callable $matcher)
 * @method bool hasCriticalThatPasses(callable $matcher)
 * @method bool hasErrorThatPasses(callable $matcher)
 * @method bool hasWarningThatPasses(callable $matcher)
 * @method bool hasNoticeThatPasses(callable $matcher)
 * @method bool hasInfoThatPasses(callable $matcher)
 * @method bool hasDebugThatPasses(callable $matcher)
 */
final class TestLogger extends AbstractLogger
{
    public array $records = [];

    public array $recordsByLevel = [];

    /**
     * @inheritdoc
     */
    #[\Override]
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $record = [
            'level' => $level,
            'message' => StringHelper::placeholders($message, $context),
            'message_raw' => $message,
            'context' => $context,
        ];

        $this->recordsByLevel[$record['level']][] = $record;
        $this->records[] = $record;
    }

    public function hasRecords($level): bool
    {
        return isset($this->recordsByLevel[$level]);
    }

    public function hasRecord($record, $level): bool
    {
        if (is_string($record)) {
            $record = ['message' => $record];
        }

        return $this->hasRecordThatPasses(function ($rec) use ($record) {
            if ($rec['message'] !== $record['message']) {
                return false;
            }
            if (isset($record['context']) && $rec['context'] !== $record['context']) {
                return false;
            }

            return true;
        }, $level);
    }

    public function hasRecordThatPasses(callable $predicate, $level): bool
    {
        if (!isset($this->recordsByLevel[$level])) {
            return false;
        }
        foreach ($this->recordsByLevel[$level] as $i => $rec) {
            if ($predicate($rec, $i)) {
                return true;
            }
        }

        return false;
    }

    public function hasRecordThatContains($message, $level): bool
    {
        return $this->hasRecordThatPasses(
            static fn($rec) => str_contains((string)$rec['message'], (string)$message),
            $level
        );
    }

    public function hasRecordThatMatches($regex, $level): bool
    {
        return $this->hasRecordThatPasses(fn($rec) => preg_match($regex, (string)$rec['message']) > 0, $level);
    }

    public function __call($method, $args): mixed
    {
        if (preg_match(
                '/(.*)(Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency)(.*)/',
                (string)$method,
                $matches
            ) > 0) {
            $genericMethod = $matches[1] . ('Records' !== $matches[3] ? 'Record' : '') . $matches[3];
            $level = strtolower($matches[2]);
            if (method_exists($this, $genericMethod)) {
                $args[] = $level;

                return call_user_func_array([$this, $genericMethod], $args);
            }
        }
        throw new \BadMethodCallException('Call to undefined method ' . self::class . '::' . $method . '()');
    }

    public function reset(): void
    {
        $this->records = [];
        $this->recordsByLevel = [];
    }
}
