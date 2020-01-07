<?php

declare(strict_types=1);

namespace Magento\NegotiableQuote\Model\Quote;

use Magento\Framework\Phrase;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Area;

/**
 * @inheritDoc
 *
 * Implementation for front-end area.
 */
class ViewAccess implements ViewAccessInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var RestrictionInterface
     */
    private $restriction;

    /**
     * @param StoreManagerInterface $storeManager
     * @param State $appState
     * @param RestrictionInterface $restriction
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        State $appState,
        RestrictionInterface $restriction
    ) {
        $this->storeManager = $storeManager;
        $this->appState = $appState;
        $this->restriction = $restriction;
    }

    /**
     * @inheritDoc
     */
    public function canViewQuote(CartInterface $quote): bool
    {
        if ($this->appState->getAreaCode() !== Area::AREA_FRONTEND) {
            throw new LocalizedException(
                new Phrase('Only for use within frontend area')
            );
        }

        $this->restriction->setQuote($quote);
        //Checking access.
        $result = $this->restriction->isAllowed(
            'Magento_NegotiableQuote::view_quotes'
        );
        $result = $result
            && (
                $this->restriction->isOwner()
                || (
                    $this->restriction->isAllowed(
                        'Magento_NegotiableQuote::view_quotes_sub'
                    )
                    && $this->restriction->isSubUserContent()
                )
            );
        $result = $result
            && in_array(
                $quote->getStoreId(),
                $this->storeManager->getWebsite()->getStoreIds()
            );

        return $result;
    }
}
