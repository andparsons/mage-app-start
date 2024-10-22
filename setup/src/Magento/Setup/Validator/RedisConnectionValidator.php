<?php

namespace Magento\Setup\Validator;

/**
 * Connection validator for Redis configurations
 */
class RedisConnectionValidator
{
    /**
     * Validate redis connection
     *
     * @param array $redisOptions
     * @return bool
     */
    public function isValidConnection(array $redisOptions)
    {
        $default = [
            'host' => '',
            'port' => '',
            'db' => '',
            'password' => null,
            'timeout' => null,
            'persistent' => ''
        ];

        $config = array_merge($default, $redisOptions);

        try {
            $redisClient = new \Credis_Client(
                $config['host'],
                $config['port'],
                $config['timeout'],
                $config['persistent'],
                $config['db'],
                $config['password']
            );
            $redisClient->setMaxConnectRetries(1);
            $redisClient->connect();
        } catch (\CredisException $e) {
            return false;
        }

        return true;
    }
}
