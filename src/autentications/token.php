<?php

namespace douggonsouza\routes\autentications;

use driver\router\autentications\autenticationsInterface;
use driver\router\headerInterface;
use driver\router\header;

class token implements autenticationsInterface
{
    protected $header;
    protected $token;

    /**
     * Evento construtor da classe
     */
    public function __construct(string $token)
    {
        $this->setHeader(new header());
        $this->setToken($token);

    }

    /**
     * Get the value of header
     */ 
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set the value of header
     *
     * @return  self
     */ 
    public function setHeader(headerInterface $header)
    {
        if(isset($header) && !empty($header)){
            $this->header = $header;
        }
        return $this;
    }

    /**
     * Confirma autenticação
     *
     * @return boolean
     */
    public function isAutenticate()
    {
        if($this->getHeader()::getHeaderAutenticate()['Authorization'] == $this->getToken()){
            return true;
        }
        return false;
    }

    /**
     * Get the value of token
     */ 
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the value of token
     *
     * @return  self
     */ 
    public function setToken($token)
    {
        if(isset($token) && !empty($token)){
            $this->token = $token;
        }
        return $this;
    }
}

?>