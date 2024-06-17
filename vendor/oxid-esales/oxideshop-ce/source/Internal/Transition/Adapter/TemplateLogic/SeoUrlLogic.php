<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Transition\Adapter\TemplateLogic;

use OxidEsales\Eshop\Core\Registry;

class SeoUrlLogic
{
    /**
     * Output SEO style url
     *
     * @param array $params
     *
     * @return string
     */
    public function seoUrl(array $params): string
    {
        $sOxid = $params['oxid'] ?? null;
        $sType = $params['type'] ?? null;
        $sUrl = $sIdent = $params['ident'] ?? null;

        // requesting specified object SEO url
        if ($sType) {
            $oObject = oxNew($sType);

            // special case for content type object when ident is provided
            if ($sType == 'oxcontent' && $sIdent && $oObject->loadByIdent($sIdent)) {
                $sUrl = $oObject->getLink();
            } elseif ($sOxid) {
                // minimising aricle object loading
                if (strtolower($sType) == "oxarticle") {
                    $oObject->disablePriceLoad();
                    $oObject->setNoVariantLoading(true);
                }

                if ($oObject->load($sOxid)) {
                    $sUrl = $oObject->getLink();
                }
            }
        } elseif ($sUrl && Registry::getUtils()->seoIsActive()) {
            // if SEO is on ..
            $sStaticUrl = Registry::getSeoEncoder()->getStaticUrl($sUrl);
            if ($sStaticUrl) {
                $sUrl = $sStaticUrl;
            } else {
                // in case language parameter is not added to url
                $sUrl = Registry::getUtilsUrl()->processUrl($sUrl);
            }
        }

        return $sUrl ?: '';
    }
}
