<?php

namespace Magento\Sales\Model\Order\Invoice\Comment;

use Magento\Sales\Model\Order\Invoice\Comment;

/**
 * Class Validator
 */
class Validator
{
    /**
     * Required field
     *
     * @var array
     */
    protected $required = [
        'parent_id' => 'Parent Invoice Id',
        'comment' => 'Comment',
    ];

    /**
     * Validate data
     *
     * @param \Magento\Sales\Model\Order\Invoice\Comment $comment
     * @return array
     */
    public function validate(Comment $comment)
    {
        $errors = [];
        $commentData = $comment->getData();
        foreach ($this->required as $code => $label) {
            if (!$comment->hasData($code)) {
                $errors[$code] = sprintf('"%s" is required. Enter and try again.', $label);
            } elseif (empty($commentData[$code])) {
                $errors[$code] = sprintf('%s can not be empty', $label);
            }
        }

        return $errors;
    }
}
