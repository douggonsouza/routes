<?php

namespace douggonsouza\regexed;

use douggonsouza\regexed\dicionaryInterface;

class dicionary implements dicionaryInterface
{
    /**
     * Lista array com a etiquetas da classe
     *
     * @return array
     * 
     */
    public function words()
    {
        return get_class_methods($this);
    }
}

?>