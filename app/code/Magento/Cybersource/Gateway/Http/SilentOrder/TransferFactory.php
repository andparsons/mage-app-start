<?php
namespace Magento\Cybersource\Gateway\Http\SilentOrder;

use Magento\Framework\HTTP\ZendClient;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Builds gateway transfer object.
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $storeId = $request['store_id'] ?? null;
        // sending store id and other additional keys are restricted by Cybersource API
        unset($request['store_id']);

        return $this->transferBuilder
            ->setClientConfig(
                [
                    'maxredirects' => 5,
                    'timeout' => 30,
                    'verifypeer' => 1
                ]
            )
            ->setHeaders([])
            ->setBody($request)
            ->setMethod(ZendClient::POST)
            ->setUri(
                (bool)$this->config->getValue('sandbox_flag', $storeId)
                ? $this->config->getValue('transaction_url_test_mode', $storeId)
                : $this->config->getValue('transaction_url', $storeId)
            )
            ->shouldEncode(true)
            ->build();
    }
}
