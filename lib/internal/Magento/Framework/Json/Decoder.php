<?php
namespace Magento\Framework\Json;

/**
 * @deprecated 101.0.0 @see \Magento\Framework\Serialize\Serializer\Json::unserialize
 */
class Decoder implements DecoderInterface
{
    /**
     * Decodes the given $data string which is encoded in the JSON format.
     *
     * @param string $data
     * @return mixed
     */
    public function decode($data)
    {
        return \Zend_Json::decode($data);
    }
}
