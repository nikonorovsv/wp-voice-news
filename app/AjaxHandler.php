<?php

namespace WPVoiceNews;

use phpDocumentor\Reflection\Types\Boolean;

/**
 * Abstract Class AjaxHandler
 * @package WPVoiceNews
 * Добавляет обработку Ajax запросов от метабокса
 */
abstract class AjaxHandler
{
    use WPErrorsTrait;

    const NONCE_KEY          = 'sdf3kk23j4k2jkdsksjkc2342342';
    const NONCE_FIELD        = 'nonce';
    const REQUEST_ERROR_CODE = 'request_method_error';

    private array $_query_vars = [];

    protected array $payload   = [];

    public function __construct()
    {
        if (!wp_doing_ajax()) {
            return;
        }

        $this->setErrorObject();
    }

    /**
     * You can install here your actions
     * @return mixed
     */
    public function init(): void
    {
        add_action('wp_ajax_' . static::ACTION, [$this, 'handleRequest']);
        if (static::PUBLIC) {
            add_action('wp_ajax_nopriv_' . static::ACTION, [$this, 'handleRequest']);
        }
    }

    /**
     * handler for all events of the current ajax
     */
    public function handleRequest()
    {
        $this->checkNonce();
        $this->checkRequest();
        $this->setQueryVars();

        if (empty($_POST['handler']) || !is_string($_POST['handler'])) {
            $this->setError(
                self::REQUEST_ERROR_CODE,
                'Parameter "handler" isn\'t sent or it has incorrect value!'
            );
        }

        $handler_method_name = $this->getHandlerMethodName($_POST['handler']);
        if (!method_exists($this, $handler_method_name)) {
            $this->setError(
                self::REQUEST_ERROR_CODE,
                "Method $handler_method_name not found!"
            );
        }

        if ($this->hasErrors()) {
            $this->sendResponseError();
        }

        if (method_exists($this, 'beforeHandle')) {
            $this->beforeHandle();
        }

        $this->{$handler_method_name}();

        if (method_exists($this, 'afterHandle')) {
            $this->afterHandle();
        }

        $this->sendResponse();
    }

    /**
     * @param string $key
     * @param        $value
     */
    protected function addResponseParam(string $key, $value)
    {
        if (is_string($value)) {
            $value = sanitize_text_field($value);
        }
        $this->payload[$key] = $value;
    }

    /**
     *
     */
    protected function clearPayload()
    {
        $this->payload = [];
    }

    /**
     * @return void
     */
    public function sendResponseError()
    {
        wp_send_json_error($this->getErrorObject());
    }

    /**
     *
     */
    protected function sendResponse()
    {
        wp_send_json_success($this->payload);
    }

    /**
     *
     */
    protected function checkRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setError(
                self::REQUEST_ERROR_CODE,
                'Only HTTP POST method is allowed.'
            );
        }

        if (empty($_POST['action'])) {
            $this->setError(
                self::REQUEST_ERROR_CODE,
                'Parameter "action" isn\'t sent or it has an empty value!'
            );
        }

        if ($this->hasErrors()) {
            $this->sendResponseError();
        }

        //        if ( ! defined( static::ACTION ) ) {
        //            // ToDo: Something...
        //        }
    }

    /**
     * @return string
     */
    public static function getNonce(): string
    {
        return wp_create_nonce(static::NONCE_KEY);
    }

    /**
     *
     */
    protected function checkNonce(): void
    {
        check_ajax_referer(static::NONCE_KEY, static::NONCE_FIELD);
    }

    /**
     *
     */
    private function setQueryVars(): void
    {
        if (empty($_POST['data']) || !is_string($_POST['data'])) {
            return;
        }

        $this->_query_vars = self::base64Decode($_POST['data']);
    }

    /**
     * @return array
     */
    protected function getQueryVars(): array
    {
        return $this->_query_vars;
    }

    /**
     * @param string $method
     * @return string
     */
    protected function getHandlerMethodName(string $method): string
    {
        $method = htmlspecialchars(trim($method));
        $method = str_replace('-', '', lcfirst(ucwords($method, '-')));
        return "{$method}Handler";
    }

    /**
     * @param string $param_name
     * @param int|null $default
     * @return int|null
     */
    protected function getIntQueryVar(string $param_name, ?int $default = null): ?int
    {
        if (isset($this->_query_vars[$param_name])) {
            return absint($this->_query_vars[$param_name]);
        }
        return $default;
    }

    /**
     * @param string $param_name
     * @param string|null $default
     * @return string|null
     */
    protected function getStringQueryVar(string $param_name, ?string $default = null): ?string
    {
        if (isset($this->_query_vars[$param_name])) {
            return sanitize_text_field($this->_query_vars[$param_name]);
        }
        return $default;
    }

    /**
     * @param string $param_name
     * @param bool $default
     * @return bool
     */
    protected function getBoolQueryVar(string $param_name, bool $default = false): bool
    {
        return isset($this->_query_vars[$param_name])
            ? (bool)$this->_query_vars[$param_name]
            : $default;
    }

    /**
     * @param string $param_name
     * @return bool
     */
    protected function hasQueryVar(string $param_name): bool
    {
        return isset($this->_query_vars[$param_name]);
    }

    /**
     * @return void
     */
    protected function die()
    {
        if (wp_doing_ajax()) {
            wp_die('', '', ['response' => null]);
        } else {
            die;
        }
    }

    /**
     * @param array $data
     * @return string
     */
    public static function base64Encode(array $data): string
    {
        $base64_string = json_encode($data);
        $base64_string = base64_encode($base64_string);

        return wp_slash($base64_string);
    }

    /**
     * @param string $base64_string
     * @return array
     */
    public static function base64Decode(string $base64_string): array
    {
        $data = null;

        $base64_string = stripslashes($base64_string);
        if ( $json_string = base64_decode($base64_string) ) {
            $data = json_decode( $json_string );
        }

        return (array) $data;
    }
}