<?php

namespace WPVoiceNews;

use WPVoiceNews\Sdk\Format;

/**
 * Class OptionsPage
 * @package WPVoiceNews
 * Создаёт страницу меню в панели администратора
 */
class OptionsPage {

    /**
     * Заголовок страницы настроек
     */
    const TITLE = 'Настройки WP Voice News';

    /**
     * Название пункта меню в панели администратора
     */
    const MENU_TITLE = 'WP Voice News';

    /**
     * Слаг URL страницы настроек
     */
    const MENU_SLUG  = 'wp-voice-news';

    /**
     * Права на редактирование страницы
     */
    const CAPABILITY = 'manage_options';

    const OPTION_NAME_API_KEY     = 'wp_voice_news_api_key';
    const OPTION_NAME_POST_TYPES  = 'wp_voice_news_post_types';
    const OPTION_NAME_FORMAT      = 'wp_voice_news_format';
    const OPTION_NAME_SAMPLE_RATE = 'wp_voice_news_sample_rate';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'create']);
    }

    /**
     * Добавляет страницу настроек
     *
     * @return void
     */
    public function create()
    {
        add_options_page(
            self::TITLE,
            self::MENU_TITLE,
            self::CAPABILITY,
            self::MENU_SLUG,
            [$this, 'includeTemplate']
        );
    }

    /**
     * @return void
     */
    public function includeTemplate ()
    {
        include WP_VOICE_NEWS_DIR . 'views/admin-options-page.php';
    }

    /**
     * Сохраняет в базу данных значения со страницы настроек
     *
     * @return void
     */
    public static function updateFields()
    {
        if (empty($_POST)) {
            return;
        }

        $apiKey     = $_POST['api_key']     ?? '';
        $postTypes  = $_POST['post_types']  ?? [];
        $format     = $_POST['format']      ?? SdkHelper::DEFAULT_FORMAT;
        $sampleRate = $_POST['sample_rate'] ?? SdkHelper::DEFAULT_SAMPLE_RATE;

        // Сохраняем ключ API
        update_option(self::OPTION_NAME_API_KEY, $apiKey, false);

        // Сохраняем выбранные типы записей
        update_option(self::OPTION_NAME_POST_TYPES, $postTypes, false);

        // Сохраняем формат синтезируемого аудио
        update_option(self::OPTION_NAME_FORMAT, $format, false);

        // Сохраняем частоту дискретизации синтезируемого аудио
        update_option(self::OPTION_NAME_SAMPLE_RATE, $sampleRate, false);
    }

    /**
     * Возвращает массив сохранённых опций
     *
     * @return array
     */
    public static function getFields(): array
    {
        return [
            'api_key'     => get_option(self::OPTION_NAME_API_KEY)     ?: '',
            'post_types'  => get_option(self::OPTION_NAME_POST_TYPES)  ?: [],
            'format'      => get_option(self::OPTION_NAME_FORMAT)      ?: SdkHelper::DEFAULT_FORMAT,
            'sample_rate' => get_option(self::OPTION_NAME_SAMPLE_RATE) ?: SdkHelper::DEFAULT_SAMPLE_RATE
        ];
    }

    /**
     * Возвращает все типы записей, поддерживаемые настройками плагина
     *
     * @return array
     */
    public static function getPostTypesList(): array
    {
        $queryArgs = [
            'public'   => true,
            '_builtin' => false
        ];
        $output    = 'objects';
        $postTypes = get_post_types($queryArgs, $output);

        $result = [
            ['name' => 'post', 'label' => __('Записи', WP_VOICE_NEWS_PREFIX)],
            ['name' => 'page', 'label' => __('Страницы', WP_VOICE_NEWS_PREFIX)],
        ];

        foreach ($postTypes as $postType) {
            $result[] = [
                'name'  => $postType->name,
                'label' => $postType->label,
            ];
        }


        return $result;
    }

    /**
     * Возвращает корректный URL страницы настроек
     *
     * @return string
     */
    public static function getCurrentUrl(): string
    {
        if (!is_admin()) {
            return '';
        }

        $screen  = get_current_screen();
        $url     = get_admin_url();
        if (!empty($screen->parent_file)) {
            $url .= $screen->parent_file;
        }

        return add_query_arg('page', self::MENU_SLUG, $url);
    }

    /**
     * @return string
     */
    public static function getExtension(): string
    {
        $format = get_option(self::OPTION_NAME_FORMAT, SdkHelper::DEFAULT_FORMAT);

        $ext = '';
        switch ($format) {
            case Format::MP3:
                $ext = 'mp3';
                break;
            case Format::OGGOPUS:
                $ext = 'ogg';
                break;
            case Format::LPCM:
                $ext = 'wav';
                break;
            default:
                break;
        }

        return $ext;
    }
}