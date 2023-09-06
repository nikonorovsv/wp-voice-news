<?php

namespace WPVoiceNews\Api;

use WPVoiceNews\PostMetaBox;
use WPVoiceNews\Api\Exception\NotFoundException;

/**
 * Класс который отвечает за все API.
 * ToDo: Переделать, если нужно больше.
 */
class Base
{
    const NAMESPACE = 'rb';
    const VERSION   = 'v1.0';
    const ENDPOINT  = 'voice';

    /**
     * Пустой
     */
    public function __construct() {}

    /**
     * Активирует API
     * @return void
     */
    public function init() {
        add_action('rest_api_init', [$this, 'registerRoutes'], 10, 0);
    }

    /**
     * Регистрирует роуты
     * @return void
     */
    public function registerRoutes()
    {
        $this->addRoute('/(?<id>[^\/]+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'getVoiceByPostId']
            ],
        ]);
    }

    /**
     * @param string $route
     * @param array $args
     * @param bool $override
     * @return bool
     */
    protected function addRoute(string $route, array $args = [], bool $override = false): bool
    {
        $namespace = self::NAMESPACE . '/' . self::VERSION;
        $endpoint  = '/' . self::ENDPOINT;

        $commonArgs = [];
        if (isset($args['args'])) {
            $commonArgs = $args['args'];
            unset($args['args']);
        }

        if (isset($args['callback'])) {
            $args = [$args];
        }

        $defaults = [
            'methods'             => 'GET',
            'callback'            => null,
            'args'                => [],
            'permission_callback' => '__return_true'
        ];

        foreach ($args as $key => &$argGroup) {
            if (!is_numeric($key)) {
                continue;
            }
            $argGroup         = array_merge($defaults, $argGroup);
            $argGroup['args'] = array_merge($commonArgs, $argGroup['args']);
        }

        return register_rest_route($namespace, $endpoint . $route, $args, $override);
    }

    /**
     * Возвращает, если есть данные по озвучке поста по его ID
     * @param $request
     * @return array
     */
    public function getVoiceByPostId($request): array
    {
        // Необходимо передать ID поста
        if (empty($request['id']) || !is_numeric($request['id'])) {
            throw new NotFoundException();
        }

        // Пост должен существовать
        $postId = absint($request['id']);
        $post   = get_post($postId);
        if (empty($post)) {
            throw new NotFoundException();
        }

        $data = PostMetaBox::getFields($postId);

        // Аудио должно быть синтезировано
        if (empty($data['audio_id'])) {
            throw new NotFoundException();
        }

        // Если аудио синтезировано, то параметр 'field_key' существует и корректен
        // См. метод PostMetaBox::getFieldKeysList(); и его использование.
        $data['id']    = (int) $data['audio_id'];
        $data['src']   = wp_get_attachment_url($data['audio_id']);
        $data['field'] = $data['field_key'];
        $data['text']  = $post->{$data['field_key']};

        unset($data['field_key'], $data['field_hash'], $data['audio_id']);

        return $data;
    }
}