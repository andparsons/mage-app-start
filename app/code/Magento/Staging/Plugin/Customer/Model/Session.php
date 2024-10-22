<?php
namespace Magento\Staging\Plugin\Customer\Model;

/**
 * Plugin for customer session model.
 */
class Session
{
    /**
     * @var \Magento\Staging\Model\VersionManager
     */
    private $versionManager;

    /**
     * @param \Magento\Staging\Model\VersionManager $versionManager
     */
    public function __construct(
        \Magento\Staging\Model\VersionManager $versionManager
    ) {
        $this->versionManager = $versionManager;
    }

    /**
     * @param \Magento\Customer\Model\Session $subject
     * @param \Closure $proceed
     *
     * @return \Magento\Customer\Model\Session
     */
    public function aroundRegenerateId(
        \Magento\Customer\Model\Session $subject,
        \Closure $proceed
    ) {
        if ($this->versionManager->isPreviewVersion()) {
            return $subject;
        }

        return $proceed();
    }

    /**
     * @param \Magento\Customer\Model\Session $subject
     * @param \Closure $proceed
     * @param array $options
     *
     * @return \Magento\Customer\Model\Session
     */
    public function aroundDestroy(
        \Magento\Customer\Model\Session $subject,
        \Closure $proceed,
        array $options
    ) {
        if ($this->versionManager->isPreviewVersion()) {
            return $subject;
        }

        return $proceed($options);
    }
}
