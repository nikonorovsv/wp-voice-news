<?php

namespace WPVoiceNews;

/**
 * Class Message
 * @package WPVoiceNews
 * Сообщения исключений
 */
class Message
{
    /**
     * Ошибка, если нет поста с таким ID
     */
    public const EMPTY_POST = 'Пост с таким ID не существует.';

    /**
     * Ошибка настроек, не введён ключ API
     */
    public const NO_API_KEY = 'На странице настроек не введен ключ API.';

    /**
     * Ошибка настроек, не введён ключ API
     */
    public const NO_TEXT = 'Текст выбранный для синтезирования пуст. Введите текст и сохранитесь.';

    /**
     * Ошибка загрузки файла, не введен идентификатор каталога
     */
    public const FILE_IS_NOT_UPLOADED = 'Не удалось сохранить файл озвучки.';

    /**
     * Уведомление на странице настроек, не выбран ни один тип записи
     */
    public const NO_POST_TYPES_NOTICE = 'Не выбрано ни одного типа записи.';

    /**
     * Уведомление на странице настроек, не введён ключ API
     */
    public const NO_API_KEY_NOTICE = 'На странице настроек не введен ключ API.';

    /**
     * Уведомление, когда не удалось получить объект attachment после сохранения
     */
    public const NO_ATTACHMENT = 'После сохранения не удалось получить данные о файле из базы данных.';

    public const SYNTHESIZE_SUCCESS_MESSAGE = 'Файл озвучки успешно создан!';

    public const REMOVE_SUCCESS_MESSAGE = 'Файл озвучки удален!';

    public const BROWSER_NOT_SUPPORT_AUDIO = 'Тег audio не поддерживается вашим браузером.';

    public const DOWNLOAD_AUDIO = 'Скачать файл.';

    public const FILE_NOT_DELETED = 'Не удалось удалить старый файл.';

    public const HASH_NOT_EQUAL = 'Текст изменился!';

    /**
     * @param string $varName
     * @return string
     */
    public static function noQueryVarMessage(string $varName): string
    {
        return sprintf('Параметр "%s" не передан.', $varName);
    }

    /**
     * @param string $url
     * @return string
     */
    public static function noBrowserSupportAudio(string $url): string {
        $text = self::BROWSER_NOT_SUPPORT_AUDIO;
        $text .= sprintf(' <a href="%s">Скачать</a>', esc_url($url));

        return $text;
    }
}
