<?php
namespace BetaKiller\Utils\Kohana;

use BetaKiller\ExceptionInterface;

class Response extends \Kohana_Response
{
    /**
     * Response types and signatures
     */

    const TYPE_HTML = 1;
    const TYPE_JSON = 2;
    const TYPE_JS   = 3;
    const TYPE_XML  = 4;

    protected static $_content_types_signatures = array(
        self::TYPE_HTML =>  'text/html',
        self::TYPE_JSON =>  'application/json',
        self::TYPE_JS   =>  'text/javascript',
        self::TYPE_XML  =>  'text/xml',
    );

    protected $_content_type = self::TYPE_HTML;

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

//    /**
//     * @var WrapperView
//     */
//    protected $_wrapper;

    /**
     * @var \Request Request which initiated current response
     */
    protected $_request;

    /**
     * @return \Response|NULL
     */
    public static function current()
    {
        return current(static::$_stack);
    }

    public static function push(\Response $response, \Request $request)
    {
        // Saving request
        $response->request($request);

        static::$_stack[] = $response;
        end(static::$_stack);
    }

    /**
     * @return \Response
     */
    public static function pop()
    {
        $response = static::current();
        array_pop(static::$_stack);
        end(static::$_stack);
        return $response;
    }

    public function request(\Request $request = NULL)
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
//            ? $this->wrapper()->setContent($this->_body)->render()
//            : $this->_body;
//    }

    /**
     * Gets or sets content type of the response
     * @param int $value
     * @return int|Response
     * @throws \Kohana_Exception
     */
    public function content_type($value = NULL)
    {
        // Act as a getter
        if ( ! $value ) {
            return $this->_content_type;
        }

        // Act s a setter
        if (!array_key_exists($value, static::$_content_types_signatures)) {
            throw new \Kohana_Exception('Unknown content type: :value', array(':value' => $value));
        }

        $this->_content_type = $value;

        $mime = static::$_content_types_signatures[ $this->_content_type ];
        $this->headers('content-type', $mime.'; charset='.\Kohana::$charset);

        return $this;
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @return \Response|\DateTimeInterface|null
     */
    public function last_modified(\DateTimeInterface $dateTime = NULL)
    {
        $value = $dateTime ? gmdate("D, d M Y H:i:s \G\M\T", $dateTime->getTimestamp()) : NULL;

        if ( $value )
        {
            return $this->headers('last-modified', $value);
        }
        else
        {
            $current_value = $this->headers('last-modified');
            return $current_value
                ? (new \DateTime())->setTimestamp(strtotime($current_value))
                : NULL;
        }
    }

    public function expires(\DateTimeInterface $dateTime)
    {
        $this->headers('expires', gmdate("D, d M Y H:i:s \G\M\T", $dateTime->getTimestamp()));
    }

    public function check_if_not_modified_since()
    {
        if ( $request_ts = $this->get_if_modified_since_timestamp() )
        {
            $document_dt = $this->last_modified();

            if ( ! $document_dt )
                return FALSE;

            if ( $request_ts >= $document_dt->getTimestamp() )
            {
                // Set status and drop body
                $this->status(304)->body('');
                return TRUE;
            }
        }

        return FALSE;
    }

    protected function get_if_modified_since_timestamp()
    {
        if ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
        {
            $mod_time = $_SERVER['HTTP_IF_MODIFIED_SINCE'];

            // Some versions of IE6 append "; length=####"
            if (($strpos = strpos($mod_time, ';')) !== FALSE)
            {
                $mod_time = substr($mod_time, 0, $strpos);
            }

            return strtotime($mod_time);
        }

        return NULL;
    }

    public function http2_server_push($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $type = $this->detect_http2_server_push_type($path);

        $value = $path.'; rel=preload; as='.$type;

        $this->_header->offsetSet('link', $value, false);
    }

    protected function detect_http2_server_push_type($path)
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $map = [
            'image'     =>  ['jpg', 'jpeg', 'png', 'gif', 'svg'],
            'script'    =>  ['js'],
            'style'     =>  ['css'],
        ];

        foreach ($map as $type => $extensions) {
            if (in_array($ext, $extensions)) {
                return $type;
            }
        }

        throw new \Exception('Can not detect HTTP2 Server Push type for url :url', [':url' => $path]);
    }

    public function render()
    {
        // If content was not modified
        $this->check_if_not_modified_since();

        return parent::render();
    }

    /**
     * Sends plain text to stdout without wrapping it by template
     * @param string $string Plain text for output
     * @param int $content_type Content type constant like Response::HTML
     */
    public function send_string($string, $content_type = self::TYPE_HTML)
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

        $this->send_string(json_encode($result), self::TYPE_JSON);
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
     * @return array
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
     * @throws \HTTP_Exception_500
     */
    public function send_jsonp(array $data, $callback_key = NULL)
    {
        if ( $callback_key === NULL )
        {
            $callback_key = "callback";
        }

        if ( ! isset($_GET[$callback_key]) )
            throw new \HTTP_Exception_500("Unknown callback function key [:key]", array(':key' => $callback_key));

        $response = $_GET[$callback_key] ."(". json_encode($data) .");";

        $this->send_string($response, self::TYPE_JS);
    }

    /**
     * Performs HTTP redirect
     *
     * @param string    $url
     * @param int       $status
     */
    public function redirect($url, $status = 302)
    {
        \HTTP::redirect($url, $status);
    }

    /**
     * @param \Throwable $e
     *
     * @throws \HTTP_Exception_Redirect
     * @throws \Kohana_Exception
     * @throws \Throwable
     */
    public static function handle_exception(\Throwable $e)
    {
        // Re-throw exceptions, generated by HTTP::redirect()
        if ($e instanceof \HTTP_Exception_Redirect) {
            throw $e;
        }

        $response = static::current();

        if (!$response) {
            throw $e;
        }

        switch ( $response->content_type() )
        {
            case self::TYPE_JSON:
                \Kohana_Exception::log($e);

                $show = ($e instanceof ExceptionInterface) && $e->showOriginalMessageToUser();

                if ($show) {
                    $message = $e->getMessage() ?: __($e->getDefaultMessageI18nKey());

                    $response->send_json(self::JSON_ERROR, $message);
                } else {
                    $response->send_json(self::JSON_ERROR);
                }

                break;

            default:
                throw $e;
        }
    }

}
