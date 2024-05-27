<?php

namespace my127\Workspace;

use my127\Console\Application\Application as ConsoleApplication;
use my127\Console\Application\Executor;
use my127\Workspace\Environment\Environment;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Application extends ConsoleApplication
{
    /** @var Environment */
    private $environment;

    public function __construct(Environment $environment, Executor $executor, EventDispatcher $dispatcher)
    {
        parent::__construct($executor, $dispatcher, 'ws', '', self::getVersion());
        $this->environment = $environment;
    }

    public function run(?array $argv = null): int
    {
        $this->option('-v, --verbose    Increase verbosity');
        $this->environment->build();

        return parent::run($argv);
    }

    public static function getVersion(): string
    {
        $version = trim(@file_get_contents(__DIR__ . '/../home/build'));
        if (empty($version)) {
            $timestamp = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
                ->format(\DateTimeInterface::ATOM);
            $base = trim(\shell_exec('2>/dev/null git symbolic-ref --short HEAD || git show --format="%%h" --no-patch') ?? '');

            return \sprintf(
                'Dev build at %s%s',
                $timestamp,
                $base ? \sprintf(' (base: %s)', $base) : ''
            );
        }

        return $version;
    }

    public static function getMetadata(): array
    {
        return ['application_version' => self::getVersion()];
    }
}
