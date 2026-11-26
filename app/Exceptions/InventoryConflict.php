<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InventoryConflict extends HttpException
{
    public function __construct(string $message = 'This room is no longer available for one or more selected nights.')
    {
        parent::__construct(409, $message);
    }
}
