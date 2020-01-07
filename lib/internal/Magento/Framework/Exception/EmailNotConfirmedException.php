<?php
namespace Magento\Framework\Exception;

/**
 * @api
 * @since 100.0.2
 */
class EmailNotConfirmedException extends AuthenticationException
{
    /**
     * @deprecated
     */
    const EMAIL_NOT_CONFIRMED = 'Email not confirmed';
}
