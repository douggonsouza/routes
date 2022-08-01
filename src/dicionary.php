<?php

namespace douggonsouza\routes;

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
        $list = get_class_methods($this);
        unset($list[array_search('words', $list)]);
        return $list;
    }

    /**
     * Tradux a word _number
     *
     * @param string $text
     * 
     * @return string
     * 
     */
    public function _number(string $text)
    {
        if(!isset($text) || empty($text)){
            return '';
        }

        return str_replace('_number','(\d+)', $text);
    }

    /**
     * Traduz a word _char
     *
     * @param string $text
     * 
     * @return string
     * 
     */
    public function _char(string $text)
    {
        if(!isset($text) || empty($text)){
            return '';
        }

        return str_replace('_char', '([a-zA-Z]+)', $text);
    }

    /**
     * traduz a word _alfanumeric
     *
     * @param string $text
     * 
     * @return string
     * 
     */
    public function _alfanumeric(string $text)
    {
        if(!isset($text) || empty($text)){
            return '';
        }

        return str_replace('_alfanumeric', '([a-zA-Z0-9]+)', $text);
    }

    /**
     * Traduz a word _string
     *
     * @param string $text
     * 
     * @return string
     * 
     */
    public function _string(string $text)
    {
        if(!isset($text) || empty($text)){
            return '';
        }

        return str_replace( '_string', '([a-zA-Z0-9 .\-\_]+)', $text);
    }
}
?>