<?php

namespace GingerPlugins\Sql;

use GingerPlugins\Components\Classes\Helper;
use GingerPlugins\Components\Configurators\BankConfig;

/**
 * Class Connection
 * Handles all configurations
 *
 * @package Sql
 * @author  Nick Postma
 * @date    2016-06-13
 * @version 1.0    - First draft
 **/
class Connection
{
    private $connection;

    /**
     * Connection constructor.
     *
     * @param string $sHost Database hostname
     * @param string $sUser Database username
     * @param string $sPassword Database password
     * @param string $sDatbase Database name
     */
    public function __construct()
    {
        try {
            $this->connection = new \PDO(
                "mysql:host=" . BankConfig::DatabaseHost . ";dbname=" . BankConfig::DatabaseName,
                BankConfig::DatabaseUsername,
                BankConfig::DatabasePassword
            );
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $oEx) {
            Helper::logGingerError(__FILE__, 'Create connection', $oEx->getMessage());
            die("Connection failed: " . $oEx->getMessage());
        }
    }


    public function checkIfTableExists($table_name)
    {
        $sql = "SELECT 1 FROM `" . $table_name . "`";
        try {
            $this->connection->prepare($sql)->execute();
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public function createTableTransaction()
    {
        try {
            $sql = "CREATE TABLE `store_transactions` (
  `id` int(6) UNSIGNED NOT NULL,
  `transaction_id` varchar(60) NOT NULL,
  `ginger_order_id` varchar(60) NOT NULL,
  `status` varchar(30) NOT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute();

            $sql = "ALTER TABLE `store_transactions`
  ADD PRIMARY KEY (`id`)";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute();

            $sql = "ALTER TABLE `store_transactions`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute();

            return true;
        } catch (\PDOException $exception) {
            Helper::logGingerError('Data_Transaction', 'createTableTransaction', $exception->getMessage());
            return false;
        }
    }

    /**
     * Select associative data by a query
     *
     * @param $sQuery
     *
     * @return array
     */
    public function Select($sQuery)
    {
        $stmt = $this->connection->prepare($sQuery);
        $stmt->execute();

        $aData = [];
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();
        if (!empty($result)) {
            $aData[] = $result;
        }
        return $aData;
    }

    /**
     * Select one row by a query
     *
     * @param string $sQuery
     *
     * @return array|null
     * @throws \Exception
     */
    public function SelectOne($sQuery)
    {
        $stmt = $this->connection->prepare($sQuery);
        $stmt->execute();
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        if ($result === false) {
            throw new \Exception($this->error);
        }

        if (!empty($result)) {
            // output data of one row
            return $result;
        }
        return null;
    }

    /**
     * Insert data into a table
     *
     * @param $sTableName
     * @param $aData
     *
     * @return mixed
     * @throws \Exception
     */
    public function Insert($sTableName, $aData)
    {
        $sQuery =
            "INSERT INTO `" . $sTableName . "` (`" . implode('`, `', array_keys($aData)) . "`) VALUES ('" . implode(
                "', '",
                $this->Escape($aData)
            ) . "'); ";
        try {
            $stmt = $this->connection->prepare($sQuery);
            $stmt->execute();
        } catch (\Exception $oEx) {
            Helper::logGingerError(__FILE__, 'Insert', $oEx->getMessage());
            die("Connection failed: " . $oEx->getMessage());
        }
    }

    /**
     * Update data in a table
     *
     * @param      $sTableName
     * @param      $aData
     * @param null $sFieldname
     * @param null $sMatchingValue
     *
     * @return mixed
     * @throws \Exception
     */
    public function Update($sTableName, $aData, $sFieldname = null, $sMatchingValue = null)
    {
        $sFieldValueString = '';

        foreach ($aData as $sFieldName => $sValue) {
            if ($sFieldValueString != '') {
                $sFieldValueString .= ", ";
            }
            $sFieldValueString .= "`" . $sFieldName . "` = '" . $this->Escape($sValue) . "'";
        }

        $sQuery = "UPDATE `" . $sTableName . "` SET " . $sFieldValueString . " ";

        if ($sFieldname !== null && $sMatchingValue !== null) {
            if (is_array($sMatchingValue)) {
                $sQuery .= "WHERE `" . $sFieldname . "` IN ('" . join("','", $sMatchingValue) . "')";
            } else {
                $sQuery .= "WHERE `" . $sFieldname . "` = '" . $this->Escape($sMatchingValue) . "'";
            }
        }

        try {
            $stmt = $this->connection->prepare($sQuery);
            $stmt->execute();
        } catch (\Exception $oEx) {
            Helper::logGingerError(__FILE__, 'Update', $oEx->getMessage());
            die("Connection failed: " . $oEx->getMessage());
        }
    }

    /**
     * Delete data from a table
     *
     * @param      $sTableName
     * @param null $sFieldname
     * @param null $sMatchingValue
     * @param null $compareFieldname
     * @param null $compareMatchingValue
     *
     * @return mixed
     * @throws \Exception
     */
    public function Delete(
        $sTableName,
        $sFieldname = null,
        $sMatchingValue = null,
        $compareFieldname = null,
        $compareMatchingValue = null
    )
    {
        $sQuery = "DELETE FROM `" . $sTableName . "` ";

        if ($sFieldname !== null && $sMatchingValue !== null) {
            $sQuery .= "WHERE `" . $sFieldname . "` = '" . $this->Escape($sMatchingValue) . "'";
        }

        if ($compareFieldname !== null && $compareMatchingValue !== null) {
            if ($sFieldname !== null && $sMatchingValue !== null) {
                $sQuery .= "AND `" . $compareFieldname . "` < '" . $compareMatchingValue . "'";
            } else {
                $sQuery .= "WHERE `" . $compareFieldname . "` < '" . $compareMatchingValue . "'";
            }
        }

        try {
            $stmt = $this->connection->prepare($sQuery);
            $stmt->execute();
        } catch (\Exception $oEx) {
            Helper::logGingerError(__FILE__, 'Delete', $oEx->getMessage());
            die("Connection failed: " . $oEx->getMessage());
        }
    }

    /**
     * Escape data for MySQL
     *
     * @param $mData mixed
     *
     * @return array|string
     */
    public function Escape($mData)
    {
        if (is_array($mData)) {
            $aEscapedData = [];
            foreach ($mData as $sKey => $sValue) {
                //$aEscapedData[$sKey] = $this->escape_string($sValue);
                $aEscapedData[$sKey] = $sValue;
            }
            return $aEscapedData;
        }

        return $mData;
    }

}