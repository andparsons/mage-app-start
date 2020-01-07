<?php
namespace Magento\NegotiableQuote\Block\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;

/**
 * NegotiableQuote system config array field renderer
 */
class DefaultExpirationPeriod extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Select ID
     */
    const SELECT_ID_EXPIRATION_PERIOD = 'quote_general_default_expiration_period_time';

    /**
     * Select Name
     */
    const SELECT_NAME_EXPIRATION_PERIOD_TIME = 'groups[general][fields][default_expiration_period_time][value]';

    /**
     * Default expiration period time
     */
    const DEFAULT_EXPIRATION_PERIOD_TIME = 'quote/general/default_expiration_period_time';

    /**
     * Default expiration period count
     */
    const DEFAULT_EXPIRATION_PERIOD_COUNT = 'quote/general/default_expiration_period';

    /**
     * Default expiration period
     */
    public static $defaultExpirationPeriod = [
        'day' => 'days',
        'week' => 'weeks',
        'month' => 'months',
    ];

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $labelFactory;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $context;

    /**
     * @param Context $context
     * @param Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->context = $context;
        $this->elementFactory = $elementFactory;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setName($element->getName() . '[]');
        $expirationTime = $element->setType('text')->setStyle(
            'width:80px;'
        )->getElementHtml();

        $select = $this->getSelectField();
        $expirationPeriod = $select->setStyle(
            'width:150px;'
        )->setValues(
            self::$defaultExpirationPeriod
        )->getElementHtml();

        return sprintf('%s %s', $expirationTime, $expirationPeriod);
    }

    /**
     * Add select field to expiration date
     *
     * @return AbstractElement
     */
    protected function getSelectField()
    {
        $expirationPeriodTime = $this->_scopeConfig->getValue(
            self::DEFAULT_EXPIRATION_PERIOD_TIME,
            ScopeInterface::SCOPE_WEBSITE
        );
        /** @var \Magento\Framework\Data\Form\Element\Select $field */
        $select = $this->elementFactory->create('select');
        $select->setForm(
            $this->getForm()
        )->setName(
            self::SELECT_NAME_EXPIRATION_PERIOD_TIME . '[]'
        )->setHtmlId(
            self::SELECT_ID_EXPIRATION_PERIOD
        )->setValues(
            self::$defaultExpirationPeriod
        )->setValue(
            isset($expirationPeriodTime) ? $expirationPeriodTime : null
        );

        return $select;
    }
}
