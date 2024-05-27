<?php

namespace GingerPlugins\Data;

use GingerPlugins\Components\Classes\Helper;
use GingerPlugins\Log\Log;
use GingerPlugins\Sql\Connection;

class Data_Transaction
{
    /**
     * Inserts 1 row containing a Credential into the data file
     *
     * @static
     *
     * @param string $sTransactionId
     * @param $gingerOrderId
     * @param $status
     * @return bool
     * @throws \Exception
     */
    public static function insert($sTransactionId, $gingerOrderId, $status): bool
    {
        try {
            if (is_null(Data_Transaction::GetRowByField('ginger_order_id', 'transaction_id', $sTransactionId))) {
                $oData = [
                    'transaction_id' => $sTransactionId,
                    'ginger_order_id' => $gingerOrderId,
                    'status' => $status,
                    'date' => date('Y-m-d h:i:s')
                ];

                $oSqlConnection = new Connection();
                $iInsertId = $oSqlConnection->Insert('store_transactions', $oData);
                Log::Write('Data_Transaction::insert', 'INPUT', 'Row inserted into database with Id ' . $iInsertId);
                return true;
            } else {
                $oData = [
                    'ginger_order_id' => $gingerOrderId,
                    'date' => date('Y-m-d h:i:s')
                ];
                Data_Transaction::Update($oData, 'transaction_id', $sTransactionId);
            }
        } catch (\PDOException $exception) {
            $connection = new Connection();
            if (!$connection->checkIfTableExists('store_transactions')) {
                $result = $connection->createTableTransaction();
                if ($result) {
                    self::insert($sTransactionId, $gingerOrderId, $status);
                }
            }
        }
    }

    /**
     * Updates 1 row containing a Credential based on the Public Key
     *
     * @static
     *
     * @param string $sFieldName
     * @param string $sMatchingValue
     * @param array $oData
     *
     * @return bool
     */
    public static function Update($oData, $sFieldName, $sMatchingValue)
    {
        $oSqlConnection = new Connection();
        if ($sFieldName !== null && $sMatchingValue !== null) {
            $oSqlConnection->Update('store_transactions', $oData, $sFieldName, $sMatchingValue);
        }
        Log::Write('Data_Credential::Update', 'INPUT', 'Row updated on ' . $sFieldName . '=' . $sMatchingValue);

        return true;
    }

    /**
     * @static
     *
     * @param string $searchField
     * @param string $wFieldname
     * @param string $wMatchingValue
     *
     * @return string
     */
    public static function GetRowByField($searchField, $wFieldname, $wMatchingValue)
    {
        $oSqlConnection = new Connection();
        $aRow = $oSqlConnection->SelectOne(
            "
				SELECT " . $searchField . "
				FROM store_transactions
				WHERE " . $wFieldname . " = '" . $wMatchingValue . "'"
        );
        if (!empty($aRow)) {
            return $aRow[0][$searchField];
        } else {
            return null;
        }
    }
}