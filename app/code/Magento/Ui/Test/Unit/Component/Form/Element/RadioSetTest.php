<?php
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\RadioSet;

/**
 * Class RadioSetTest
 *
 * @method RadioSet getModel
 */
class RadioSetTest extends AbstractElementTest
{
    /**
     * @inheritdoc
     */
    protected function getModelName()
    {
        return RadioSet::class;
    }

    /**
     * @inheritdoc
     */
    public function testGetComponentName()
    {
        $this->assertSame(RadioSet::NAME, $this->getModel()->getComponentName());
    }
}
