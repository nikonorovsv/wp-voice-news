<?php

/**
 * Файл из репозитория Yandex-SpeechKit-SDK
 * @link https://github.com/itpanda-llc/yandex-speechkit-sdk
 */

namespace WPVoiceNews\Sdk\Topic;

/**
 * Class Ru
 * @package WPVoiceNews\Sdk\Topic
 * Языковая модель распознавания (Русский язык)
 *
 * @link https://cloud.yandex.ru/docs/speechkit/stt/requet
 * @link https://cloud.yandex.ru/docs/speechkit/stt/models
 */
class Ru
{
    /**
     * Основная версия (По умолчанию)
     */
    public const GENERAL = 'general';

    /**
     * Экспериментальная версия
     */
    public const GENERAL_RC = 'general:rc';

    /**
     * Предыдущая версия
     */
    public const GENERAL_DEPRECATED = 'general:deprecated';
}
