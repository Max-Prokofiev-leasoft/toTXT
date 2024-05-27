<?php

namespace GingerPlugins\Data;

class Translations
{
    public static function getTranslations($lang)
    {
        require_once('ginger_translations_' . $lang . '.php');
        return $ginger_translation;
    }
}
