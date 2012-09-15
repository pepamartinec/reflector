<?php

namespace Reflector\Iterator;

class CallbackFilterIterator extends \FilterIterator
{
    protected $callback;

    protected $iterator;

    public function __construct( \Iterator $iterator, $callback )
    {
        if( !is_callable( $callback ) )
            throw new InvalidArgumentException( 'Argument $callback is expected to be a valid callback' );

        parent::__construct( $iterator );

        $this->iterator = $iterator;
        $this->callback = $callback;
    }

    public function accept()
    {
        return call_user_func( $this->callback, $this->iterator->current() );
    }
}
