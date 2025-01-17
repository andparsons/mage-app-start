<?php
namespace Magento\ProductLinksSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\ProductLinksSampleData\Model\ProductLink
     */
    protected $productLink;

    /**
     * @param \Magento\ProductLinksSampleData\Model\ProductLink $productLink
     */
    public function __construct(
        \Magento\ProductLinksSampleData\Model\ProductLink $productLink
    ) {
        $this->productLink = $productLink;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->productLink->install(
            ['Magento_ProductLinksSampleData::fixtures/related.csv'],
            ['Magento_ProductLinksSampleData::fixtures/upsell.csv'],
            ['Magento_ProductLinksSampleData::fixtures/crosssell.csv']
        );
    }
}
