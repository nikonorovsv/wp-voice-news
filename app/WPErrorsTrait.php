<?php

namespace WPVoiceNews;

use WP_Error;

/**
 * Use WP_Error errors into your class
 */
trait WPErrorsTrait {
    protected WP_Error $_errors;

    /**
     * @param WP_Error|null $error_object
     */
    protected function setErrorObject( WP_Error $error_object = null ): void {
        $this->_errors = $error_object ?? new \WP_Error();
    }

    /**
     * @return WP_Error
     */
    public function getErrorObject(): WP_Error {
        return $this->_errors;
    }

    /**
     * @param string $key
     * @param string $message
     */
    protected function setError(string $key, string $message)
    {
        $message = __($message, WP_VOICE_NEWS_PREFIX);

        $this->_errors->add($key, $message);
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->_errors->get_error_messages();
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool {
        return $this->_errors->has_errors();
    }
}