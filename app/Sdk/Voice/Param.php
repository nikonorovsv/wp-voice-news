<?php

/**
 * Файл из репозитория Yandex-SpeechKit-SDK
 * @link https://github.com/itpanda-llc/yandex-speechkit-sdk
 */

declare(strict_types=1);

namespace WPVoiceNews\Sdk\Voice;

use WPVoiceNews\Sdk\Exception;

/**
 * Class Param
 * @package WPVoiceNews\Sdk\Voice
 * Желаемый голос (Произвольный выбор)
 */
class Param
{
    /**
     * @return string Желаемый голос
     */
    public static function random(): string
    {
        try {
            $constants = (new \ReflectionClass(static::class))
                ->getConstants();
        } catch (\ReflectionException $e) {
            throw new Exception\ClientException($e->getMessage());
        }

        return $constants[array_rand($constants)];
    }
}
