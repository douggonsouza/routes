<?php

namespace douggonsouza\routes\autentications;

interface autenticationsInterface
{
    /**
     * Get the value of header
     */ 
    public function getHeader();

    /**
     * Confirma autenticação
     *
     * @return boolean
     */
    public function isAutenticate();
}
