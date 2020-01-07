<?php
namespace Magento\NegotiableQuote\Ui\Component\Filters\Quote;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Company\Api\AuthorizationInterface;
use Magento\Company\Model\Company\Structure;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\AbstractFilter;

/**
 * Filter quotes by current customer.
 */
class CurrentCustomer extends AbstractFilter
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var Structure
     */
    private $structure;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param UserContextInterface $userContext
     * @param Structure $structure
     * @param AuthorizationInterface $authorization
     * @param array $components [optional]
     * @param array $data [optional]
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        UserContextInterface $userContext,
        Structure $structure,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $filterBuilder, $filterModifier, $components, $data);
        $this->userContext = $userContext;
        $this->structure = $structure;
        $this->authorization = $authorization;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = (array)$this->getData('config');
        $config['visible'] = $this->isVisible();
        $this->setData('config', (array)$config);

        $this->applyFilter();

        parent::prepare();
    }

    /**
     * Apply filter on data provider.
     *
     * @return void
     */
    private function applyFilter()
    {
        if (isset($this->filterData[$this->getName()])) {
            $value = $this->filterData[$this->getName()];

            if ($value) {
                $filter = $this->filterBuilder->setConditionType('eq')
                    ->setField('customer_id')
                    ->setValue($this->userContext->getUserId())
                    ->create();

                $this->getContext()->getDataProvider()->addFilter($filter);
            }
        }
    }

    /**
     * Show filter if company customer has permission to view quotes of subordinates.
     *
     * @return bool
     */
    private function isVisible()
    {
        $customerId = $this->userContext->getUserId();

        $subCustomers = $this->structure->getAllowedChildrenIds($customerId);
        $isAllowed = $this->authorization->isAllowed('Magento_NegotiableQuote::view_quotes_sub');

        return !empty($subCustomers) && $isAllowed;
    }
}
