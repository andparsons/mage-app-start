<?php

namespace Magento\EncryptionKey\Controller\Adminhtml\Crypt;

/**
 * Encryption key changer controller
 */
abstract class Key extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_EncryptionKey::crypt_key';
}
