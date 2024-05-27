<?php

namespace GingerPlugins\Json;

use GingerPlugins\Exceptions\InvalidJsonException;

/**
 * Class JsonSerializer
 *
 * @package Emspay\Json
 *
 * @author  Adriaan Meijer
 * @date    2014-10-13
 * @version 1.0    - First draft
 */
class JsonSerializer
{
    /**
     * Transforms a JSON string to the original format
     *
     * @static
     *
     * @param string $sJson
     *
     * @return mixed
     * @throws InvalidJsonException
     */
    static public function DeSerialize($sJson = '')
    {
        if (!is_string($sJson)) {
            throw new InvalidJsonException();
        }
        if (empty($sJson)) {
            throw new InvalidJsonException();
        }

        $oData = json_decode($sJson);

        if (json_last_error() !== 0) {
            throw new InvalidJsonException();
        }
        return $oData;
    }

    /**
     * Transforms different types of data into JSON
     *
     * @static
     *
     * @param null $mData
     *
     * @return string
     * @throws InvalidJsonException
     */
    static public function Serialize($mData = null)
    {
        $sJson = json_encode($mData);
        if (json_last_error() !== 0) {
            throw new InvalidJsonException();
        }
        return $sJson;
    }
}