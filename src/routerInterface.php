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
     * Devolve código de resposta
     *
     * @param int $code
     * @return int
     */
    public static function http_response_code($code = NULL);
}