<?php

namespace App\Exceptions;

use Exception;

class DuplicateEmailException extends Exception
{
    public function __construct()
    {
        parent::__construct('A user with this email already exists.');
    }
}
