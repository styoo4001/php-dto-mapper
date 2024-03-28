<?php

namespace Tests\Fixtures;

class Skill
{
    private string $client;

    private string $server;

    public function getServer(): string
    {
        return $this->server;
    }
}
