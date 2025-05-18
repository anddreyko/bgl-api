<?php

declare(strict_types=1);

namespace App\Presentation\Cli;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FixturesLoadCommand extends Command
{
    /**
     * @param EntityManagerInterface $em
     * @param string[] $paths
     * @param string|null $name
     */
    public function __construct(private EntityManagerInterface $em, private array $paths, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('fixtures:load')
            ->setDescription('Loading fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Loading fixtures</info>');

        $loader = new Loader();

        foreach ($this->paths as $path) {
            $loader->loadFromDirectory($path);
        }

        $executor = new ORMExecutor($this->em, new ORMPurger());

        /** @psalm-suppress InternalMethod */
        $executor->setLogger(
            static function (string $message) use ($output) {
                $output->writeln($message);
            }
        );

        $executor->execute($loader->getFixtures());

        $output->writeln('<info>Done!</info>');

        return 0;
    }
}
