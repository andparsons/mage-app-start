<?php
declare(strict_types = 1);

namespace Magento\DataServices\ViewModel;

use Magento\Customer\Model\Session as PersonalizationSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for WishListProvider
 */
class WishListProvider implements ArgumentInterface
{
    /**
     * @var PersonalizationSession
     */
    private $personalizationSession;

    /**
     * @param PersonalizationSession $personalizationSession
     */
    public function __construct(
      PersonalizationSession $personalizationSession
    ) {
        $this->personalizationSession = $personalizationSession;
    }

    /**
     * Return User Action
     *
     * @return string
     */
    public function getUserAction() : string
    {
        $userAction = $this->personalizationSession->getUserAction();
        $this->personalizationSession->unsUserAction();
        return $userAction ? $userAction : '';
    }

    /**
     * Return Product Context
     *
     * @return array
     */
    public function getModelContext() : array
    {
        $productContext = $this->personalizationSession->getProductContext();
        $this->personalizationSession->unsProductContext();
        return $productContext ? $productContext : [];
    }
}
