<?php


namespace Help;


class Str
{
    public static function separate(string $text, string $separator = ' ', bool $trim_spaces = true) : array
    {

        $slices = preg_split('/'.$separator.'/', $text);
        if (!$trim_spaces)
            return $slices;

        return self::removeEmptyElement($slices);
    }

    public static function removeEmptyElement(array $elements, bool $trim = true) : array
    {
        $newElement = [];
        foreach ($elements as $element)
        {
            if (!empty(trim($element)))
            {
                $newElement[] = $trim? trim($element): $element;
            }
        }
        return $newElement;
    }

    public static function have(string $separator, string $text) : int
    {
        return preg_split('/'.$separator.'/', $text);
    }

}
