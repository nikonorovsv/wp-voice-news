<?php

/*
Plugin Name: WP Voice News
Plugin URI: http://github.com/nikonorovsv/wp-voice-news
Description: Функционал для авторов, позволяющий синтезировать текст новостей в аудиозапись при помощи сервиса Yandex SpeechKit.
Version: 1.0
Author: Sergey Nikonorov
Author URI: https://nikonorovsv.com
License: GPL2
*/

const WP_VOICE_NEWS_PREFIX = 'wp-voice-news';

define('WP_VOICE_NEWS_DIR', plugin_dir_path(__FILE__));
define('WP_VOICE_NEWS_URL', plugin_dir_url(__FILE__));

require WP_VOICE_NEWS_DIR . 'vendor/autoload.php';

$config = require WP_VOICE_NEWS_DIR . 'config.php';

// Добавляем страницу настроек
$createOptionsPage = new WPVoiceNews\OptionsPage;

// Добавляем метабоксы
$createMetaBoxes = new WPVoiceNews\PostMetaBox;

// Регистрируем обработчики аякс запросов
foreach ($config['ajax_handlers'] as $handler_class_name) {
    $handler = new $handler_class_name();
    $handler->init();
}

// Инициализируем API
$api = new WPVoiceNews\Api\Base();
$api->init();

// Регистрируем стили
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style(
        'wp-voice-news-admin-styles',
        WP_VOICE_NEWS_URL . 'frontend/dist/main.css'
    );
}, 20, 1);

// Регистрируем скрипты
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script(
        'wp-voice-news-admin-scripts',
        WP_VOICE_NEWS_URL . 'frontend/dist/main.js'
    );
}, 20, 1);