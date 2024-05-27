<?php

namespace GingerPlugins\Classes;

use GingerPlugins\Components\Traits\WebRequestTrait;
use GingerPlugins\Json\JsonSerializer;
use GingerPlugins\Components\Classes\Helper;
use GingerPlugins\Log\Log;

trait CodeBlockTrait
{
    use WebRequestTrait;

    /**
     * @var null|int Contains the ID
     */
    protected ?int $RemoteAppIdContainer = null;

    /**
     * @throws \GingerPlugins\Exceptions\InvalidApiResponse
     * @throws \Exception
     */
    public function addCodeblocks($message, $placeholder = 'header-checkout-error')
    {
        $this->RemoteAppIdContainer = $this->getAppId();

        #Delete all current app codeblocks already installed for this app. Making it a clean install.
        $sOutput = $this->makeWebRequest('apps/' . $this->RemoteAppIdContainer . '/appcodeblocks', 'GET');
        $aCollectionOfCodeBlocks = JsonSerializer::DeSerialize($sOutput);

        Log::Write('AddCodeBlock', 'CollectionCodeBlock', $sOutput);

        if (isset($aCollectionOfCodeBlocks->items)) {
            foreach ($aCollectionOfCodeBlocks->items as $oItem) {
                $this->makeWebRequest('appcodeblocks/' . $oItem->id, 'DELETE');
            }
        }

        $oCodeBlock = new \stdClass();
        $oCodeBlock->placeholder = $placeholder;
        $oCodeBlock->value = '<p style="color: #ff0000">' . $message . '</p>';

        $this->makeWebRequest('apps/' . $this->RemoteAppIdContainer . '/appcodeblocks', 'POST', $oCodeBlock);
    }
}