<?php
namespace Magento\QuickOrder\Controller\Ajax\Product;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Suggest
 */
class Suggest extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\QuickOrder\Model\Config
     */
    private $moduleConfig;

    /**
     * @var \Magento\QuickOrder\Model\Product\Suggest\DataProvider
     */
    private $suggestDataProvider;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\QuickOrder\Model\Config $moduleConfig
     * @param \Magento\QuickOrder\Model\Product\Suggest\DataProvider $suggestDataProvider
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\QuickOrder\Model\Config $moduleConfig,
        \Magento\QuickOrder\Model\Product\Suggest\DataProvider $suggestDataProvider
    ) {
        parent::__construct($context);
        $this->moduleConfig = $moduleConfig;
        $this->suggestDataProvider = $suggestDataProvider;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        if (!$this->moduleConfig->isActive()) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setRefererUrl();
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $query = $this->getRequest()->getParam('q');
        $result->setData($this->suggestDataProvider->getItems($query));

        return $result;
    }
}
