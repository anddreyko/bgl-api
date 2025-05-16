<?php

declare(strict_types=1);

namespace App\Presentation\Cli;

use App\Core\ValueObjects\Token;
use App\Domain\Auth\Renders\ConfirmEmailRender;
use App\Infrastructure\Mail\Builders\MessageBuilder;
use App\Infrastructure\Mail\MailSender;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MailerCheckCommand extends Command
{
    public function __construct(private readonly MailSender $mailer, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('mailer:check')
            ->setDescription('Checking mailer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Sending...</info>');

        $this->mailer->send(
            MessageBuilder::create()
                ->from((string)env('MAIL_NOREPLY', ''))
                ->to('you@example.com'),
            new ConfirmEmailRender(Token::create(new \DateTimeImmutable()))
        );

        $output->writeln('<info>Done!</info>');

        return 0;
    }
}
