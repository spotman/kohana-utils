<?php
namespace BetaKiller\Utils\Kohana;

trait ControllerHelperTrait
{
    /**
     * Getter for request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Setter for request
     *
     * @param Request $request
     * @return Request|$this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Getter for response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Setter for response
     *
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Helper for Request::param()
     *
     * @param string|null $key
     * @param string|null $default
     * @return mixed
     */
    protected function param($key = NULL, $default = NULL)
    {
        return $this->getRequest()->param($key, $default);
    }

    protected function post($key = NULL)
    {
        return $this->getRequest()->post($key);
    }

    protected function query($key = NULL)
    {
        return $this->getRequest()->query($key);
    }

    protected function is_ajax()
    {
        return $this->getRequest()->is_ajax();
    }

    /**
     * Getter/setter for Response content-type
     * Use this method for better uncaught exception handling
     *
     * @param int|null $type
     * @return int|Response
     */
    private function content_type($type = NULL)
    {
        return $this->getResponse()->content_type($type);
    }

    /**
     * Helper for better encapsulation of Response
     */
    protected function content_type_json()
    {
        $this->content_type(Response::TYPE_JSON);
    }

    /**
     * Helper for setting "Last-Modified" header
     * @param \DateTime $dateTime
     */
    protected function last_modified(\DateTime $dateTime)
    {
        $this->getResponse()->last_modified($dateTime);
    }

    /**
     * Helper for setting "Expires" header
     * @param \DateTime $dateTime
     */
    protected function expires(\DateTime $dateTime)
    {
        $this->getResponse()->expires($dateTime);
    }

    /**
     * Sends plain text to stdout without wrapping it by template
     *
     * @param string $string Plain text for output
     * @param int $content_type Content type constant like Response::HTML
     */
    protected function send_string($string, $content_type = Response::TYPE_HTML)
    {
        $this->getResponse()->send_string($string, $content_type);
    }

    /**
     * Helper for sending view to Response
     *
     * @param \View $view
     */
    protected function send_view(\View $view)
    {
        $this->send_string($view);
    }

    /**
     * Sends JSON response to stdout
     *
     * @param integer $result JSON result constant or raw data
     * @param mixed $data Raw data to send, if the first argument is constant
     */
    protected function send_json($result = self::JSON_SUCCESS, $data = NULL)
    {
        $this->getResponse()->send_json($result, $data);
    }

    protected function send_success_json($data = null)
    {
        $this->send_json(self::JSON_SUCCESS, $data);
    }

    protected function send_error_json($data = null)
    {
        $this->send_json(self::JSON_ERROR, $data);
    }

    /**
     * Sends response for JSONP request
     *
     * @param array $data Raw data
     * @param string|null $callback_key JavaScript callback function key
     */
    protected function send_jsonp(array $data, $callback_key = NULL)
    {
        $this->getResponse()->send_jsonp($data, $callback_key);
    }

    /**
     * Sends file to STDOUT for viewing or downloading
     *
     * @param string $content String content of the file
     * @param string $mime_type MIME-type
     * @param string $alias File name for browser`s "Save as" dialog
     * @param bool $force_download
     * @throws \HTTP_Exception_500
     */
    protected function send_file($content, $mime_type = NULL, $alias = NULL, $force_download = FALSE)
    {
        if (!$content) {
            throw new \HTTP_Exception_500('Content is empty');
        }

        $response = $this->getResponse();

        $response->body($content);

        $response->headers('Content-Type', $mime_type ?: 'application/octet-stream');
        $response->headers('Content-Length', strlen($content) );

        if ( $force_download ) {
            $response->headers('Content-Disposition', 'attachment; filename='.$alias);
        }
    }

    /**
     * Performs HTTP redirect
     *
     * @param string    $url
     * @param int       $status
     */
    protected function redirect($url, $status = 302)
    {
        $this->getResponse()->redirect($url, $status);
    }
}
