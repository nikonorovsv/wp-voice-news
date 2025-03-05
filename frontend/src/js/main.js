import WPAjax from './ajax'
import {
  enableElement,
  disableElement,
  updateOptionsList,
  notify,
  createPlayer
} from './utils'

window.onload = function () {
  const $noticeBox = document.getElementById('wp_voice_news_notice_container')
  const $audioBox = document.getElementById('wp_voice_news_audio_container')
  const $submitButton = document.getElementById('wp_voice_news_submit')
  const $removeButton = document.getElementById('wp_voice_news_remove')

  if ($audioBox === null) {
    return
  }

  // Fields
  const $useSSMLField = document.getElementById('wp_voice_news_use_ssml_field')
  const $langField = document.getElementById('wp_voice_news_lang_field')
  const $voiceField = document.getElementById('wp_voice_news_voice_field')
  const $emotionField = document.getElementById('wp_voice_news_emotion_field')
  const $speedField = document.getElementById('wp_voice_news_speed_field')
  const $fieldKeyField = document.getElementById('wp_voice_news_field_key_field')

  /**
   * Делает AJAX-запрос, чтобы получить список доступных голосов для выбранного языка.
   * Вставляет список в DOM, с помощью функции updateOptionsList()
   *
   * @returns {Promise<void>}
   */
  const updateVoicesList = async () => {
    const ajax = new WPAjax(
      'wp-voice-news-metabox',
      'get-lang-voices',
      $submitButton.dataset.nonce
    )

    disableElement($submitButton)

    try {
      await ajax.load({
        payload: {
          post_id: $submitButton.dataset.postId,
          lang: $langField.value
        },
        onSuccess: async ({ items }) => {
          updateOptionsList($voiceField, items ?? [])

          await updateEmotionsList()

          enableElement($submitButton)
        },
        onError: e => console.log(e)
      })
    } catch (e) {
      console.log(e)
    }
  }

  /**
   * Делает AJAX-запрос, чтобы получить список доступных эмоций для выбранного голоса.
   * Вставляет список в DOM, с помощью функции updateOptionsList()
   *
   * @returns {Promise<void>}
   */
  const updateEmotionsList = async () => {
    const ajax = new WPAjax(
      'wp-voice-news-metabox',
      'get-voice-emotions',
      $submitButton.dataset.nonce
    )

    disableElement($submitButton)

    try {
      await ajax.load({
        payload: {
          post_id: $submitButton.dataset.postId,
          voice: $voiceField.value
        },
        onSuccess: ({ items }) => {
          updateOptionsList($emotionField, items ?? [])

          enableElement($submitButton)
        },
        onError: e => console.log(e)
      })
    } catch (e) {
      console.log(e)
    }
  }

  /**
   * Отправляет AJAX-запрос, чтобы синтезировать речь в соответствии с выбранными параметрами.
   *
   * @returns {Promise<void>}
   */
  const synthesize = async () => {
    const ajax = new WPAjax(
      'yandex-speech-kit-sdk',
      'synthesize',
      $submitButton.dataset.nonce
    )

    console.log(ajax)

    try {
      await ajax.load({
        payload: {
          post_id: $submitButton.dataset.postId * 1,
          lang: $langField.value,
          voice: $voiceField.value,
          emotion: $emotionField.value,
          speed: $speedField.value,
          field_key: $fieldKeyField.value,
          use_ssml: $useSSMLField.checked
        },
        onSuccess: ({ payload: { message, url, type, noSupportMessage } }) => {
          if (message) {
            notify($noticeBox, message, 'success')
          }

          if (url !== undefined && type !== undefined) {
            createPlayer($audioBox, url, type, noSupportMessage)

            $submitButton.innerText = 'Озвучить заново'
            $removeButton.classList.remove('hidden')
          }
        },
        onError: errors => {
          if (!errors) return

          errors
            .forEach(({ code, message }) => {
              if (code === 'settings_error' || code === 'text_error') {
                notify($noticeBox, message)
              } else {
                console.log(code, message)
              }
            })
        }
      })
    } catch (e) {
      console.log(e)
    }
  }

  /**
   * Отправляет AJAX-запрос, чтобы удалить файл озвучки.
   *
   * @returns {Promise<void>}
   */
  const remove = async () => {
    const ajax = new WPAjax(
      'yandex-speech-kit-sdk',
      'remove',
      $submitButton.dataset.nonce
    )

    try {
      await ajax.load({
        payload: {
          post_id: $removeButton.dataset.postId * 1
        },
        onSuccess: ({ payload: { message } }) => {
          if (message) {
            notify($noticeBox, message, 'success')
          }

          $audioBox.innerText = ''
          $submitButton.innerText = 'Озвучить'
          $removeButton.classList.add('hidden')
        },
        onError: errors => {
          if (!errors) return

          errors
            .forEach(({ code, message }) => {
              if (code === 'settings_error' || code === 'text_error') {
                notify($noticeBox, message)
              } else {
                console.log(code, message)
              }
            })
        }
      })
    } catch (e) {
      console.log(e)
    }
  }

  $langField.addEventListener('change', updateVoicesList)
  $voiceField.addEventListener('change', updateEmotionsList)
  $submitButton.addEventListener('click', synthesize)
  $removeButton.addEventListener('click', remove)
}
