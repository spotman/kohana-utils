<?php defined('SYSPATH') OR die('No direct script access.');

class Util_Response extends Kohana_Response {

    /**
     * Response types and signatures
     */

    const HTML  = 1;
    const JSON  = 2;
    const JS    = 3;

    protected static $_content_types_signatures = array(
        self::HTML  =>  'text/html',
        self::JSON  =>  'application/json',
        self::JS    =>  'text/javascript',
    );

    protected $_content_type = self::HTML;

    /**
     * JSON response types and signatures
     */

    const JSON_SUCCESS  = 1;
    const JSON_ERROR    = 2;

    protected static $_json_response_signatures = array(
        self::JSON_SUCCESS  =>  'ok',
        self::JSON_ERROR    =>  'error',
    );

    protected static $_stack = array();

    /**
     * @var View_Wrapper
     */
    protected $_wrapper;

    /**
     * @var Request Request which initiated current response
     */
    protected $_request;

    /**
     * @return Response|NULL
     */
    public static function & current()
    {
        if ( ! static::$_stack )
            return NULL;

        return static::$_stack[ key(static::$_stack) ];
    }

    public static function push(Response $response, Request $request)
    {
        // Saving request
        $response->request($request);

        static::$_stack[] = & $response;
        end(static::$_stack);
    }

    /**
     * @return Response
     */
    public static function & pop()
    {
        $response = static::current();
        array_pop(static::$_stack);
        end(static::$_stack);
        return $response;
    }

    public function & request(Request $request = NULL)
    {
        if ( $request === NULL )
            return $this->_request;

        $this->_request = & $request;
        return $this;
    }

//    /**
//     * Generating wrapper from current content type
//     * @return Response_Wrapper
//     */
//    protected function wrapper()
//    {
//        // By default we do not add any wrapper (this is for sending files / images)
//        $type = 'transparent';
//
//        switch ( $this->_content_type )
//        {
//            case self::HTML:
//                $type = 'html';
//            break;
//        }
//
//        return Response_Wrapper::factory($type);
//    }

// TODO deal with wrappers and templates
//    /**
//     * Gets or sets the body of the response
//     * @param mixed|null $content
//     * @return $this|string
//     */
//    public function body($content = NULL)
//    {
//        if ($content === NULL)
//            return $this->get_body();
//
//        $this->_body = (string) $content;
//        return $this;
//    }
//
//    public function get_body($render_wrapper = TRUE)
//    {
//        return ( $render_wrapper AND $this->_request AND $this->_request->is_initial() )
//            ? $this->wrapper()->set_content($this->_body)->render()
//            : $this->_body;
//    }

    /**
     * Gets or sets content type of the response
     * @param int $value
     * @return int|Response
     * @throws HTTP_Exception_500
     */
    public function content_type($value = NULL)
    {
        // Act as a getter
        if ( ! $value )
            return $this->_content_type;

        // Act s a setter
        if ( ! in_array($value, array_keys(static::$_content_types_signatures)) )
            throw new HTTP_Exception_500('Unknown content type: :value', array(':value' => $value));

        $this->_content_type = $value;

        $mime = static::$_content_types_signatures[ $this->_content_type ];
        $this->headers('content-type', $mime.'; charset='.Kohana::$charset);

        return $this;
    }

    public function last_modified($timestamp)
    {
        $this->headers('last-modified', gmdate("D, d M Y H:i:s \G\M\T", $timestamp));
    }

    /**
     * Sends plain text to stdout without wrapping it by template
     * @param string $string Plain text for output
     * @param int $content_type Content type constant like Response::HTML
     */
    public function send_string($string, $content_type = self::HTML)
    {
        $this->content_type($content_type);
        $this->body($string);
    }

    /**
     * Sends JSON response to stdout
     * @param integer $result JSON result constant or raw data
     * @param mixed $data Raw data to send, if the first argument is constant
     */
    public function send_json($result = self::JSON_SUCCESS, $data = NULL)
    {
        if ( is_int($result) )
        {
            $result = $this->prepare_json($result, $data);
        }

        $this->send_string(json_encode($result), self::JSON);
    }

    /**
     * Creates structured JSON-response
     * {
     *   response: "ok|error",
     *   message: <data>
     * }
     * Makes JSON-transport between backend and frontend
     * @param $result integer Constant Request::HTML or similar
     * @param $data mixed
     * @return string
     */
    protected function prepare_json($result, $data)
    {
        $response = array("response" => static::$_json_response_signatures[ $result ]);

        if ( $data )
        {
            $response["message"] = $data;
        }

        return $response;
    }

    /**
     * Sends response for JSONP request
     * @param array $data Raw data
     * @param string|null $callback_key JavaScript callback function key
     * @throws HTTP_Exception_500
     */
    public function send_jsonp(array $data, $callback_key = NULL)
    {
        if ( $callback_key === NULL )
        {
            $callback_key = "callback";
        }

        if ( ! isset($_GET[$callback_key]) )
            throw new HTTP_Exception_500("Unknown callback function key [:key]", array(':key' => $callback_key));

        $response = $_GET[$callback_key] ."(". json_encode($data) .");";

        $this->send_string($response, self::JS);
    }

    public static function handle_exception(Kohana_Exception $e)
    {
        // Re-throw exceptions, generated by HTTP::redirect()
        if ( $e instanceof HTTP_Exception_Redirect )
            throw $e;

        $response = static::current();

        switch ( $response->content_type() )
        {
            case self::JSON:
                Kohana_Exception::_handler($e);
                $response->send_json(self::JSON_ERROR, $e->get_user_message());
                break;

            default:
                throw $e;
        }
    }

}