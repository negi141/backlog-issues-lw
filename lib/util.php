<?php

class Util
{
    public static function vardump($object)
    {
        echo "<pre>";
        var_dump($object);
        echo "</pre>";
    }

    public static function leftString($value, $len, $addString = '...')
    {
        $result = $value;
        if (mb_strlen($value, "UTF-8") > $len) {
            $result = mb_substr($value, 0, $len, "UTF-8") . $addString;
        }
        return $result;
    }
}
