<?php

declare(strict_types=1);

namespace Bgl\Tests\Support\Modules;


use Bgl\Tests\Support\DiHelper;
use Bgl\Tests\Support\Dummy\TestLogger;
use Codeception\Module;
use Codeception\TestInterface;
use Psr\Log\LoggerInterface;

final class PsrLoggerModule extends Module
{
    private TestLogger $logger;

    #[\Override]
    public function _before(TestInterface $test): void
    {
        $this->logger = DiHelper::container()->get(TestLogger::class);
    }

    public function grabLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function haveEmergency(string|\Stringable $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function haveAlert(string|\Stringable $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function haveCritical(string|\Stringable $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function haveError(string|\Stringable $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function haveWarning(string|\Stringable $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function haveNotice(string|\Stringable $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function haveInfo(string|\Stringable $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function haveDebug(string|\Stringable $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function seeLoggerHasEmergency(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertTrue(
            $this->isRecordLogged('hasEmergency', $message, $context),
            sprintf(
                'Failed asserting that emergency "%s"%s has been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    /**
     * @param string $method
     * @param string|\Stringable $message
     * @param array|null $context
     *
     * @return bool
     */
    protected function isRecordLogged(string $method, string|\Stringable $message, ?array $context = null): bool
    {
        $record = ['message' => $message];

        if ($context !== null) {
            $record['context'] = $context;
        }

        return $this->logger->$method($record);
    }

    public function dontSeeLoggerHasEmergency(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertFalse(
            $this->isRecordLogged('hasEmergency', $message, $context),
            sprintf(
                'Failed asserting that emergency "%s"%s has not been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function seeLoggerHasAlert(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertTrue(
            $this->isRecordLogged('hasAlert', $message, $context),
            sprintf(
                'Failed asserting that alert "%s"%s has been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function dontSeeLoggerHasAlert(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertFalse(
            $this->isRecordLogged('hasAlert', $message, $context),
            sprintf(
                'Failed asserting that alert "%s"%s has not been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function seeLoggerHasCritical(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertTrue(
            $this->isRecordLogged('hasCritical', $message, $context),
            sprintf(
                'Failed asserting that critical "%s"%s has been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function dontSeeLoggerHasCritical(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertFalse(
            $this->isRecordLogged('hasCritical', $message, $context),
            sprintf(
                'Failed asserting that critical "%s"%s has not been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function seeLoggerHasError(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertTrue(
            $this->isRecordLogged('hasError', $message, $context),
            sprintf(
                'Failed asserting that error "%s"%s has been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function dontSeeLoggerHasError(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertFalse(
            $this->isRecordLogged('hasError', $message, $context),
            sprintf(
                'Failed asserting that error "%s"%s has not been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function seeLoggerHasWarning(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertTrue(
            $this->isRecordLogged('hasWarning', $message, $context),
            sprintf(
                'Failed asserting that warning "%s"%s has been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function dontSeeLoggerHasWarning(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertFalse(
            $this->isRecordLogged('hasWarning', $message, $context),
            sprintf(
                'Failed asserting that warning "%s"%s has not been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function seeLoggerHasNotice(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertTrue(
            $this->isRecordLogged('hasNotice', $message, $context),
            sprintf(
                'Failed asserting that notice "%s"%s has been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function dontSeeLoggerHasNotice(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertFalse(
            $this->isRecordLogged('hasNotice', $message, $context),
            sprintf(
                'Failed asserting that notice "%s"%s has not been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function seeLoggerHasInfo(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertTrue(
            $this->isRecordLogged('hasInfo', $message, $context),
            sprintf(
                'Failed asserting that info "%s"%s has been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function dontSeeLoggerHasInfo(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertFalse(
            $this->isRecordLogged('hasInfo', $message, $context),
            sprintf(
                'Failed asserting that info "%s"%s has not been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function seeLoggerHasDebug(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertTrue(
            $this->isRecordLogged('hasDebug', $message, $context),
            sprintf(
                'Failed asserting that debug "%s"%s has been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function dontSeeLoggerHasDebug(string|\Stringable $message, ?array $context = null): void
    {
        $this->assertFalse(
            $this->isRecordLogged('hasDebug', $message, $context),
            sprintf(
                'Failed asserting that debug "%s"%s has not been logged.',
                $message,
                $context !== null ? ' (with context)' : ''
            )
        );
    }

    public function seeLoggerHasAnyEmergency(): void
    {
        $this->assertTrue(
            $this->logger->hasEmergencyRecords(),
            'Failed asserting that any emergency was logged.'
        );
    }

    public function dontSeeLoggerHasAnyEmergency(): void
    {
        $this->assertFalse(
            $this->logger->hasEmergencyRecords(),
            'Failed asserting that no emergency was logged.'
        );
    }

    public function seeLoggerHasAnyAlert(): void
    {
        $this->assertTrue(
            $this->logger->hasAlertRecords(),
            'Failed asserting that any alert was logged.'
        );
    }

    public function dontSeeLoggerHasAnyAlert(): void
    {
        $this->assertFalse(
            $this->logger->hasAlertRecords(),
            'Failed asserting that no alert was logged.'
        );
    }

    public function seeLoggerHasAnyCritical(): void
    {
        $this->assertTrue(
            $this->logger->hasCriticalRecords(),
            'Failed asserting that any critical was logged.'
        );
    }

    public function dontSeeLoggerHasAnyCritical(): void
    {
        $this->assertFalse(
            $this->logger->hasCriticalRecords(),
            'Failed asserting that no critical was logged.'
        );
    }

    public function seeLoggerHasAnyError(): void
    {
        $this->assertTrue(
            $this->logger->hasErrorRecords(),
            'Failed asserting that any error was logged.'
        );
    }

    public function dontSeeLoggerHasAnyError(): void
    {
        $this->assertFalse(
            $this->logger->hasErrorRecords(),
            'Failed asserting that no error was logged.'
        );
    }

    public function seeLoggerHasAnyWarning(): void
    {
        $this->assertTrue(
            $this->logger->hasWarningRecords(),
            'Failed asserting that any warning was logged.'
        );
    }

    public function dontSeeLoggerHasAnyWarning(): void
    {
        $this->assertFalse(
            $this->logger->hasWarningRecords(),
            'Failed asserting that no warning was logged.'
        );
    }

    public function seeLoggerHasAnyNotice(): void
    {
        $this->assertTrue(
            $this->logger->hasNoticeRecords(),
            'Failed asserting that any notice was logged.'
        );
    }

    public function dontSeeLoggerHasAnyNotice(): void
    {
        $this->assertFalse(
            $this->logger->hasNoticeRecords(),
            'Failed asserting that no notice was logged.'
        );
    }

    public function seeLoggerHasAnyInfo(): void
    {
        $this->assertTrue(
            $this->logger->hasInfoRecords(),
            'Failed asserting that any info was logged.'
        );
    }

    public function dontSeeLoggerHasAnyInfo(): void
    {
        $this->assertFalse(
            $this->logger->hasInfoRecords(),
            'Failed asserting that no info was logged.'
        );
    }

    public function seeLoggerHasAnyDebug(): void
    {
        $this->assertTrue(
            $this->logger->hasDebugRecords(),
            'Failed asserting that any debug was logged.'
        );
    }

    public function dontSeeLoggerHasAnyDebug(): void
    {
        $this->assertFalse(
            $this->logger->hasDebugRecords(),
            'Failed asserting that no debug was logged.'
        );
    }

    public function seeLoggerHasEmergencyThatContains($message): void
    {
        $this->assertTrue(
            $this->logger->hasEmergencyThatContains($message),
            sprintf('Failed asserting that emergency containing "%s" has been logged.', $message)
        );
    }

    public function dontSeeLoggerHasEmergencyThatContains($message): void
    {
        $this->assertFalse(
            $this->logger->hasEmergencyThatContains($message),
            sprintf('Failed asserting that emergency containing "%s" has not been logged.', $message)
        );
    }

    public function seeLoggerHasAlertThatContains($message): void
    {
        $this->assertTrue(
            $this->logger->hasAlertThatContains($message),
            sprintf('Failed asserting that alert containing "%s" has been logged.', $message)
        );
    }

    public function dontSeeLoggerHasAlertThatContains($message): void
    {
        $this->assertFalse(
            $this->logger->hasAlertThatContains($message),
            sprintf('Failed asserting that alert containing "%s" has not been logged.', $message)
        );
    }

    public function seeLoggerHasCriticalThatContains($message): void
    {
        $this->assertTrue(
            $this->logger->hasCriticalThatContains($message),
            sprintf('Failed asserting that critical containing "%s" has been logged.', $message)
        );
    }

    public function dontSeeLoggerHasCriticalThatContains($message): void
    {
        $this->assertFalse(
            $this->logger->hasCriticalThatContains($message),
            sprintf('Failed asserting that critical containing "%s" has not been logged.', $message)
        );
    }

    public function seeLoggerHasErrorThatContains($message): void
    {
        $this->assertTrue(
            $this->logger->hasErrorThatContains($message),
            sprintf('Failed asserting that error containing "%s" has been logged.', $message)
        );
    }

    public function dontSeeLoggerHasErrorThatContains($message): void
    {
        $this->assertFalse(
            $this->logger->hasErrorThatContains($message),
            sprintf('Failed asserting that error containing "%s" has not been logged.', $message)
        );
    }

    public function seeLoggerHasWarningThatContains($message): void
    {
        $this->assertTrue(
            $this->logger->hasWarningThatContains($message),
            sprintf('Failed asserting that warning containing "%s" has been logged.', $message)
        );
    }

    public function dontSeeLoggerHasWarningThatContains($message): void
    {
        $this->assertFalse(
            $this->logger->hasWarningThatContains($message),
            sprintf('Failed asserting that warning containing "%s" has not been logged.', $message)
        );
    }

    public function seeLoggerHasNoticeThatContains($message): void
    {
        $this->assertTrue(
            $this->logger->hasNoticeThatContains($message),
            sprintf('Failed asserting that notice containing "%s" has been logged.', $message)
        );
    }

    public function dontSeeLoggerHasNoticeThatContains($message): void
    {
        $this->assertFalse(
            $this->logger->hasNoticeThatContains($message),
            sprintf('Failed asserting that notice containing "%s" has not been logged.', $message)
        );
    }

    public function seeLoggerHasInfoThatContains($message): void
    {
        $this->assertTrue(
            $this->logger->hasInfoThatContains($message),
            sprintf('Failed asserting that info containing "%s" has been logged.', $message)
        );
    }

    public function dontSeeLoggerHasInfoThatContains($message): void
    {
        $this->assertFalse(
            $this->logger->hasInfoThatContains($message),
            sprintf('Failed asserting that info containing "%s" has not been logged.', $message)
        );
    }

    public function seeLoggerHasDebugThatContains($message): void
    {
        $this->assertTrue(
            $this->logger->hasDebugThatContains($message),
            sprintf('Failed asserting that debug containing "%s" has been logged.', $message)
        );
    }

    public function dontSeeLoggerHasDebugThatContains($message): void
    {
        $this->assertFalse(
            $this->logger->hasDebugThatContains($message),
            sprintf('Failed asserting that debug containing "%s" has not been logged.', $message)
        );
    }

    public function seeLoggerHasEmergencyThatMatchesRegex($regex): void
    {
        $this->assertTrue(
            $this->logger->hasEmergencyThatMatches($regex),
            sprintf('Failed asserting that emergency matching regex "%s" has been logged.', $regex)
        );
    }

    public function dontSeeLoggerHasEmergencyThatMatchesRegex($regex): void
    {
        $this->assertFalse(
            $this->logger->hasEmergencyThatMatches($regex),
            sprintf('Failed asserting that emergency matching regex "%s" has not been logged.', $regex)
        );
    }

    public function seeLoggerHasAlertThatMatchesRegex($regex): void
    {
        $this->assertTrue(
            $this->logger->hasAlertThatMatches($regex),
            sprintf('Failed asserting that alert matching regex "%s" has been logged.', $regex)
        );
    }

    public function dontSeeLoggerHasAlertThatMatchesRegex($regex): void
    {
        $this->assertFalse(
            $this->logger->hasAlertThatMatches($regex),
            sprintf('Failed asserting that alert matching regex "%s" has not been logged.', $regex)
        );
    }

    public function seeLoggerHasCriticalThatMatchesRegex($regex): void
    {
        $this->assertTrue(
            $this->logger->hasCriticalThatMatches($regex),
            sprintf('Failed asserting that critical matching regex "%s" has been logged.', $regex)
        );
    }

    public function dontSeeLoggerHasCriticalThatMatchesRegex($regex): void
    {
        $this->assertFalse(
            $this->logger->hasCriticalThatMatches($regex),
            sprintf('Failed asserting that critical matching regex "%s" has not been logged.', $regex)
        );
    }

    public function seeLoggerHasErrorThatMatchesRegex($regex): void
    {
        $this->assertTrue(
            $this->logger->hasErrorThatMatches($regex),
            sprintf('Failed asserting that error matching regex "%s" has been logged.', $regex)
        );
    }

    public function dontSeeLoggerHasErrorThatMatchesRegex($regex): void
    {
        $this->assertFalse(
            $this->logger->hasErrorThatMatches($regex),
            sprintf('Failed asserting that error matching regex "%s" has not been logged.', $regex)
        );
    }

    public function seeLoggerHasWarningThatMatchesRegex($regex): void
    {
        $this->assertTrue(
            $this->logger->hasWarningThatMatches($regex),
            sprintf('Failed asserting that warning matching regex "%s" has been logged.', $regex)
        );
    }

    public function dontSeeLoggerHasWarningThatMatchesRegex($regex): void
    {
        $this->assertFalse(
            $this->logger->hasWarningThatMatches($regex),
            sprintf('Failed asserting that warning matching regex "%s" has not been logged.', $regex)
        );
    }

    public function seeLoggerHasNoticeThatMatchesRegex($regex): void
    {
        $this->assertTrue(
            $this->logger->hasNoticeThatMatches($regex),
            sprintf('Failed asserting that notice matching regex "%s" has been logged.', $regex)
        );
    }

    public function dontSeeLoggerHasNoticeThatMatchesRegex($regex): void
    {
        $this->assertFalse(
            $this->logger->hasNoticeThatMatches($regex),
            sprintf('Failed asserting that notice matching regex "%s" has not been logged.', $regex)
        );
    }

    public function seeLoggerHasInfoThatMatchesRegex($regex): void
    {
        $this->assertTrue(
            $this->logger->hasInfoThatMatches($regex),
            sprintf('Failed asserting that info matching regex "%s" has been logged.', $regex)
        );
    }

    public function dontSeeLoggerHasInfoThatMatchesRegex($regex): void
    {
        $this->assertFalse(
            $this->logger->hasInfoThatMatches($regex),
            sprintf('Failed asserting that info matching regex "%s" has not been logged.', $regex)
        );
    }

    public function seeLoggerHasDebugThatMatchesRegex(string $regex): void
    {
        $this->assertTrue(
            $this->logger->hasDebugThatMatches($regex),
            sprintf('Failed asserting that debug matching regex "%s" has been logged.', $regex)
        );
    }

    public function dontSeeLoggerHasDebugThatMatchesRegex(string $regex): void
    {
        $this->assertFalse(
            $this->logger->hasDebugThatMatches($regex),
            sprintf('Failed asserting that debug matching regex "%s" has not been logged.', $regex)
        );
    }

    public function seeLoggerHasEmergencyThatPasses(callable $matcher): void
    {
        $this->assertTrue(
            $this->logger->hasEmergencyThatPasses($matcher),
            'Failed asserting that emergency matching callable was logged.'
        );
    }

    public function dontSeeLoggerHasEmergencyThatPasses(callable $matcher): void
    {
        $this->assertFalse(
            $this->logger->hasEmergencyThatPasses($matcher),
            'Failed asserting that emergency matching callable was not logged.'
        );
    }

    public function seeLoggerHasAlertThatPasses(callable $matcher): void
    {
        $this->assertTrue(
            $this->logger->hasAlertThatPasses($matcher),
            'Failed asserting that alert matching callable was logged.'
        );
    }

    public function dontSeeLoggerHasAlertThatPasses(callable $matcher): void
    {
        $this->assertFalse(
            $this->logger->hasAlertThatPasses($matcher),
            'Failed asserting that alert matching callable was not logged.'
        );
    }

    public function seeLoggerHasCriticalThatPasses(callable $matcher): void
    {
        $this->assertTrue(
            $this->logger->hasCriticalThatPasses($matcher),
            'Failed asserting that critical matching callable was logged.'
        );
    }

    public function dontSeeLoggerHasCriticalThatPasses(callable $matcher): void
    {
        $this->assertFalse(
            $this->logger->hasCriticalThatPasses($matcher),
            'Failed asserting that critical matching callable was not logged.'
        );
    }

    public function seeLoggerHasErrorThatPasses(callable $matcher): void
    {
        $this->assertTrue(
            $this->logger->hasErrorThatPasses($matcher),
            'Failed asserting that error matching callable was logged.'
        );
    }

    public function dontSeeLoggerHasErrorThatPasses(callable $matcher): void
    {
        $this->assertFalse(
            $this->logger->hasErrorThatPasses($matcher),
            'Failed asserting that error matching callable was not logged.'
        );
    }

    public function seeLoggerHasWarningThatPasses(callable $matcher): void
    {
        $this->assertTrue(
            $this->logger->hasWarningThatPasses($matcher),
            'Failed asserting that warning matching callable was logged.'
        );
    }

    public function dontSeeLoggerHasWarningThatPasses(callable $matcher): void
    {
        $this->assertFalse(
            $this->logger->hasWarningThatPasses($matcher),
            'Failed asserting that warning matching callable was not logged.'
        );
    }

    public function seeLoggerHasNoticeThatPasses(callable $matcher): void
    {
        $this->assertTrue(
            $this->logger->hasNoticeThatPasses($matcher),
            'Failed asserting that notice matching callable was logged.'
        );
    }

    public function dontSeeLoggerHasNoticeThatPasses(callable $matcher): void
    {
        $this->assertFalse(
            $this->logger->hasNoticeThatPasses($matcher),
            'Failed asserting that notice matching callable was not logged.'
        );
    }

    public function seeLoggerHasInfoThatPasses(callable $matcher): void
    {
        $this->assertTrue(
            $this->logger->hasInfoThatPasses($matcher),
            'Failed asserting that info matching callable was logged.'
        );
    }

    public function dontSeeLoggerHasInfoThatPasses(callable $matcher): void
    {
        $this->assertFalse(
            $this->logger->hasInfoThatPasses($matcher),
            'Failed asserting that info matching callable was not logged.'
        );
    }

    public function seeLoggerHasDebugThatPasses(callable $matcher): void
    {
        $this->assertTrue(
            $this->logger->hasDebugThatPasses($matcher),
            'Failed asserting that debug matching callable was logged.'
        );
    }

    public function dontSeeLoggerHasDebugThatPasses(callable $matcher): void
    {
        $this->assertFalse(
            $this->logger->hasDebugThatPasses($matcher),
            'Failed asserting that debug matching callable was not logged.'
        );
    }
}
