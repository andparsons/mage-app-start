<?php
namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\NegotiableQuote\Api\NegotiableCartRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Proxy methods call to CartRepositoryInterface.
 */
class NegotiableCartRepository implements NegotiableCartRepositoryInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        return $this->cartRepository->get($cartId);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        return $this->cartRepository->getList($searchCriteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getForCustomer($customerId, array $sharedStoreIds = [])
    {
        return $this->cartRepository->getForCustomer($customerId, $sharedStoreIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getActive($cartId, array $sharedStoreIds = [])
    {
        return $this->cartRepository->getActive($cartId, $sharedStoreIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveForCustomer($customerId, array $sharedStoreIds = [])
    {
        return $this->cartRepository->getActiveForCustomer($customerId, $sharedStoreIds);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CartInterface $quote)
    {
        $this->cartRepository->save($quote);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CartInterface $quote)
    {
        $this->cartRepository->delete($quote);
    }
}
