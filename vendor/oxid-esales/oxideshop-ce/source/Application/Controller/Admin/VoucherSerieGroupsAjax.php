<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;

/**
 * Class manages voucher assignment to user groups
 */
class VoucherSerieGroupsAjax extends \OxidEsales\Eshop\Application\Controller\Admin\ListComponentAjax
{
    /**
     * Columns array
     *
     * @var array
     */
    protected $_aColumns = ['container1' => [ // field , table,  visible, multilanguage, ident
        ['oxtitle', 'oxgroups', 1, 0, 0],
        ['oxid', 'oxgroups', 0, 0, 0],
        ['oxid', 'oxgroups', 0, 0, 1],
    ],
                                 'container2' => [
                                     ['oxtitle', 'oxgroups', 1, 0, 0],
                                     ['oxid', 'oxgroups', 0, 0, 0],
                                     ['oxid', 'oxobject2group', 0, 0, 1],
                                 ]
    ];

    /**
     * Returns SQL query for data to fetc
     *
     * @return string
     */
    protected function getQuery()
    {
        // looking for table/view
        $sGroupTable = $this->getViewName('oxgroups');
        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $sVoucherId = Registry::getRequest()->getRequestEscapedParameter('oxid');
        $sSynchVoucherId = Registry::getRequest()->getRequestEscapedParameter('synchoxid');

        // category selected or not ?
        if (!$sVoucherId) {
            $sQAdd = " from $sGroupTable where 1 ";
        } else {
            $sQAdd = " from $sGroupTable, oxobject2group where ";
            $sQAdd .= " oxobject2group.oxobjectid = " . $oDb->quote($sVoucherId) . " and $sGroupTable.oxid = oxobject2group.oxgroupsid ";
        }

        if ($sSynchVoucherId && $sSynchVoucherId != $sVoucherId) {
            $sQAdd .= " and $sGroupTable.oxid not in ( select $sGroupTable.oxid from $sGroupTable, oxobject2group where ";
            $sQAdd .= " oxobject2group.oxobjectid = " . $oDb->quote($sSynchVoucherId) . " and $sGroupTable.oxid = oxobject2group.oxgroupsid ) ";
        }

        return $sQAdd;
    }

    /**
     * Removes selected user group(s) from Voucher serie list.
     */
    public function removeGroupFromVoucher()
    {
        $aRemoveGroups = $this->getActionIds('oxobject2group.oxid');
        if (Registry::getRequest()->getRequestEscapedParameter('all')) {
            $sQ = $this->addFilter("delete oxobject2group.* " . $this->getQuery());
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->Execute($sQ);
        } elseif ($aRemoveGroups && is_array($aRemoveGroups)) {
            $sQ = "delete from oxobject2group where oxobject2group.oxid in (" . implode(", ", \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quoteArray($aRemoveGroups)) . ") ";
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->Execute($sQ);
        }
    }

    /**
     * Adds selected user group(s) to Voucher serie list.
     */
    public function addGroupToVoucher()
    {
        $aChosenCat = $this->getActionIds('oxgroups.oxid');
        $soxId = Registry::getRequest()->getRequestEscapedParameter('synchoxid');

        if (Registry::getRequest()->getRequestEscapedParameter('all')) {
            $sGroupTable = $this->getViewName('oxgroups');
            $aChosenCat = $this->getAll($this->addFilter("select $sGroupTable.oxid " . $this->getQuery()));
        }
        if ($soxId && $soxId != "-1" && is_array($aChosenCat)) {
            foreach ($aChosenCat as $sChosenCat) {
                $oNewGroup = oxNew(\OxidEsales\Eshop\Application\Model\Object2Group::class);
                $oNewGroup->oxobject2group__oxobjectid = new \OxidEsales\Eshop\Core\Field($soxId);
                $oNewGroup->oxobject2group__oxgroupsid = new \OxidEsales\Eshop\Core\Field($sChosenCat);
                $oNewGroup->save();
            }
        }
    }
}
