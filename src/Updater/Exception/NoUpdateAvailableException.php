<?php

namespace my127\Workspace\Updater\Exception;

class NoUpdateAvailableException extends \RuntimeException
{
    public function __construct(private readonly string $currentVersion)
    {
        parent::__construct('There is no update available for workspace.');
    }

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }
}
