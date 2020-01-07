<?php
namespace Magento\CheckoutStaging\Model;

class PreviewQuotaRepository
{
    /**
     * @var ResourceModel\PreviewQuota
     */
    private $previewQuotaResource;

    /**
     * PreviewQuotaRepository constructor.
     *
     * @param ResourceModel\PreviewQuota $previewQuotaResource
     */
    public function __construct(
        ResourceModel\PreviewQuota $previewQuotaResource
    ) {
        $this->previewQuotaResource = $previewQuotaResource;
    }

    /**
     * @param PreviewQuota $previewQuota
     * @return void
     */
    public function save(PreviewQuota $previewQuota)
    {
        $this->previewQuotaResource->insert($previewQuota->getId());
    }
}
