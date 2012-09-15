<?php
namespace Reflector;

class InvalidFileException extends ReflectorException
{
    public function __construct( $fileName, \Exception $previous = null )
    {
        parent::__construct( "File '{$fileName}' not found", $previous );
    }
}
