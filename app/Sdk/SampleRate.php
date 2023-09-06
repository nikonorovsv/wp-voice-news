<?php

/**
 * Файл из репозитория Yandex-SpeechKit-SDK
 * @link https://github.com/itpanda-llc/yandex-speechkit-sdk
 */

namespace WPVoiceNews\Sdk;

/**
 * Class SampleRate
 * @package WPVoiceNews\Sdk
 * Частота дискретизации аудио
 */
class SampleRate
{
    /**
     * 48 кГц (По умолчанию)
     * @link https://cloud.yandex.ru/docs/speechkit/stt/request
     * @link https://cloud.yandex.ru/docs/speechkit/tts/request
     */
    public const KHZ_48 = '48000';

    /**
     * 16 кГц
     * @link https://cloud.yandex.ru/docs/speechkit/stt/request
     * @link https://cloud.yandex.ru/docs/speechkit/tts/request
     */
    public const KHZ_16 = '16000';

    /**
     * 8 кГц
     * @link https://cloud.yandex.ru/docs/speechkit/stt/request
     * @link https://cloud.yandex.ru/docs/speechkit/tts/request
     */
    public const KHZ_8 = '8000';
}
