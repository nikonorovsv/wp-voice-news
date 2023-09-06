<?php

namespace WPVoiceNews;

/**
 * Class FileLoader
 * @package WPVoiceNews
 * Сохраняет полученные аудиофайлы в медиатеку
 */
class FileLoader
{
    const FILE_NAME_PREFIX = 'yandex_speech_kit_';

    private string $_fileData;

    /**
     * @param string|null $fileData
     */
    public function __construct(?string $fileData = null)
    {
        if ($fileData) {
            $this->setFileData($fileData);
        }
    }

    /**
     * @param string $fileData
     * @return void
     */
    public function setFileData(string $fileData)
    {
        $this->_fileData = $fileData;
    }

    /**
     * Сохраняет бинарное содержимое файла в медиатеку WordPress
     *
     * @param $postId
     *
     * @return int|\WP_Error
     */
    public function attach($postId)
    {
        // ToDo: отключить! Или найти другое решение
        define('ALLOW_UNFILTERED_UPLOADS', true);

        // Необходимы для правильной работы функции загрузки медиафайлов
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $title  = __('Озвучка ' . get_the_title($postId), WP_VOICE_NEWS_PREFIX);
        $ext    = OptionsPage::getExtension();

        // extract mime type
        $mimeType = finfo_buffer(finfo_open(), $this->_fileData, FILEINFO_MIME_TYPE);
        $tmp      = tempnam(sys_get_temp_dir(), self::FILE_NAME_PREFIX);
        file_put_contents($tmp, $this->_fileData);

        // Для сброса кэша браузера при одинаковых названиях файлов
        $hash = md5($postId . time());

        // Загружаем файл в Медиатеку Wordpress
        $mediaId = media_handle_sideload(
            [
                'name'     => self::FILE_NAME_PREFIX . "$hash.$ext",
                'type'     => $mimeType,
                'tmp_name' => $tmp,
                'error'    => UPLOAD_ERR_OK,
                'size'     => filesize($tmp)
            ],
            $postId,
            $title
        );

        @unlink($tmp);

        return $mediaId;
    }

    /**
     * Удаляет файл из медиатеки
     *
     * @param $mediaId
     * @param bool $forceDelete
     *
     * @return array|false|\WP_Post|null
     */
    public function delete($mediaId, bool $forceDelete = true)
    {
        return wp_delete_attachment($mediaId, $forceDelete);
    }

    /**
     * @param int $mediaId
     * @return \WP_Post|null
     */
    public static function getAttachment(int $mediaId): ?\WP_Post
    {
        return get_post($mediaId);
    }
}