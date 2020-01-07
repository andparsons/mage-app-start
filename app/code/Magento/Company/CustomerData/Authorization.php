<?php
namespace Magento\Company\CustomerData;

use Magento\Company\Api\AuthorizationInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Company authorization section.
 */
class Authorization implements SectionSourceInterface
{
    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var array
     */
    private $authorizationResources;

    /**
     * @param AuthorizationInterface $authorization
     * @param array $authorizationResources
     */
    public function __construct(
        AuthorizationInterface $authorization,
        $authorizationResources = []
    ) {
        $this->authorization = $authorization;
        $this->authorizationResources = $authorizationResources;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        return [
            'resources' => $this->getAuthorizationResourcesStates()
        ];
    }

    /**
     * Get authorization resources states.
     *
     * @return array
     */
    private function getAuthorizationResourcesStates()
    {
        $authorizationResourcesStatus = [];
        foreach ($this->authorizationResources as $resource) {
            $authorizationResourcesStatus[$resource] = $this->authorization->isAllowed($resource);
        }

        return $authorizationResourcesStatus;
    }
}
