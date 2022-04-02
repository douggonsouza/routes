<?php

namespace douggonsouza\routes;

use douggonsouza\router\requestInterface;
use douggonsouza\router\autentications\autenticationsInterface;

/**
 * Interface de rotas
 * 
 * @version 1.0.0
 */
interface routerInterface
{
    /**
     * colhe informações locais
     *
     * @param object $request
     * @version 1.0.0
     */
    public static function request($request);

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
    public static function routing($typeRequest, $pattern, $controller, $autenticate = null);

    /**
     * Executa a resposta do controller
     *
     * @param string     $controller
     * @param array|null $params
     * 
     * @return void
     * 
     * @version 1.0.1
     */
    public static function response($controller, array $params = array());

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
    public static function instanceController(string $controller, array $params = array());

    /**
     * Recarrega a classe de controller
     *
     * @param string $controller
     * 
     * @return mixed
     * 
     * @version 1.0.0
     */
    public static function redirectAction(string $controller, array $params = array());

    /**
     * Recarrega uma nova url relativa
     *
     * @param string $urlRelative
     * 
     * @return void
     * 
     * @version 1.0.0
     */
    public static function newLocation(string $urlRelative);

    /**
     * Devolve código de resposta
     *
     * @param int $code
     * @return int
     */
    public static function http_response_code($code = NULL);
}