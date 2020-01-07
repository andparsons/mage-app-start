<?php

namespace Magento\NegotiableQuote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for quote comment attachment.
 *
 * @api
 * @since 100.0.0
 */
interface CommentAttachmentInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants
     */
    const ATTACHMENT_ID = 'attachment_id';
    const COMMENT_ID    = 'comment_id';
    const FILE_NAME     = 'file_name';
    const FILE_PATH     = 'file_path';
    const FILE_TYPE     = 'file_type';
    /**#@-*/

    /**
     * Get attachment ID.
     *
     * @return int
     */
    public function getAttachmentId();

    /**
     * Set attachment ID.
     *
     * @param int $id
     * @return $this
     */
    public function setAttachmentId($id);

    /**
     * Get comment ID.
     *
     * @return int
     */
    public function getCommentId();

    /**
     * Set comment ID.
     *
     * @param int $id
     * @return $this
     */
    public function setCommentId($id);

    /**
     * Get file name.
     *
     * @return string
     */
    public function getFileName();

    /**
     * Set file name.
     *
     * @param string $name
     * @return $this
     */
    public function setFileName($name);

    /**
     * Get file path.
     *
     * @return string
     */
    public function getFilePath();

    /**
     * Set file path.
     *
     * @param string $path
     * @return $this
     */
    public function setFilePath($path);

    /**
     * Get file type.
     *
     * @return string
     */
    public function getFileType();

    /**
     * Set file type.
     *
     * @param string $type
     * @return $this
     */
    public function setFileType($type);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\NegotiableQuote\Api\Data\CommentAttachmentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NegotiableQuote\Api\Data\CommentAttachmentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\CommentAttachmentExtensionInterface $extensionAttributes
    );
}
