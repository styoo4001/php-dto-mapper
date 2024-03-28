<?php

namespace Styoo4001\PhpDtoMapper;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class ServiceProvider
{
    // ...
    public function boot(Application $app): void
    {
        $app->resolving(function ($object, $app) {
            if ($object instanceof RequestCommand) {
                return CommandObjectMapper::mapping($object, CommandObjectMapper::getMappingData($app->make(Request::class)));
            }
        });
    }
}
