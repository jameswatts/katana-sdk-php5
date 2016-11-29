<?php
/**
 * PHP 5 SDK for the KATANA(tm) Platform (http://katana.kusanagi.io)
 * Copyright (c) 2016-2017 KUSANAGI S.L. All rights reserved.
 *
 * Distributed under the MIT license
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 *
 * @link      https://github.com/kusanagi/katana-sdk-php5
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @copyright Copyright (c) 2016-2017 KUSANAGI S.L. (http://kusanagi.io)
 */

namespace Katana\Sdk\Component;

use Katana\Sdk\Api\ApiLoggerTrait;
use Katana\Sdk\Api\Factory\ApiFactory;
use Katana\Sdk\Api\Mapper\CompactPayloadMapper;
use Katana\Sdk\Api\Mapper\ExtendedPayloadMapper;
use Katana\Sdk\Api\Mapper\PayloadMapperInterface;
use Katana\Sdk\Console\CliInput;
use Katana\Sdk\Exception\ConsoleException;
use Katana\Sdk\Executor\AbstractExecutor;
use Katana\Sdk\Executor\ExecutorFactory;
use Katana\Sdk\Logger\KatanaLogger;

/**
 * Base class for Components
 *
 * @package Katana\Sdk\Component
 */
abstract class AbstractComponent
{
    use ApiLoggerTrait;

    /**
     * @var CliInput
     */
    protected $input;

    /**
     * @var AbstractExecutor
     */
    protected $executor;

    /**
     * @var ApiFactory
     */
    protected $apiFactory;

    /**
     * @var KatanaLogger
     */
    protected $logger;

    /**
     * @var callback[]
     */
    private $callbacks = [];

    /**
     * @var array
     */
    private $resources = [];

    /**
     * @var callable
     */
    private $startup;

    /**
     * @var callable
     */
    private $error;

    /**
     * @var callable
     */
    private $shutdown;

    public function __construct()
    {
        $this->input = CliInput::createFromCli();

        if ($this->input->isQuiet()) {
            $level = KatanaLogger::LOG_NONE;
        } elseif ($this->input->isDebug()) {
            $level = KatanaLogger::LOG_DEBUG;
        } else {
            $level = KatanaLogger::LOG_INFO;
        }
        $this->logger = new KatanaLogger($level);

        $mapper = $this->input->getMapping() === 'compact'
            ? new CompactPayloadMapper()
            : new ExtendedPayloadMapper();
        $this->apiFactory = $this->getApiFactory($mapper);

        $executorFactory = new ExecutorFactory($mapper, $this->logger);
        $this->executor = $executorFactory->build($this->input);
    }

    /**
     * @param string $name
     * @param callable $callback
     */
    protected function setCallback($name, callable $callback)
    {
        $this->callbacks[$name] = $callback;
    }

    /**
     * Run the SDK.
     */
    public function run()
    {
        if ($this->startup) {
            $this->logger->debug('Calling startup callback');
            call_user_func($this->startup, $this);
        }

        $actions = implode(', ', array_keys($this->callbacks));
        $this->logger->info("Component running with callbacks for $actions");
        $this->executor->execute(
            $this->apiFactory,
            $this->input,
            $this->callbacks,
            $this->error
        );

        if ($this->shutdown) {
            $this->logger->debug('Calling shutdown callback');
            call_user_func($this->shutdown, $this);
        }
    }

    /**
     * @param PayloadMapperInterface $mapper
     * @return ApiFactory
     */
    abstract protected function getApiFactory(PayloadMapperInterface $mapper);

    /**
     * @param string $name
     * @param callable $resource
     * @return bool
     * @throws ConsoleException
     */
    public function setResource($name, callable $resource)
    {
        $resource = $resource();
        if (!$resource) {
            $msg = "Set resource $name failed";
            $this->logger->error($msg);
            throw new ConsoleException($msg);
        }

        $this->logger->info("Setting $name resource");
        $this->resources[$name] = $resource;

        return true;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws ConsoleException
     */
    public function getResource($name)
    {
        if (!$this->hasResource($name)) {
            $msg = "Resource $name not found";
            $this->logger->error($msg);
            throw new ConsoleException($msg);
        }

        return $this->resources[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasResource($name)
    {
        return isset($this->resources[$name]);
    }

    /**
     * @param callable $startup
     * @return bool
     */
    public function startup(callable $startup)
    {
        $this->startup = $startup;

        return true;
    }

    /**
     * @param callable $shutdown
     * @return bool
     */
    public function shutdown(callable $shutdown)
    {
        $this->shutdown = $shutdown;

        return true;
    }

    /**
     * @param callable $error
     * @return bool
     */
    public function error(callable $error)
    {
        $this->error = $error;

        return true;
    }
}
