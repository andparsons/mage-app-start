<?php

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType;

/**
 * Filling Widget Options that have cms static block type.
 */
class CmsStaticBlock extends ParametersForm
{
    /**
     * Cms Page Link grid block.
     *
     * @var string
     */
    protected $gridBlock = './ancestor::body//*[contains(@id, "responseCntoptions_fieldset")]';

    /**
     * Path to grid.
     *
     * @var string
     */
    // @codingStandardsIgnoreStart
    protected $pathToGrid = \Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\ParametersType\CmsStaticBlock\Grid::class;
    // @codingStandardsIgnoreEnd
}
