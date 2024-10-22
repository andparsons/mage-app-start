<?php

namespace Magento\Config\Console\Command;

use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigShowCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var FileReader
     */
    private $reader;

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var array
     */
    private $env;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $envConfig;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->reader = $this->objectManager->get(FileReader::class);
        $this->writer = $this->objectManager->get(Writer::class);

        $this->config = $this->loadConfig();
        $this->envConfig = $this->loadEnvConfig();
        $this->env = $_ENV;

        $config = include __DIR__ . '/../../_files/config.php';
        $this->writer->saveConfig([ConfigFilePool::APP_CONFIG => $config]);

        $config = include __DIR__ . '/../../_files/env.php';
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => $config]);

        $_ENV['CONFIG__DEFAULT__WEB__TEST2__TEST_VALUE_4'] = 'value4.env.default.test';
        $_ENV['CONFIG__WEBSITES__BASE__WEB__TEST2__TEST_VALUE_4'] = 'value4.env.website_base.test';
        $_ENV['CONFIG__STORES__DEFAULT__WEB__TEST2__TEST_VALUE_4'] = 'value4.env.store_default.test';

        $command = $this->objectManager->create(ConfigShowCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @param string $scope
     * @param string $scopeCode
     * @param int $resultCode
     * @param array $configs
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Config/_files/config_data.php
     * @dataProvider executeDataProvider
     */
    public function testExecute($scope, $scopeCode, $resultCode, array $configs)
    {
        foreach ($configs as $inputPath => $configValue) {
            $arguments = [
                ConfigShowCommand::INPUT_ARGUMENT_PATH => $inputPath
            ];

            if ($scope !== null) {
                $arguments['--' . ConfigShowCommand::INPUT_OPTION_SCOPE] = $scope;
            }
            if ($scopeCode !== null) {
                $arguments['--' . ConfigShowCommand::INPUT_OPTION_SCOPE_CODE] = $scopeCode;
            }

            $this->commandTester->execute($arguments);

            $this->assertEquals(
                $resultCode,
                $this->commandTester->getStatusCode()
            );

            $commandOutput = $this->commandTester->getDisplay();
            foreach ($configValue as $value) {
                $this->assertContains($value, $commandOutput);
            }
        }
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function executeDataProvider()
    {
        return [
            [
                null,
                null,
                Cli::RETURN_SUCCESS,
                [
                    'web/test/test_value_1' => ['value1.db.default.test'],
                    'web/test/test_value_2' => ['value2.local_config.default.test'],
                    'web/test2/test_value_3' => ['value3.config.default.test'],
                    'web/test2/test_value_4' => ['value4.env.default.test'],
                    'carriers/fedex/account' => ['******'],
                    'paypal/fetch_reports/ftp_password' => ['******'],
                    'web/test' => [
                        'web/test/test_value_1 - value1.db.default.test',
                        'web/test/test_value_2 - value2.local_config.default.test',
                    ],
                    'web/test2' => [
                        'web/test2/test_value_3 - value3.config.default.test',
                        'web/test2/test_value_4 - value4.env.default.test',
                    ],
                    'web' => [
                        'web/test/test_value_1 - value1.db.default.test',
                        'web/test/test_value_2 - value2.local_config.default.test',
                        'web/test2/test_value_3 - value3.config.default.test',
                        'web/test2/test_value_4 - value4.env.default.test',
                    ],
                    '' => [
                        'web/test/test_value_1 - value1.db.default.test',
                        'web/test/test_value_2 - value2.local_config.default.test',
                        'web/test2/test_value_3 - value3.config.default.test',
                        'web/test2/test_value_4 - value4.env.default.test',
                        'carriers/fedex/account - ******',
                        'paypal/fetch_reports/ftp_password - ******',
                    ],
                ]
            ],
            [
                ScopeInterface::SCOPE_WEBSITES,
                'base',
                Cli::RETURN_SUCCESS,
                [
                    'web/test/test_value_1' => ['value1.db.website_base.test'],
                    'web/test/test_value_2' => ['value2.local_config.website_base.test'],
                    'web/test2/test_value_3' => ['value3.config.website_base.test'],
                    'web/test2/test_value_4' => ['value4.env.website_base.test'],
                    'web/test' => [
                        'web/test/test_value_1 - value1.db.website_base.test',
                        'web/test/test_value_2 - value2.local_config.website_base.test',
                    ],
                    'web/test2' => [
                        'web/test2/test_value_3 - value3.config.website_base.test',
                        'web/test2/test_value_4 - value4.env.website_base.test',
                    ],
                    'web' => [
                        'web/test/test_value_1 - value1.db.website_base.test',
                        'web/test/test_value_2 - value2.local_config.website_base.test',
                        'web/test2/test_value_3 - value3.config.website_base.test',
                        'web/test2/test_value_4 - value4.env.website_base.test',
                    ],
                    '' => [
                        'web/test/test_value_1 - value1.db.website_base.test',
                        'web/test/test_value_2 - value2.local_config.website_base.test',
                        'web/test2/test_value_3 - value3.config.website_base.test',
                        'web/test2/test_value_4 - value4.env.website_base.test',
                    ],
                ]
            ],
            [
                ScopeInterface::SCOPE_STORES,
                'default',
                Cli::RETURN_SUCCESS,
                [
                    'web/test/test_value_1' => ['value1.db.store_default.test'],
                    'web/test/test_value_2' => ['value2.local_config.store_default.test'],
                    'web/test2/test_value_3' => ['value3.config.store_default.test'],
                    'web/test2/test_value_4' => ['value4.env.store_default.test'],
                    'web/test' => [
                        'web/test/test_value_1 - value1.db.store_default.test',
                        'web/test/test_value_2 - value2.local_config.store_default.test',
                    ],
                    'web/test2' => [
                        'web/test2/test_value_3 - value3.config.store_default.test',
                        'web/test2/test_value_4 - value4.env.store_default.test',
                    ],
                    'web' => [
                        'web/test/test_value_1 - value1.db.store_default.test',
                        'web/test/test_value_2 - value2.local_config.store_default.test',
                        'web/test2/test_value_3 - value3.config.store_default.test',
                        'web/test2/test_value_4 - value4.env.store_default.test',
                    ],
                    '' => [
                        'web/test/test_value_1 - value1.db.store_default.test',
                        'web/test/test_value_2 - value2.local_config.store_default.test',
                        'web/test2/test_value_3 - value3.config.store_default.test',
                        'web/test2/test_value_4 - value4.env.store_default.test',
                    ],
                ]
            ],
            [
                null,
                null,
                Cli::RETURN_FAILURE,
                [
                    'web/test/test_wrong_value' => [
                        'Configuration for path: "web/test/test_wrong_value" doesn\'t exist'
                    ],
                ]
            ],
            [
                'default',
                null,
                Cli::RETURN_FAILURE,
                [
                    'web/test/test_wrong_value' => [
                        'Configuration for path: "web/test/test_wrong_value" doesn\'t exist'
                    ],
                ]
            ],
            [
                'default',
                'scope_code',
                Cli::RETURN_FAILURE,
                [
                    'web/test/test_wrong_value' => [
                        'The "default" scope can\'t include a scope code. Try again without entering a scope code.'
                    ],
                ]
            ],
            [
                'some_scope',
                'scope_code',
                Cli::RETURN_FAILURE,
                [
                    'web/test/test_wrong_value' => [
                        'The "some_scope" value doesn\'t exist. Enter another value and try again.'
                    ],
                ]
            ],
            [
                'websites',
                'scope_code',
                Cli::RETURN_FAILURE,
                [
                    'web/test/test_wrong_value' => [
                        'The "scope_code" value doesn\'t exist. Enter another value and try again.'
                    ],
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    private function loadConfig()
    {
        return $this->reader->load(ConfigFilePool::APP_CONFIG);
    }

    /**
     * @return array
     */
    private function loadEnvConfig()
    {
        return $this->reader->load(ConfigFilePool::APP_ENV);
    }

    public function tearDown()
    {
        $_ENV = $this->env;

        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG),
            "<?php\n return array();\n"
        );
        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );

        $this->writer->saveConfig([ConfigFilePool::APP_CONFIG => $this->config]);
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => $this->envConfig]);
    }
}
