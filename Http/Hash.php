<?php

namespace GingerPlugins\Http;

use GingerPlugins\Components\Configurators\BankConfig;
use GingerPlugins\Log\Log;

/**
 *
 * Creates an hash based on the given inputs for authentication and integretity checks in the API
 * @author  Adriaan Meijer
 * @version 1.0    - Initial creation
 *
 */
class Hash
{
    /**
     * This is the field in the header of each request that contains the hash. Do NOT change this unless instructed by CCV.
     */
    const Header_Public = 'x-public';

    /**
     * This is the field in the header of each request that contains the hash. Do NOT change this unless instructed by CCV.
     */
    const Header_Hash = 'x-hash';

    /**
     * This is the encryption method with which the hash was made. Do NOT change this unless instructed by CCV.
     */
    const Hash_Encryption = 'sha512';

    /**
     * This character separates the fields which are hashed. Do NOT change this unless instructed by CCV.
     */
    const Hash_Field_Separator = '|';

    /**
     * Public key to valid with. Normally this is the App Secret Key, but could be the API credential Secret.
     * @var null|string
     */
    protected $sSecretKey = null;

    /**
     * Collection of data to be hashed.
     * @var array
     */
    protected $aDataToHash = [];

    public function __construct($sSecretKey = null)
    {
        if (is_null($sSecretKey)) {
            $this->sSecretKey = BankConfig::AppSecretKey;
        } else {
            $this->sSecretKey = $sSecretKey;
        }
    }

    public function AddData($sData)
    {
        if (is_string($sData)) {
            $this->aDataToHash[] = $sData;
        }
        return $this;
    }

    /**
     * Returns the calculated hash
     * @return string
     */
    public function Hash()
    {
        $sStringToHash = implode($this::Hash_Field_Separator, $this->aDataToHash);
        $sHash = hash_hmac($this::Hash_Encryption, $sStringToHash, $this->sSecretKey);

        Log::Write('Hash::Hash', 'DATA', $sStringToHash);
        Log::Write('Hash::Hash', 'GENERATE', $sHash);
        return $sHash;
    }

    public function IsValid($sExpectedHash = '')
    {
        Log::Write('Hash::IsValid', 'VALIDATE', $sExpectedHash);
        if (!is_string($sExpectedHash)) {
            return false;
        }

        if ($sExpectedHash === $this->Hash()) {
            Log::Write('Hash::IsValid', 'VALID', 'OK');
            return true;
        }
        Log::Write('Hash::IsValid', 'VALID', 'FAILED');
        return false;
    }
}