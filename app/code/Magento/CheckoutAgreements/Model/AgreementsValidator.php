<?php
namespace Magento\CheckoutAgreements\Model;

/**
 * Class AgreementsValidator
 */
class AgreementsValidator implements \Magento\Checkout\Api\AgreementsValidatorInterface
{
    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementsProviderInterface[]
     */
    protected $agreementsProviders;

    /**
     * @param AgreementsProviderInterface[] $list
     * @codeCoverageIgnore
     */
    public function __construct($list = null)
    {
        $this->agreementsProviders = (array) $list;
    }

    /**
     * Validate that all required agreements is signed
     *
     * @param int[] $agreementIds
     * @return bool
     */
    public function isValid($agreementIds = [])
    {
        $agreementIds = $agreementIds === null ? [] : $agreementIds;
        $requiredAgreements = [];
        foreach ($this->agreementsProviders as $agreementsProvider) {
            $requiredAgreements = array_merge($requiredAgreements, $agreementsProvider->getRequiredAgreementIds());
        }
        $agreementsDiff = array_diff($requiredAgreements, $agreementIds);
        return empty($agreementsDiff);
    }
}
