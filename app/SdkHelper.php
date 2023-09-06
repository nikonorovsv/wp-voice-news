<?php

namespace WPVoiceNews;

use WPVoiceNews\Sdk\Format;
use WPVoiceNews\Sdk\SampleRate;
use WPVoiceNews\Sdk\Lang;
use WPVoiceNews\Sdk\Emotion;
use WPVoiceNews\Sdk\Speed;
use WPVoiceNews\Sdk\Limit;
use WPVoiceNews\Sdk\Voice\Ru as RuVoices;

/**
 * Class SdkHelper - фасад для Sdk
 * @package WPVoiceNews
 */
class SdkHelper
{
    const DEFAULT_FORMAT      = Format::OGGOPUS;
    const DEFAULT_SAMPLE_RATE = SampleRate::KHZ_16;
    const DEFAULT_LANG        = Lang::RU_RU;
    const DEFAULT_VOICE       = RuVoices::ALENA;
    const DEFAULT_EMOTION     = Emotion::NEUTRAL;
    const DEFAULT_SPEED       = Speed::AVERAGE;

    /**
     * Возвращает доступные форматы синтезируемого аудио
     *
     * @return string[]
     */
    public static function getFormatsList(): array
    {
        return [
            Format::MP3     => __('MP3 (аудиокодек MPEG-1/2/2.5 Layer III)', WP_VOICE_NEWS_PREFIX),
            Format::OGGOPUS => __('OGG (аудиокодек OPUS)', WP_VOICE_NEWS_PREFIX),
            // Format::LPCM    => __('LPCM без WAV-заголовка', WP_VOICE_NEWS_PREFIX), // Не играет в браузере
        ];
    }

    /**
     * Возвращает доступные частоты дискретизации синтезируемого аудио
     *
     * @return string[]
     */
    public static function getSampleRateList(): array
    {
        return [
            SampleRate::KHZ_8  => '8 кГц',
            SampleRate::KHZ_16 => '16 кГц',
            SampleRate::KHZ_48 => '48 кГц',
        ];
    }

    /**
     * Возвращает список доступных языков
     *
     * @return string[]
     */
    public static function getLanguagesList(): array
    {
       return [
           Lang::RU_RU => 'Русский',
       ];
    }

    /**
     * Возвращает список доступных голосов для выбранного языка
     *
     * @param string $lang
     * @return array|string[]
     */
    public static function getVoicesList(string $lang): array
    {
        $voices = [
            Lang::RU_RU => [
                RuVoices::ALENA  => 'Алёна',
                RuVoices::FILIPP => 'Филипп',
                RuVoices::JANE   => 'Жанэ',
                RuVoices::OMAZH  => 'Омаж',
                RuVoices::ZAHAR  => 'Захар',
                RuVoices::ERMIL  => 'Эрмиль',
            ],
        ];

        return $voices[$lang] ?? [];
    }

    /**
     * Возвращает список доступных эмоциональных окрасок для выбранного голоса
     *
     * @param string $voice
     * @return array|string[]
     */
    public static function getEmotionsList(string $voice): array
    {
        $emotions = [
            RuVoices::ALENA  => [Emotion::NEUTRAL, Emotion::GOOD],
            RuVoices::FILIPP => [Emotion::NEUTRAL],
            RuVoices::JANE   => [Emotion::NEUTRAL, Emotion::GOOD, Emotion::EVIL],
            RuVoices::OMAZH  => [Emotion::NEUTRAL, Emotion::EVIL],
            RuVoices::ZAHAR  => [Emotion::NEUTRAL, Emotion::GOOD],
            RuVoices::ERMIL  => [Emotion::NEUTRAL, Emotion::GOOD],
        ];

        if (!isset($emotions[$voice])) {
            return [];
        }

        $labelsMap = [
            Emotion::NEUTRAL => 'Нейтральный',
            Emotion::GOOD    => 'Радостный',
            Emotion::EVIL    => 'Раздражённый'
        ];

        $result = [];
        foreach ($emotions[$voice] as $key) {
            $result[$key] = $labelsMap[$key];
        }

        return $result;
    }

    /**
     * Возвращает список доступных скоростей озвучки
     *
     * @return string[]
     */
    public static function getSpeedsList(): array
    {
        return [
            Speed::FASTEST => 'Самый быстрый темп',
            Speed::AVERAGE => 'Средняя скорость человеческой речи',
            Speed::SLOWEST => 'Самый медленный темп',
        ];
    }

    /**
     * Проверяет текст на превышение лимита количества символов для озвучки
     *
     * @param string $text
     * @return bool
     */
    public static function isTextLengthCorrect(string $text): bool
    {
        return (mb_strlen($text) <= Limit::SYNTHESIZE_TEXT_LENGTH);
    }

    /**
     * Очищает текст для озвучки. SSML сюда не отправлять.
     *
     * @param string $text
     * @return string
     */
    public static function prepareSynthesizeText(string $text): string
    {
        // Удаляем тэги
        $text = sanitize_text_field($text);

        // Удаляем шорткоды
        $text = self::stripShortcodes($text);
        
        // ...

        return $text;
    }

    /**
     * Удаляет все шорткоды из текста
     *
     * @param string $content
     * @return string
     */
    private static function stripShortcodes( $content ): string
    {
        if ( false === strpos( $content, '[' ) ) {
            return $content;
        }
    
        // Find all registered tag names in $content.
        preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
    
        $tags = array_values(array_unique($matches[1]));
    
        if (empty($tags)) {
            return $content;
        }
    
        $pattern = get_shortcode_regex($tags);
        $content = preg_replace_callback( "/$pattern/", 'strip_shortcode_tag', $content );
    
        return $content;
    }
}