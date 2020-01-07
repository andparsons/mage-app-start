<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\NegotiableQuote\Model\Cart;

/**
 * Class RemoveFailedSku
 */
class RemoveFailedSku extends \Magento\Backend\App\Action
{
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param EncoderInterface $jsonEncoder
     * @param RawFactory $resultRawFactory
     * @param Cart $cart
     */
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        EncoderInterface $jsonEncoder,
        RawFactory $resultRawFactory,
        Cart $cart
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->cart = $cart;
    }

    /**
     * Remove single error item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $sku = $this->getRequest()->getParam('remove_sku');
        try {
            $this->cart->removeFailedSku($sku);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addError(__('Exception occurred during update quote'));
        }
        $data = [];
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/json');
        $response->setContents($this->jsonEncoder->encode($data));
        return $response;
    }
}
