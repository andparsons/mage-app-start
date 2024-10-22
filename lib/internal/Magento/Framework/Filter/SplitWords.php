<?php
namespace Magento\Framework\Filter;

/**
 * Split words
 */
class SplitWords implements \Zend_Filter_Interface
{
    /**
     * @var bool
     */
    protected $uniqueOnly;

    /**
     * @var int
     */
    protected $wordsQty;

    /**
     * @var string
     */
    protected $wordSeparatorRegexp;

    /**
     * @param bool $uniqueOnly Unique words only
     * @param int $wordsQty Limit words qty in result
     * @param string $wordSeparatorRegexp
     */
    public function __construct($uniqueOnly = true, $wordsQty = 0, $wordSeparatorRegexp = '\s')
    {
        $this->uniqueOnly = $uniqueOnly;
        $this->wordsQty = $wordsQty;
        $this->wordSeparatorRegexp = $wordSeparatorRegexp;
    }

    /**
     * Filter value
     *
     * @param string $str The source string
     * @return array
     */
    public function filter($str)
    {
        $result = [];
        $split = preg_split('#' . $this->wordSeparatorRegexp . '#siu', $str, null, PREG_SPLIT_NO_EMPTY);
        foreach ($split as $word) {
            if ($this->uniqueOnly) {
                $result[$word] = $word;
            } else {
                $result[] = $word;
            }
        }
        if ($this->wordsQty && count($result) > $this->wordsQty) {
            $result = array_slice($result, 0, $this->wordsQty);
        }
        return $result;
    }
}
