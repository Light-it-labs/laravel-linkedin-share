<?php

namespace Lightit\LinkedinShare\Facades;

class LinkedinShare extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return \Lightit\LinkedinShare\LinkedinShare::class;
    }
}
