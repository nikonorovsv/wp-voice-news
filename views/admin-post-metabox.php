<?php

use WPVoiceNews\PostMetaBox;
use WPVoiceNews\SdkHelper;
use WPVoiceNews\AjaxHandler;
use WPVoiceNews\FileLoader;
use WPVoiceNews\Message;

global $post;

$defaults = PostMetaBox::getFields($post->ID);

$languagesList = SdkHelper::getLanguagesList();
$voicesList    = SdkHelper::getVoicesList($defaults['lang']);
$emotionsList  = SdkHelper::getEmotionsList($defaults['voice']);
$speedsList    = SdkHelper::getSpeedsList();
$fieldsList    = PostMetaBox::getFieldKeysList();

$audio = null;
if (!empty($defaults['audio_id'])) {
    // Пустой ID вернёт вместо attachment текущий post
    $audio = FileLoader::getAttachment($defaults['audio_id']);
}

$options = [
    'field_key' => [
        'label'    => __('Поле для озвучки', WP_VOICE_NEWS_PREFIX),
        'items'    => $fieldsList,
        'selected' => $defaults['field_key'],
        'hidden'   => false,
    ],
    'lang' => [
        'label'    => __('Язык текста', WP_VOICE_NEWS_PREFIX),
        'items'    => $languagesList,
        'selected' => $defaults['lang'],
        'hidden'   => false,
    ],
    'voice' => [
        'label'    => __('Голос', WP_VOICE_NEWS_PREFIX),
        'items'    => $voicesList,
        'selected' => $defaults['voice'],
        'hidden'   => true,
    ],
    'emotion' => [
        'label'    => __('Эмоциональный окрас', WP_VOICE_NEWS_PREFIX),
        'items'    => $emotionsList,
        'selected' => $defaults['emotion'],
        'hidden'   => true,
    ],
    'speed' => [
        'label'    => __('Скорость чтения', WP_VOICE_NEWS_PREFIX),
        'items'    => $speedsList,
        'selected' => $defaults['speed'],
        'hidden'   => false,
    ]
]; ?>

<div id="yandex_speech_notice_container">
    <?php

    if (!empty($defaults['field_hash']) && $defaults['field_hash'] !== md5($post->{$defaults['field_key']})) { ?>

        <div class="yandex-speech-notice notice-warning">
            <?= __(Message::HASH_NOT_EQUAL, WP_VOICE_NEWS_PREFIX) ?>
        </div>

        <?php
    } ?>
</div>

<div id="yandex_speech_audio_container">

    <?php

    if (!empty($audio)) { ?>
        <audio controls>
            <source src="<?= esc_url($audio->guid) ?>" type="<?= $audio->post_mime_type ?>">
            <?= __(Message::BROWSER_NOT_SUPPORT_AUDIO, WP_VOICE_NEWS_PREFIX) ?>
            <a href="<?= esc_url($audio->guid) ?>">
                <?= __(Message::DOWNLOAD_AUDIO, WP_VOICE_NEWS_PREFIX) ?>
            </a>.
        </audio>
        <?php
    } ?>

</div>

<?php

foreach ($options as $key => $data) { ?>

    <p>
        <label for="yandex_speech_<?= $key ?>_field">
            <strong><?= $data['label'] ?></strong>
        </label>
        <br>
        <select
            name="<?= $key ?>"
            id="yandex_speech_<?= $key ?>_field"
            <?= (count($data['items']) > 1) ? '' : 'disabled' ?>
        >
            <?php

            foreach ($data['items'] as $value => $label) { ?>

                <option
                    value="<?= $value ?>"
                    <?= ($value === $data['selected']) ? 'selected' : '' ?>
                >
                    <?= __($label, WP_VOICE_NEWS_PREFIX) ?>
                </option>

                <?php
            } ?>
        </select>
    </p>

    <?php
} ?>

<p>
    <label for="yandex_speech_use_ssml_field">
        <input type="checkbox" name="use_ssml" id="yandex_speech_use_ssml_field">
        <?= __('Текст в <a href="https://cloud.yandex.ru/docs/speechkit/tts/ssml" target="_blank">формате SSML</a>', WP_VOICE_NEWS_PREFIX) ?>
    </label>
</p>

<p><small><?= __('Важно! Сохраните изменения перед озвучкой.', WP_VOICE_NEWS_PREFIX) ?></small></p>

<button
        id="yandex_speech_submit"
        type="button"
        class="button"
        data-nonce="<?= AjaxHandler::getNonce() ?>"
        data-post-id="<?= $post->ID ?>"
>
    <?= __($audio ? 'Озвучить заново' : 'Озвучить', WP_VOICE_NEWS_PREFIX) ?>
</button>

<button
    id="yandex_speech_remove"
    type="button"
    class="button button-link-delete<?= !$audio ? ' hidden' : '' ?>"
    data-post-id="<?= $post->ID ?>"
>
    <?= __('Удалить', WP_VOICE_NEWS_PREFIX) ?>
</button>