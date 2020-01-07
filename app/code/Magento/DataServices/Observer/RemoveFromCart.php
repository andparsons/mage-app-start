<?php
declare(strict_types=1);

namespace Magento\DataServices\Observer;

use Magento\DataServices\Model\ProductContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * RemoveFromCart observer for data services events
 */
class RemoveFromCart implements ObserverInterface
{
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var ProductContextInterface
     */
    private $productContext;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param StoreManagerInterface $storeManager
     * @param CheckoutSession $checkoutSession
     * @param Json $jsonSerializer
     * @param ProductContextInterface $productContext
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        Json $jsonSerializer,
        ProductContextInterface $productContext
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->jsonSerializer = $jsonSerializer;
        $this->productContext = $productContext;
    }

    /**
     * Adds the cart id to a cookie for retrieval for data services js events
     *
     * @param Observer $observer
     * @return void
     * @throws FailureToSendException If cookie couldn't be sent to the browser.
     * @throws CookieSizeLimitReachedException Thrown when the cookie is too big to store any additional data.
     * @throws InputException If the cookie name is empty or contains invalid characters.
     * @throws NoSuchEntityException If store entity cannot be found
     */
    public function execute(Observer $observer)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $domain = parse_url($baseUrl, PHP_URL_HOST);
        $domain = str_replace('www.', '', $domain);

        /** @var PublicCookieMetadata $publicCookieMetadata */
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
             ->setDuration(3600)
             ->setPath('/')
             ->setDomain($domain)
             ->setHttpOnly(false);

        $productContext = $this->productContext->getContextData($observer->getEvent()->getQuoteItem()->getProduct());
        $this->cookieManager->setPublicCookie(
            "dataservices_product_context",
            $this->jsonSerializer->serialize($productContext),
            $publicCookieMetadata
        );
    }
}
