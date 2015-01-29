<?php
namespace Craft;

class AmCommandPlusService extends BaseApplicationComponent
{
    /**
     * Convert a string to a camel cased string.
     *
     * @param string $string
     *
     * @return string
     */
    public function camelString($string)
    {
        $string = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
        return str_replace(' ', '', lcfirst(ucwords(strtolower(strtr($string, '_-', '  ')))));
    }
}