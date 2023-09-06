<?php

namespace WPVoiceNews\Api\Exception;

class NotFoundException extends \RuntimeException
{
    /** @var int */
    protected $code = 2;
    /** @var string */
    protected  $message    = 'Данные не найдены.';
    public int $statusCode = 200;
}