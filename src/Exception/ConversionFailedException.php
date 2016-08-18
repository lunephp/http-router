<?php


namespace Lune\Http\Router\Exception;

use Exception;

class ConversionFailedException extends Exception
{
    public function __construct($input)
    {

        parent::__construct("Conversion to RouteHandler failed");
    }
}