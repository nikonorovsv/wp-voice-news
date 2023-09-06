<?php

namespace WPVoiceNews\AjaxHandlers;

use WPVoiceNews\AjaxHandler;
use WPVoiceNews\FileLoader;
use WPVoiceNews\SdkHelper;
use WPVoiceNews\PostMetaBox as Helper;
use WPVoiceNews\OptionsPage;
use WPVoiceNews\Message;
use WPVoiceNews\Sdk\Cloud;
use WPVoiceNews\Sdk\Synthesize;
use WPVoiceNews\Sdk\Message as SdkMessage;
use WPVoiceNews\Sdk\Lang;
use WPVoiceNews\Sdk\Exception\ClientException;

/**
 * Class YandexSpeechKit
 * @package WPVoiceNews\AjaxHandlers
 * Добавляет обработку Ajax запросов к SDK Yandex Speech Kit
 */
class YandexSpeechKit extends AjaxHandler
{
    const ACTION = 'yandex-speech-kit-sdk';
    const PUBLIC = false;

    /**
     * @return void
     */
    protected function synthesizeHandler() {
        // Получаем данные из POST запроса
        $postId = self::getIntQueryVar('post_id');
        $post   = get_post($postId);
        if (empty($post)) {
            $this->setError('no_query_var', Message::EMPTY_POST);
            $this->sendResponseError();
        }

        $fieldKey = self::getStringQueryVar('field_key', Helper::DEFAULT_FIELD_KEY);
        $useSSML  = self::getBoolQueryVar('use_ssml');
        $lang     = self::getStringQueryVar('lang', SdkHelper::DEFAULT_LANG);
        $voice    = self::getStringQueryVar('voice', SdkHelper::DEFAULT_VOICE);
        $emotion  = self::getStringQueryVar('emotion', SdkHelper::DEFAULT_EMOTION);
        $speed    = self::getStringQueryVar('speed', SdkHelper::DEFAULT_SPEED);

        $fieldsToUpdate = [
            'use_ssml'   => $useSSML,
            'lang'       => $lang,
            'voice'      => $voice,
            'emotion'    => $emotion,
            'speed'      => $speed,
            'field_key'  => $fieldKey,
        ];

        // Проверяем, изменился ли хэш
        $oldHash = get_post_meta($post->ID, Helper::META_KEY_FIELD_HASH, true);
        $newHash = md5($post->{$fieldKey});
        if ($oldHash !== $newHash) {
            $fieldsToUpdate['field_hash'] = $newHash;
        }

        // Сохраняем настройки поста в постмета
        Helper::updateFields($post->ID, $fieldsToUpdate);

        // Получаем и проверяем глобальные настройки
        $options = OptionsPage::getFields();
        if (empty($options['api_key'])) {
            $this->setError('settings_error', Message::NO_API_KEY);
            $this->sendResponseError();
        }

        // Готовим текст для сервиса озвучки
        $text = $post->{$fieldKey} ?? '';
        if (!$useSSML) {
            $text = SdkHelper::prepareSynthesizeText($text);
        }
        if (empty($text)) {
            $this->setError('text_error', Message::NO_TEXT);
            $this->sendResponseError();
        }
        if (!SdkHelper::isTextLengthCorrect($text)) {
            $this->setError('text_error', SdkMessage::LENGTH_ERROR);
            $this->sendResponseError();
        }

        try {
            // Создаем подключение к API
            $cloud = Cloud::createApi($options['api_key']);

            // Создаем объект с настройками озвучки
            $synthesize = new Synthesize();
            if ($useSSML) {
                $synthesize->setSsml($text);
            } else {
                $synthesize->setText($text);
            }

            // Добавляем глобальные настройки
            $synthesize->setFormat($options['format']);
            $synthesize->setSampleRate($options['sample_rate']);

            // Добавляем настройки поста
            $synthesize->setLang($lang);
            $synthesize->setSpeed($speed);

            // Только для русского языка
            if ($lang === Lang::RU_RU) {
                if ($voice) {
                    $synthesize->setVoice($voice);
                }
                if ($emotion) {
                    $synthesize->setEmotion($emotion);
                }
            }

            // Получаем бинарную запись файла озвучки
            $fileData = $cloud->request($synthesize);

            $fileLoader = new FileLoader();
            $fileLoader->setFileData($fileData);

            // Если есть, удаляем старый файл озвучки
            $oldMediaId = get_post_meta($post->ID, Helper::META_KEY_AUDIO_ID, true);
            if ($oldMediaId) {
                $deleted = $fileLoader->delete($oldMediaId);
                if ($deleted === false) {
                    $this->setError('file_loader_error', Message::FILE_NOT_DELETED);
                    $this->sendResponseError();
                }
            }

            // Сохраняем файл озвучки в медиатеку
            $mediaId = $fileLoader->attach($post->ID);
            if ($mediaId instanceof \WP_Error) {
                $this->setError(
                    'file_loader_error',
                    $mediaId->get_error_message() ?: Message::FILE_IS_NOT_UPLOADED
                );
                $this->sendResponseError();
            }

            // Сохраняем ID файла в постмета
            update_post_meta($post->ID, Helper::META_KEY_AUDIO_ID, $mediaId);
            $audio = $fileLoader::getAttachment($mediaId);
            if (is_null($audio)) {
                $this->setError('file_loader_error', Message::NO_ATTACHMENT);
                $this->sendResponseError();
            }

            // Формируем ответ в случае успеха
            $this->addResponseParam('payload', [
                'url'              => $audio->guid,
                'type'             => $audio->post_mime_type,
                'noSupportMessage' => __(Message::noBrowserSupportAudio($audio->guid), WP_VOICE_NEWS_PREFIX),
                'message'          => __(Message::SYNTHESIZE_SUCCESS_MESSAGE, WP_VOICE_NEWS_PREFIX)
            ]);
        } catch (ClientException $e) {
            $this->setError('sdk_client_error', $e->getMessage());
            $this->sendResponseError();
        }
    }

    /**
     * @return void
     */
    protected function removeHandler() {
        // Получаем данные из POST запроса
        $postId = self::getIntQueryVar('post_id');
        $post   = get_post($postId);
        if (empty($post)) {
            $this->setError('no_query_var', Message::EMPTY_POST);
            $this->sendResponseError();
        }

        try {
            // Если есть, удаляем старый файл озвучки
            $oldMediaId = get_post_meta($post->ID, Helper::META_KEY_AUDIO_ID, true);
            if ($oldMediaId) {
                $fileLoader = new FileLoader();
                $deleted    = $fileLoader->delete($oldMediaId);
                if ($deleted === false) {
                    $this->setError('file_loader_error', Message::FILE_NOT_DELETED);
                    $this->sendResponseError();
                }

                // Формируем ответ в случае успеха
                $this->addResponseParam('payload', [
                    'message' => __(Message::REMOVE_SUCCESS_MESSAGE, WP_VOICE_NEWS_PREFIX)
                ]);
            }
        } catch (ClientException $e) {
            $this->setError('file_loader_error', $e->getMessage());
            $this->sendResponseError();
        }
    }
}