<?php
namespace Magento\CheckoutAgreements\Model;

/**
 * Interface AgreementsProviderInterface
 */
interface AgreementsProviderInterface
{
    /**
     * Get list of Required Agreement Ids
     *
     * @return int[]
     */
    public function getRequiredAgreementIds();
}
