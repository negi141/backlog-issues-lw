<?php

class Render
{
    public static function view($file, $params = [])
    {
        return self::renderFile("view", $file, $params);
    }

    public static function layout($file, $params = [])
    {
        return self::renderFile("layout", $file, $params);
    }

    public static function renderFile($dir, $file, $params = [])
    {
        extract($params);
        ob_start();
        include "{$dir}/{$file}.php";
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}