<?php

use WPVoiceNews\OptionsPage;
use WPVoiceNews\SdkHelper;
use WPVoiceNews\Message;

OptionsPage::updateFields();

$fields         = OptionsPage::getFields();
$title          = OptionsPage::TITLE;
$actionUrl      = OptionsPage::getCurrentUrl();
$postTypesList  = OptionsPage::getPostTypesList();
$formatsList    = SdkHelper::getFormatsList();
$sampleRateList = SdkHelper::getSampleRateList();

?>

<div class="wrap">
    <h1>
        <?php esc_html_e($title, WP_VOICE_NEWS_PREFIX); ?>
    </h1>

    <p>
        <?= __( 'Подробнее о настройках в <a href="https://cloud.yandex.ru/docs/speechkit/tts/request" target="_blank">документации</a> Yandex Speech Kit.', WP_VOICE_NEWS_PREFIX) ?>
    </p>

    <?php

    /**
     * Уведомление, если не введён API ключ
     */
    if (empty($fields['api_key'])) { ?>

        <div class="notice notice-warning">
            <p>
                <strong><?= __('Внимание!', WP_VOICE_NEWS_PREFIX); ?></strong>
                <?= __(Message::NO_API_KEY_NOTICE, WP_VOICE_NEWS_PREFIX); ?>
            </p>
        </div>

        <?php
    }

    /**
     * Уведомление, если не выбраны типы записей
     */
    if (empty($fields['post_types'])) { ?>

        <div class="notice notice-warning">
            <p>
                <strong><?= __('Внимание!', WP_VOICE_NEWS_PREFIX); ?></strong>
                <?= __(Message::NO_POST_TYPES_NOTICE, WP_VOICE_NEWS_PREFIX); ?>
            </p>
        </div>

        <?php
    } ?>

    <form id="wp_voice_news_settings" name="form" action="<?= $actionUrl ?>" method="POST">
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="api_key_field">
                        <?= __('Ключ API', WP_VOICE_NEWS_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <input
                        type="text"
                        name="api_key"
                        class="regular-text"
                        id="api_key_field"
                        value="<?= $fields['api_key'] ?? '' ?>"
                    />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    Типы записей
                </th>
                <td>
                    <fieldset>
                        <legend>
                            <?= __('Выберите поддерживаемые типы записей', WP_VOICE_NEWS_PREFIX) ?>:
                        </legend>
                        <?php

                        foreach ($postTypesList as $idx => $postType) { ?>

                            <div>
                                <label for="post_types_field-<?= $idx ?>">
                                    <input
                                        type="checkbox"
                                        id="post_types_field-<?= $idx ?>"
                                        name="post_types[]"
                                        value="<?= $postType['name'] ?>"
                                        <?= in_array($postType['name'], $fields['post_types']) ? 'checked' : '' ?>
                                    />
                                    <?= $postType['label'] ?>
                                </label>
                            </div>

                            <?php
                        } ?>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="format_field">
                        <?= __('Формат синтезируемого аудио', WP_VOICE_NEWS_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <select name="format" id="format_field">
                        <?php

                        foreach ($formatsList as $value => $label) { ?>

                            <option
                                value="<?= $value ?>"
                                <?= ($value === $fields['format']) ? 'selected' : '' ?>
                            >
                                <?= __($label, WP_VOICE_NEWS_PREFIX) ?>
                            </option>

                            <?php
                        } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="sample_rate_field">
                        <?= __('Частота дискретизации синтезируемого аудио', WP_VOICE_NEWS_PREFIX) ?>
                    </label>
                </th>
                <td>
                    <select name="sample_rate" id="sample_rate_field">
                        <?php

                        foreach ($sampleRateList as $value => $label) { ?>

                            <option
                                value="<?= $value ?>"
                                <?= ($value == $fields['sample_rate']) ? 'selected' : '' ?>
                            >
                                <?= __($label, WP_VOICE_NEWS_PREFIX) ?>
                            </option>

                            <?php
                        } ?>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input
                type="submit"
                name="submit"
                id="submit"
                class="button button-primary"
                value="<?= __('Сохранить изменения', WP_VOICE_NEWS_PREFIX) ?>"
            />
        </p>
    </form>
</div>