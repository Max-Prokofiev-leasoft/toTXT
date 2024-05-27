<?php

namespace GingerPlugins\Data;

use GingerPlugin\Components\Classes\Redefiner;
use GingerPlugins\Components\Classes\Helper;
use GingerPlugins\Entities\Credential;
use GingerPlugins\Exceptions\InvalidCredentialException;
use GingerPlugins\Log\Log;
use GingerPlugins\Sql\Connection;

require_once(dirname(__FILE__, 2) . '/Sql/Connection.php');

/**
 * Class Data_Credential
 * Concrete class for all data manipulations for Credentials
 *
 **/
class Data_Credential
{
    /**
     * Inserts 1 row containing a Credential into the data file
     *
     * @static
     *
     * @param Credential $oCredential
     *
     * @return bool
     * @throws \Exception
     */
    public static function Insert(Credential $oCredential): bool
    {
        $redefiner = new Redefiner();

        $storeId = $redefiner->getStoreId();
        $oCredential->SetStoreId($storeId);

        if (is_null(Data_Credential::GetOneByStoreId($storeId))) {
            $oSqlConnection = new Connection();
            $iInsertId = $oSqlConnection->Insert('store_data', Data_Credential::EncodeData($oCredential));
            Log::Write('Data_Credential::Insert', 'INPUT', 'Row inserted into database with Id ' . $iInsertId);
            return true;
        } else {
            Data_Credential::Update($oCredential);
        }
    }

    /**
     * Updates 1 row containing a Credential based on the Public Key
     *
     * @static
     *
     * @param Credential $oCredential
     *
     * @return bool
     * @throws \Exception
     */
    public static function Update(Credential $oCredential)
    {
        $oSqlConnection = new Connection();
        if ($oCredential->GetStoreId()) {
            $oSqlConnection->Update(
                'store_data',
                Data_Credential::EncodeData($oCredential),
                'store_id',
                $oCredential->GetStoreId()
            );
        } else {
            $oSqlConnection->Update(
                'store_data',
                Data_Credential::EncodeData($oCredential),
                'store_ap',
                base64_encode($oCredential->GetApiPublic())
            );
        }
        Log::Write('Data_Credential::Update', 'INPUT', 'Row updated on ' . $oCredential->GetApiPublic());

        return true;
    }

    /**
     * Deletes 1 row containing a WebHook based on the Public Key
     * @static
     *
     * @param Credential $oCredential
     *
     * @return bool
     * @throws \Exception
     */
    public static function Delete(Credential $oCredential)
    {
        $oSqlConnection = new Connection();
        $oSqlConnection->Delete('store_data', 'store_id', $oCredential->GetStoreId());
        Log::Write('Data_Credential::Delete', 'INPUT', 'Row deleted on ' . $oCredential->GetStoreId());

        return true;
    }

    /**
     * Return one Credential based on the Store id
     *
     * @static
     *
     * @param string $api_public
     *
     * @return Credential
     * @throws InvalidCredentialException
     * @throws \Exception
     */
    public static function GetOneByApiPublic($api_public = '')
    {
        $oSqlConnection = new Connection();
        $a = base64_encode($api_public);
        $aRow = $oSqlConnection->SelectOne(
            "
				SELECT *
				FROM `store_data`
				WHERE `store_ap` = '" . base64_encode($api_public) . "'
			"
        );
        if (!empty($aRow)) {
            return new Credential(Data_Credential::DecodeData($aRow[0]['payload']));
        } else {
            return null;
        }
    }

    /**
     * Return one Credential based on the Public Key
     *
     * @static
     *
     * @param string $store_id
     *
     * @return Credential
     * @throws InvalidCredentialException
     * @throws \Exception
     */
    public static function GetOneByStoreId($store_id = '')
    {
        if (!$store_id) {
            return null;
        }

        $oSqlConnection = new Connection();
        $aRow = $oSqlConnection->SelectOne(
            "
				SELECT *
				FROM `store_data`
				WHERE `store_id` = " . $store_id
        );
        if (!empty($aRow)) {
            return new Credential(Data_Credential::DecodeData($aRow[0]['payload']));
        } else {
            return null;
        }
    }

    /**
     * Decode data
     *
     * @static
     * @param string $data
     * @return array
     */
    public static function DecodeData($data)
    {
        return json_decode(base64_decode($data));
    }

    /**
     * Encode credentials
     *
     * @static
     * @param Credential $oCredential
     * @return array
     */
    public static function EncodeData(Credential $oCredential)
    {
        $data = [];
        if (!empty($oCredential->GetStoreId())) {
            $data['store_id'] = $oCredential->GetStoreId();
        }
        $data['payload'] = base64_encode(json_encode($oCredential->ToArray()));
        $data['store_ap'] = base64_encode($oCredential->GetApiPublic());

        return $data;
    }
}