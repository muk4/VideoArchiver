<?php

/**
 * Helper class that handles console colors.
 */
class Color 
{
    const GRN = "\033[92m";
    const BLD = "\033[01m";
    const RED = "\033[01;31m";
    const YEL = "\033[01;33m";
    const END = "\033[0m";
    
    /**
     * Returns green hashtag used as list marker.
     * @return string Green hashtag.
     */
    public static function hash()
    {
        return sprintf("%s[#]%s ", Color::GRN, Color::END);
    }
    
    /**
     * Returns colored text.
     * @param string Text to color.
     * @param string Color constant from Color class.
     * @return string Colored text.
     */
    public static function text($text, $color)
    {
        return $color . $text . Color::END;
    }
}
