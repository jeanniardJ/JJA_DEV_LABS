<?php

namespace App\Message\Scanner;

class TriggerScanMessage
{
    public function __construct(
        private string $scanId,
        private string $url
    ) {
    }

    public function getScanId(): string
    {
        return $this->scanId;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
