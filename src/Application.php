<?php

namespace my127\Workspace;

use my127\Console\Application\Application as ConsoleApplication;
use my127\Console\Application\Executor;
use my127\Workspace\Environment\Environment;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Application extends ConsoleApplication
{
    private const BUILD_COMMIT = '@build_git_commit_short@';

    private Environment $environment;

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
        $version = \trim(@\file_get_contents(__DIR__ . '/../home/build') ?? '');
        if (!empty($version)) {
            return $version;
        }

        $devBranch = \trim(@\file_get_contents(__DIR__ . '/../home/buildinfo') ?? '');
        if (!empty($devBranch)) {
            return 'dev-' . $devBranch;
        }

        return 'dev-' . self::BUILD_COMMIT;
    }

    public static function getMetadata(): array
    {
        return ['application_version' => self::getVersion()];
    }
}
