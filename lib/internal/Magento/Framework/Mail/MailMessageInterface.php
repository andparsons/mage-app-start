<?php
namespace Magento\Framework\Mail;

/**
 * Mail Message interface
 *
 * @api
 * @since 101.0.8
 */
interface MailMessageInterface extends MessageInterface
{
    /**
     * Set mail message body in HTML format.
     *
     * @param string $html
     * @return $this
     * @since 101.0.8
     */
    public function setBodyHtml($html);

    /**
     * Set mail message body in text format.
     *
     * @param string $text
     * @return $this
     * @since 101.0.8
     */
    public function setBodyText($text);

    /**
     * Get message source code.
     *
     * @return string
     * @since 101.0.8
     */
    public function getRawMessage();
}
