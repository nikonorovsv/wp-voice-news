<?php

namespace WPVoiceNews;

/**
 * Class PostMetaBox
 * @package WPVoiceNews
 * Создаёт метабокс на странице записи
 */
class PostMetaBox
{
    const FIELD_ID = 'yandex-speech-kit';
    const TITLE    = 'Озвучка';

    const META_KEY_FIELD_HASH = 'yandex_speech_kit_hash';
    const META_KEY_FIELD_KEY  = 'yandex_speech_kit_field_key';
    const META_KEY_TEXT_TYPE  = 'yandex_speech_kit_text_type';
    const META_KEY_LANG       = 'yandex_speech_kit_lang';
    const META_KEY_VOICE      = 'yandex_speech_kit_voice';
    const META_KEY_EMOTION    = 'yandex_speech_kit_emotion';
    const META_KEY_SPEED      = 'yandex_speech_kit_speed';
    const META_KEY_AUDIO_ID   = 'yandex_speech_kit_audio_id';

    const DEFAULT_FIELD_KEY   = 'post_excerpt';

    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'create'], 10, 0);
    }

    /**
     * Добавляет метабокс к выбранным в настройках типам записей
     *
     * @return void
     */
    public function create()
    {
        $postTypes = get_option(OptionsPage::OPTION_NAME_POST_TYPES) ?: [];

        if (empty($postTypes)) {
            return;
        }

        add_meta_box(
            self::FIELD_ID,
            self::TITLE,
            [$this, 'includeTemplate'],
            $postTypes,
            'side',
            'low'
        );
    }

    /**
     * @param int|null $postId
     * @param array|null $data
     *
     * @return void
     */
    public static function updateFields(?int $postId, ?array $data)
    {
        if (!get_post($postId)) {
            return;
        }

        if (is_null($data)) {
            $data = $_POST;
        }

        $textType = !empty($data['use_ssml']) ? 'ssml' : 'text';

        $lang      = $data['lang']       ?? SdkHelper::DEFAULT_LANG;
        $voice     = $data['voice']      ?? SdkHelper::DEFAULT_VOICE;
        $emotion   = $data['emotion']    ?? SdkHelper::DEFAULT_EMOTION;
        $speed     = $data['speed']      ?? SdkHelper::DEFAULT_SPEED;
        $fieldKey  = $data['field_key']  ?? self::DEFAULT_FIELD_KEY;
        $fieldHash = $data['field_hash'] ?? false;

        // Сохраняем тип текста
        update_post_meta($postId, self::META_KEY_TEXT_TYPE, $textType);

        // Сохраняем выбранный язык
        update_post_meta($postId, self::META_KEY_LANG, $lang);

        // Сохраняем выбранный голос
        update_post_meta($postId, self::META_KEY_VOICE, $voice);

        // Сохраняем выбранную эмоциональную окраску
        update_post_meta($postId, self::META_KEY_EMOTION, $emotion);

        // Сохраняем выбранную скорость чтения
        update_post_meta($postId, self::META_KEY_SPEED, $speed);

        // Сохраняем выбранное поле для озвучки
        update_post_meta($postId, self::META_KEY_FIELD_KEY, $fieldKey);

        // Сохраняем хэш озвученного текста, для дальнейшего сравнения
        if ($fieldHash) {
            update_post_meta($postId, self::META_KEY_FIELD_HASH, $fieldHash);
        }
    }

    /**
     * @return void
     */
    public function includeTemplate()
    {
        include WP_VOICE_NEWS_URL . 'views/admin-post-metabox.php';
    }

    /**
     * Возвращает сохраненные значения, либо значения по умолчанию, если еще не сохранялось.
     *
     * @param int|null $postId
     * @return array
     */
    public static function getFields(?int $postId): array {
        return [
            'lang'       => get_post_meta($postId, self::META_KEY_LANG, true)       ?: SdkHelper::DEFAULT_LANG,
            'voice'      => get_post_meta($postId, self::META_KEY_VOICE, true)      ?: SdkHelper::DEFAULT_VOICE,
            'emotion'    => get_post_meta($postId, self::META_KEY_EMOTION, true)    ?: SdkHelper::DEFAULT_EMOTION,
            'speed'      => get_post_meta($postId, self::META_KEY_SPEED, true)      ?: SdkHelper::DEFAULT_SPEED,
            'field_key'  => get_post_meta($postId, self::META_KEY_FIELD_KEY, true)  ?: self::DEFAULT_FIELD_KEY,
            'field_hash' => get_post_meta($postId, self::META_KEY_FIELD_HASH, true) ?: '',
            'audio_id'   => get_post_meta($postId, self::META_KEY_AUDIO_ID, true)   ?: null,
        ];
    }

    /**
     * Возвращает список возможных полей для озвучки
     *
     * @return string[]
     */
    public static function getFieldKeysList(): array {
        return [
            'post_title'   => __('Заголовок', WP_VOICE_NEWS_PREFIX),
            'post_content' => __('Контент', WP_VOICE_NEWS_PREFIX),
            'post_excerpt' => __('Вступление', WP_VOICE_NEWS_PREFIX),
        ];
    }
}