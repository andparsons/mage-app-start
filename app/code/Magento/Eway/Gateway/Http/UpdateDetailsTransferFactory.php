<?php
namespace Magento\Eway\Gateway\Http;

use Magento\Eway\Gateway\Helper\TransactionReader;
use Magento\Eway\Gateway\Http\Client\Curl;
use Magento\Eway\Gateway\Validator\Shared\AccessCodeValidator;

/**
 * Class UpdateDetailsTransferFactory
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class UpdateDetailsTransferFactory extends AbstractTransferFactory
{
    /**
     * @inheritdoc
     */
    public function create(array $request)
    {
        return $this->transferBuilder->setMethod(Curl::GET)
            ->setAuthUsername($this->getApiKey())
            ->setAuthPassword($this->getApiPassword())
            ->setUri($this->getUrl($request))
            ->build();
    }

    /**
     * Returns url.
     *
     * @param array $request
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getUrl($request)
    {
        return $this->action->getUrl('/' . TransactionReader::readAccessCode($request));
    }
}
