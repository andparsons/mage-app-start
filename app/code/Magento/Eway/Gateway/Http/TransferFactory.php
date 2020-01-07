<?php
namespace Magento\Eway\Gateway\Http;

use Magento\Eway\Gateway\Http\Client\Curl;

/**
 * Class TransferFactory
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class TransferFactory extends AbstractTransferFactory
{
    /**
     * @inheritdoc
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setMethod(Curl::POST)
            ->setHeaders(['Content-Type' => 'application/json'])
            ->setBody(json_encode($request, JSON_UNESCAPED_SLASHES))
            ->setAuthUsername($this->getApiKey())
            ->setAuthPassword($this->getApiPassword())
            ->setUri($this->getUrl())
            ->build();
    }

    /**
     * Returns url.
     *
     * @return string
     */
    private function getUrl()
    {
        return $this->action->getUrl();
    }
}
