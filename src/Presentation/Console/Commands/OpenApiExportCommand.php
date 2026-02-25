<?php

declare(strict_types=1);

namespace Bgl\Presentation\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'openapi:export', description: 'Export OpenAPI specification to JSON')]
final class OpenApiExportCommand extends Command
{
    private const string OUTPUT_PATH = 'web/openapi.json';

    /** @param array<string, mixed> $openapiConfig */
    public function __construct(private readonly array $openapiConfig)
    {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $spec = self::stripInternalKeys($this->openapiConfig);

        $json = json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            $output->writeln('<error>Failed to encode OpenAPI spec to JSON.</error>');

            return Command::FAILURE;
        }

        $path = self::OUTPUT_PATH;

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        file_put_contents($path, $json . "\n");

        $output->writeln(sprintf('<info>OpenAPI spec exported to %s</info>', $path));

        return Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function stripInternalKeys(array $data): array
    {
        $internal = ['x-message', 'x-interceptors', 'x-auth', 'x-map'];

        foreach ($internal as $key) {
            unset($data[$key]);
        }

        /**
         * @var string $key
         * @var mixed $value
         */
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                /** @var array<string, mixed> $value */
                $data[$key] = self::stripInternalKeys($value);
            }
        }

        return $data;
    }
}
