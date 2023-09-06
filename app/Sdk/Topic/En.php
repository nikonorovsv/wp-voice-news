<?php

/**
 * Файл из репозитория Yandex-SpeechKit-SDK
 * @link https://github.com/itpanda-llc/yandex-speechkit-sdk
 */

namespace WPVoiceNews\Sdk\Topic;

/**
 * Class En
 * @package WPVoiceNews\Sdk\Topic
 * Языковая модель распознавания (Английский язык)
 *
 * @link https://cloud.yandex.ru/docs/speechkit/stt/request
 * @link https://cloud.yandex.ru/docs/speechkit/stt/models
 */
class En
{
    /**
     * Фразы (3—5 слов) на различные темы (По умолчанию)
     */
    public const GENERAL = 'general';

    /**
     * Адреса, названия организаций и географических объектов
     */
    public const MAPS = 'maps';
}
