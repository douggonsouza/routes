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

use douggonsouza\routes\routerInterface;
use douggonsouza\regexed\regexed;
use douggonsouza\propertys\propertys;
use douggonsouza\mvc\view\display;
use douggonsouza\request\usagesInterface;
use douggonsouza\regexed\dicionaryInterface;
use douggonsouza\propertys\propertysInterface;
use douggonsouza\benchmarck\benchmarckInterface;
use douggonsouza\mvc\control\controllersInterface;
use douggonsouza\mvc\view\views;

abstract class router implements routerInterface
{
    // TIPOS DE REQUISIÇÃO
    const _POST   = 'POST';
    const _GET    = 'GET';
    const _PUT    = 'PUT';
    const _PATCH  = 'PATCH';
    const _DELETE = 'DELETE';
    const _HEAD   = 'HEAD';

    protected static $regexed;
    protected static $benchmarck;
    protected static $controller;
    protected static $autenticate;
    protected static $usages;
    protected static $infos;

    const VERBS_HTTP = array(
        self::_POST => self::_POST,
        self::_GET  => self::_GET,
        self::_PUT  => self::_PUT,
        self::_PATCH  => self::_PATCH,
        self::_DELETE => self::_DELETE,
        self::_HEAD => self::_HEAD,
    );

    /**
     * Recebe dicionario de tradução regex
     *
     * @param dicionaryInterface $dicionary
     * 
     * @return void
     * 
     */
    public static function dicionary(dicionaryInterface $dicionary)
    {
        self::setRegexed($dicionary);
    }

    /**
     * Recebe a classe usages
     *
     * @param usagesInterface $usages
     * @param propertysInterface|null $propertys
     * 
     * @return [type]
     * 
     */
    public static function usages(usagesInterface $usages)
    {
        self::setUsages($usages);

        self::fillInfos(self::getUsages());

        return self::getUsages();
    }

    /**
     * Objeto referência do template
     *
     * @param string $benchmarck
     * 
     * @return mixed
     * 
     */
    public static function benchmarck($benchmarck)
    {
        self::setBenchmarck($benchmarck);
        return self::getBenchmarck();
    }

    /**
     * Expõe o valor da etiqueta
     * 
     * @param string $label
     * 
     * @return string
     */
    public static function label(string $label)
    {
        $benchmarck = self::getBenchmarck();
        if(!isset($benchmarck)){ 
            throw new \Exception("Benchmarck não identificado.");
        }

        return $benchmarck->getLanguage()->get($label);
    }

    /**
     * Expõe o objeto de alertas
     * 
     * @param string $label
     * 
     * @return string
     */
    public static function alerts()
    {
        $benchmarck = self::getBenchmarck();
        if(!isset($benchmarck)){ 
            throw new \Exception("Benchmarck não identificado.");
        }

        return $benchmarck->getAlerts();
    }
    /**
     * Encaminha configuração de roteamento do identificador
     *
     * @param string $identify
     * @param propertysInterface|null $params
     * 
     * @return mixed
     * 
     */
    public static function block(string $template, propertysInterface $params = null)
    {
        // idenificador
        if(!isset($template) || empty($template)){
            throw new \Exception("Não identificado o template.");
        }

        try{
            // benchmarck
            views::view(null, $params, $template);
        }
        catch(\Exception $e){
            return 500;
        }
    }

    /**
     * Encaminha configuração de roteamento do identificador
     *
     * @param string $identify
     * @param propertysInterface|null $params
     * 
     * @return mixed
     * 
     */
    public static function content(propertysInterface $params)
    {
        $template = self::getController()::getPage();
        if(is_null($template) || empty($template)){
            throw new \Exception("Page Content não identificado.");
        }

        try{
            // benchmarck
            views::view(null, $params, $template);
        }
        catch(\Exception $e){
            return 500;
        }

    }

    /**
     * Encaminha configuração para assets
     *
     * @param string $asset
     * 
     * @return string
     * 
     */
    public static function assets(string $asset, string $type)
    {
        return self::getBenchmarck()->assets($asset, $type);
    }
 
    /**
     * Method control - Encaminha configuração de roteamento
     *
     * @param string $typeRequest 
     * @param string $pattern 
     * @param controllersInterface $controller 
     * @param string $function 
     *
     * @return void
     */
    public static function routing(string $typeRequest, string $pattern, controllersInterface $controller, string $function = 'main')
    {
        if(!isset($typeRequest) || !isset($pattern) || !isset($controller)){
            throw new \Exception("Parametros obrigatórios não identificados.");
        }

        // Tipo da requisição
        if(strtoupper($typeRequest) !== strtoupper(self::getUsages()->getRequestMethod())){
            return;
        }

        // Pattern
        if (!preg_match(self::translate($pattern), self::getUsages()->getRequest(), $params)){
            return;
        }

        exit(self::response($controller, $function));
    }
 
    /**
     * exit - Retorna o response code de finalização da request
     *
     * @param  mixed $responseCode
     * @param  mixed $identify
     * @return void
     */
    public static function end(string $responseCode, controllersInterface $control = null)
    {
        if(!isset($responseCode) || !isset($responseCode)){
            throw new \Exception("Parametro Response Code não identificados.");
        }

        // responde com um layout benchmarck
        if(isset($control) && is_string($control)){
            self::response($control, self::getInfos());
        }

        // responde com código
        exit(self::http_response_code($responseCode));
    }

    /**
     * withMethod - Identifica e retorna o metodo
     *
     * @param  string $controller
     * @return string
     */
    protected static function withMethod(string $controller)
    {
        $response = array($controller, 'main');
        if(is_int(strpos($controller, ':'))){
            $response = explode(':', $controller);
        }

        return $response;
    }

    /**
     * Prepara infos
     *
     * @param usagesInterface    $usages
     * @param propertysInterface $propertys
     * 
     * @return void
     * 
     */
    public static function fillInfos(usagesInterface $usages)
    {
        if(!isset($usages)){
            throw new \Exception("Parâmetros 'usages' não existe.");
        }

        self::setInfos(new propertys(array(
            'HEADER' => $usages->getHeader(),
            'REQUEST_METHOD' => $usages->getRequestMethod(),
            'GET' => $_GET,
            'POST' => $_POST,
            'FILE' => $_FILES,
            'REQUEST' => $usages->getRequest(),
            'PARAMSREQUEST' => $usages->getParamsRequest()
        )));
    }

    /**
     * Traduz a string para regex
     *
     * @param string $text
     * 
     * @return string|null
     */
    protected static function translate(string $text)
    {
        if(!isset($text) || empty($text)){
            return $text;
        }

        // traduz para regex
        return '/^' . self::getRegexed()->translate($text) . '$/';
    }
    
    /**
     * identifyMethod - Identifica e retorna o metodo
     *
     * @param  string $controller
     * @return string
     */
    protected static function identifyMethod(string &$controller)
    {
        if(is_int(strpos($controller, ':'))){
            $class = explode(':',$controller);
            $controller = $class[0];
            return $class[1];
        }

        return null;
    }
    /**
     * Method controlAction - Inicia a controller com a página
     *
     * @param controllersInterface $controller 
     * @param string $function 
     *
     * @return void
     */
    public static function response(controllersInterface $controller, string $function = 'main')
    {
        if(!isset($controller) && empty($controller)){
            throw new \Exception('O parâmetro Controller é obrigatório.');
        }

        self::setController($controller);
        if(is_null(self::getController())){
            return 404;
        }

        try{
            self::getController()->_before(self::getController()::getInfos());
            self::getController()->$function(self::getController()::getInfos());
            return 200;
        }
        catch(\Exception $e){
            return 500;
        }
    }

    /**
     * Recarrega a classe de controller
     *
     * @param string $pattern
     * 
     * @return mixed
     * 
     * @version 1.0.0
     */
    public static function redirect(string $pattern, propertysInterface $gets = null)
    {
        if(!isset($pattern) && empty($pattern)){
            throw new \Exception('O parâmetro Controller é obrigatório.');
        }

        try{
            return header('Location: '. self::getUsages()->getHeader()['Origin'] . $pattern);
        }
        catch(\Exception $e){
            return 500;
        }
    }

    /**
     * Devolve código de resposta
     *
     * @param int $code
     * 
     * @return int
     */
    protected static function http_response_code($code = NULL)
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

    /**
     * Get the value of regexed
     */ 
    public static function getRegexed()
    {
        return self::$regexed;
    }

    /**
     * Set the value of regexed
     *
     * @return  self
     */ 
    public static function setRegexed(dicionaryInterface $dicionary)
    {
        if(isset($dicionary) && !empty($dicionary)){
            self::$regexed = new regexed($dicionary);
        }
    }

    /**
     * Get the value of benchmarck
     * 
     * @return benchmarckInterface
     */ 
    public static function getBenchmarck()
    {
        return self::$benchmarck;
    }

    /**
     * Set the value of benchmarck
     *
     * @return  void
     */ 
    public static function setBenchmarck(benchmarckInterface $benchmarck)
    {
        if(isset($benchmarck) && !empty($benchmarck)){
            self::$benchmarck = $benchmarck;
        }
        return;
    }

    /**
     * Get the value of usages
     */ 
    public static function getUsages()
    {
        return self::$usages;
    }

    /**
     * Set the value of usages
     *
     * @return  self
     */ 
    protected static function setUsages(usagesInterface $usages)
    {
        if(isset($usages) && !empty($usages)){
            self::$usages = $usages;
        }
    }

    /**
     * Get the value of infos
     * 
     * @return propertysInterface
     */ 
    public static function getInfos()
    {
        return self::$infos;
    }

    /**
     * Set the value of infos
     *
     * @return  self
     */ 
    protected static function setInfos(propertysInterface $infos)
    {
        if(isset($infos) && !empty($infos)){
            self::$infos = $infos;
        }
    }
}