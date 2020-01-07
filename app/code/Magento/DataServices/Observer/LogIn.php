<?php
declare(strict_types=1);

namespace Magento\DataServices\Observer;

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
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * LogIn observer for data services events
 */
class LogIn implements ObserverInterface
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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param StoreManagerInterface $storeManager
     * @param CartRepositoryInterface $cartRepository
     * @param Json $jsonSerializer
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        StoreManagerInterface $storeManager,
        CartRepositoryInterface $cartRepository,
        Json $jsonSerializer
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->storeManager = $storeManager;
        $this->cartRepository = $cartRepository;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Set a flag if customers logs in or out
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
        $customerId = $observer->getEvent()->getCustomer()->getId();

        /** @var PublicCookieMetadata $publicCookieMetadata */
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
             ->setDuration(3600)
             ->setPath('/')
             ->setDomain($domain)
             ->setHttpOnly(false);

        $this->cookieManager->setPublicCookie(
           "authentication_flag",
            $this->jsonSerializer->serialize(true),
            $publicCookieMetadata
        );

        $this->cookieManager->setPublicCookie(
            "dataservices_customer_id",
             $this->jsonSerializer->serialize($customerId),
             $publicCookieMetadata
         );

        try {
            $quote = $this->cartRepository->getForCustomer($customerId);
            if ($quote) {
                $cartId = $quote->getId();
                if ($cartId) {
                    $this->cookieManager->setPublicCookie(
                        "dataservices_cart_id",
                        $this->jsonSerializer->serialize($cartId),
                        $publicCookieMetadata
                    );
                }
            }
        } catch (NoSuchEntityException $e) {
            // Intentionally do nothing. This should gracefully fail if the customer does not have a cart.
        }

    }
}
