<?php

namespace WPVoiceNews\AjaxHandlers;

use WPVoiceNews\AjaxHandler;
use WPVoiceNews\SdkHelper;
use WPVoiceNews\Message;

/**
 * Class PostMetaBoxHandler
 * @package WPVoiceNews\AjaxHandlers
 * Добавляет обработку Ajax запросов от метабокса
 */
class PostMetaBox extends AjaxHandler
{
    const ACTION = 'wp-voice-news-metabox';
    const PUBLIC = false;

    /**
     * Обрабатывает запрос на получение списка голосов для выбранного языка
     *
     * @return void
     */
    public function getLangVoicesHandler()
    {
        $postId = static::getIntQueryVar('post_id');
        $lang   = static::getStringQueryVar('lang');

        if (empty($postId)) {
            $this->setError('no_query_var', Message::noQueryVarMessage('post_id'));
            $this->sendResponseError();
        }
        if (empty($lang)) {
            $this->setError('no_query_var', Message::noQueryVarMessage('lang'));
            $this->sendResponseError();
        }

        $this->addResponseParam('items', SdkHelper::getVoicesList($lang));
    }

    /**
     * Обрабатывает запрос на получение списка эмоций для выбранного голоса
     *
     * @return void
     */
    public function getVoiceEmotionsHandler()
    {
        $postId = static::getIntQueryVar('post_id');
        $voice  = static::getStringQueryVar('voice');

        if (empty($postId)) {
            $this->setError('no_query_var', Message::noQueryVarMessage('post_id'));
            $this->sendResponseError();
        }
        if (empty($voice)) {
            $this->setError('no_query_var', Message::noQueryVarMessage('voice'));
            $this->sendResponseError();
        }

        $this->addResponseParam('items', SdkHelper::getEmotionsList($voice));
    }
}