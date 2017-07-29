<?php

class Html
{
    public static function icon($name)
    {
        return "<span class='glyphicon glyphicon-{$name}'></span>";
    }

    public static function tag($tagName, $content, $attr = '')
    {
        if ($attr !== '') $attr = " " . $attr;
        return "<{$tagName}{$attr}>" . $content . "</{$tagName}>";
    }

}