<?php

declare(strict_types=1);

namespace my127\Workspace\Updater\Exception;

class UpdateNotAvailableOnDevBuildException extends \RuntimeException
{
    public function __construct(private readonly string $latestVersion)
    {
        parent::__construct('Self update unavailable on development build.');
    }

    public function getLatestVersion(): string
    {
        return $this->latestVersion;
    }
}
