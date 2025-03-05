export const enableElement = $el => {
  if ($el.hasAttribute('disabled')) {
    $el.removeAttribute('disabled')
  }
}

export const disableElement = $el => {
  if (!$el.hasAttribute('disabled')) {
    $el.setAttribute('disabled', '')
  }
}

/**
 * Обновляет переданный список HTMLSelectElement значениями из массива items.
 *
 * @param field
 * @param items
 */
export const updateOptionsList = (field, items) => {
  if (!(field instanceof HTMLSelectElement)) {
    return
  }
  if (!items) {
    return
  }

  // Включаем/выключаем список
  if (Object.keys(items).length > 1) {
    enableElement(field)
  } else {
    disableElement(field)
  }

  field.innerHTML = ''
  for (const item in items) {
    const opt = document.createElement('option')
    opt.value = item
    opt.innerHTML = items[item]

    field.append(opt)
  }
}

/**
 * Отображает уведомления в метабоксе.
 *
 * @param $el
 * @param message
 * @param type
 */
export const notify = ($el, message, type = 'error') => {
  $el.innerText = ''

  const notice = document.createElement('div')
  notice.classList.add('wp-voice-news-notice', `notice-${type}`)
  notice.innerText = message

  $el.appendChild(notice)
}

/**
 * Добавляет/обновляет аудио плеер после сохранения озвучки
 *
 * @param $el
 * @param url
 * @param type
 * @param noSupportMessage
 */
export const createPlayer = ($el, url, type, noSupportMessage = '') => {
  $el.innerText = ''

  const audio = document.createElement('audio')
  audio.setAttribute('controls', '')

  const source = document.createElement('source')
  source.setAttribute('src', url)
  source.setAttribute('type', type)
  source.innerHTML = noSupportMessage

  audio.appendChild(source)

  $el.appendChild(audio)
}

export default {
  enableElement,
  disableElement,
  updateOptionsList,
  notify,
  createPlayer
}
