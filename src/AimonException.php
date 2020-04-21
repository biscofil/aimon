<?php

namespace Aimon;

/**
 * Class AimonException
 * @package Aimon
 */
class AimonException extends \Exception
{

    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }

}