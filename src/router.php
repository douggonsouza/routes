<?php

/**
 * REGEX
 * 
 * _number      = somente números  = (\d+)
 * _char        = somente letras   = ([a-zA-Z]+)
 * _alfanumeric = letras e números = ([a-zA-Z0-9]+)
 * _string      = letras, espaço e caracteres especiais = ([a-zA-Z0-9 .\-\_]+)
 */ 

namespace douggonsouza\routes;

use driver\router\routerInterface;
use driver\router\autentications\autenticationsInterface;
use driver\router\autenticate;

abstract class router implements routerInterface
{
    // TIPOS DE REQUISIÇÃO
    const _POST   = 'POST';
    const _GET    = 'GET';
    const _PUT    = 'PUT';
    const _DELETE = 'DELETE';
    const _HEAD   = 'HEAD';

    protected static $controller;
    protected static $autenticate;

    /**
     * Colhe informações locais
     *
     * @param object $request
     * @version 1.0.0
     */
    public function request(requestInterface $request)
    {
        return;
    }

    /**
     * Undocumented function
     *
     * @param string $typeRequest
     * @param string $pattern
     * @param string $url
     * @return void
     * 
     * @version 1.0.0
     */
    public static function routing($typeRequest, $pattern, actInterface $controller, autenticationsInterface $autenticate = null)
    {
        if(!isset($typeRequest) || !isset($pattern) || !isset($controller)){
            exit(self::http_response_code(500));
        }

        if (!preg_match(
            self::translate($pattern),
            '',
            $params)) {
                return;
        }

        if(isset($autenticate)){
            if(!$autenticate->isAutenticate()){
                exit(self::http_response_code(401));
            }
        }

        exit(self::http_response_code(
            self::response($controller, array())
        ));
    }

    /**
     * Traduz a string para regex
     *
     * @param string $text
     * @return string|null
     */
    protected static function translate(string $text)
    {
        if(!isset($text) || empty($text)){
            return $text;
        }

        // traduz para regex
        return '/^'.str_replace(
            array('/',':number',':char',':alfanumeric',':string'),
            array('\/','(\d+)','([a-zA-Z]+)','([a-zA-Z0-9]+)','([a-zA-Z0-9 .\-\_]+)'),
            $text
        ).'$/';
    }

    /**
     * Instancia a classe de controller
     *
     * @param string     $controller
     * @param array|null $params
     * 
     * @return void
     * 
     * @version 1.0.1
     */
    public static function controller(string $controller, array $params = array())
    {
        if(!isset($controller) && empty($controller)){
            throw new \Exception('O parâmetro Controller é obrigatório.');
        }

        try{
            // inicia a controller
            self::setController(new $controller());
            if(is_null(self::getController())){
                return 404;
            }

            // Assets Commons
            define('_assets', $controller->getAssets());
    
            // chama evento anterior
            $controller->_before();

            // chama função main
            $controller->main(array_merge(array('params' => $params), $_REQUEST));

            // chama evento posterior
            $controller->_after();

            return 200;
        }
        catch(\Exception $e){
            return 500;
        }
    }

    /**
     * Recarrega a classe de controller
     *
     * @param string $controller
     * 
     * @return mixed
     * 
     * @version 1.0.0
     */
    public static function redirect(string $controller, array $params = array())
    {
        try{
            return self::controller($controller, $params);
        }
        catch(\Exception $e){
            return 500;
        }
    }

    /**
     * Recarrega a classe de controller
     *
     * @param string $urlRelative
     * @return mixed
     * 
     * @version 1.0.0
     * @deprecated 1.0.0
     */
    public static function location(string $urlRelative)
    {
        if(!isset($urlRelative) || empty($urlRelative) ){
            return;
        }

        header("Location: $urlRelative");
        exit;
    }

    /**
     * Devolve código de resposta
     *
     * @param int $code
     * @return int
     */
    public static function http_response_code($code = NULL)
    {
        if(!isset($code) || empty($code)){
            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }

        switch ($code) {
            case 100: $text = 'Continue';
                break;
            case 101: $text = 'Switching Protocols';
                break;
            case 200: $text = 'OK';
                break;
            case 201: $text = 'Created';
                break;
            case 202: $text = 'Accepted';
                break;
            case 203: $text = 'Non-Authoritative Information';
                break;
            case 204: $text = 'No Content';
                break;
            case 205: $text = 'Reset Content';
                break;
            case 206: $text = 'Partial Content';
                break;
            case 300: $text = 'Multiple Choices';
                break;
            case 301: $text = 'Moved Permanently';
                break;
            case 302: $text = 'Moved Temporarily';
                break;
            case 303: $text = 'See Other';
                break;
            case 304: $text = 'Not Modified';
                break;
            case 305: $text = 'Use Proxy';
                break;
            case 400: $text = 'Bad Request';
                break;
            case 401: $text = 'Unauthorized';
                break;
            case 402: $text = 'Payment Required';
                break;
            case 403: $text = 'Forbidden';
                break;
            case 404: $text = 'Not Found';
                break;
            case 405: $text = 'Method Not Allowed';
                break;
            case 406: $text = 'Not Acceptable';
                break;
            case 407: $text = 'Proxy Authentication Required';
                break;
            case 408: $text = 'Request Time-out';
                break;
            case 409: $text = 'Conflict';
                break;
            case 410: $text = 'Gone';
                break;
            case 411: $text = 'Length Required';
                break;
            case 412: $text = 'Precondition Failed';
                break;
            case 413: $text = 'Request Entity Too Large';
                break;
            case 414: $text = 'Request-URI Too Large';
                break;
            case 415: $text = 'Unsupported Media Type';
                break;
            case 500: $text = 'Internal Server Error';
                break;
            case 501: $text = 'Not Implemented';
                break;
            case 502: $text = 'Bad Gateway';
                break;
            case 503: $text = 'Service Unavailable';
                break;
            case 504: $text = 'Gateway Time-out';
                break;
            case 505: $text = 'HTTP Version not supported';
                break;
            default:
                exit('Unknown http status code "' . htmlentities($code) . '"');
                break;
        }
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header($protocol . ' ' . $code . ' ' . $text);
        $GLOBALS['http_response_code'] = $code;

        return $code;
    }

    /**
     * Get the value of controller
     */ 
    public static function getController()
    {
        return self::$controller;
    }

    /**
     * Set the value of controller
     *
     * @return  self
     */ 
    public static function setController($controller)
    {
        if(isset($controller) && !empty($controller)){
            self::$controller = $controller;
        }
    }

    /**
     * Get the value of autenticate
     */ 
    public static function getAutenticate()
    {
        return self::$autenticate;
    }

    /**
     * Set the value of autenticate
     *
     * @return  self
     */ 
    public static function setAutenticate($autenticate)
    {
        if(isset($autenticate) && !empty($autenticate)){
            self::$autenticate = $autenticate;
        }
    }
}