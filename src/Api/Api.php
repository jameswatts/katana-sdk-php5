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

namespace Katana\Sdk\Api;
use Katana\Sdk\Component\Component;
use Katana\Sdk\Logger\KatanaLogger;
use Katana\Sdk\Schema\Mapping;
use Katana\Sdk\Schema\ServiceSchema;

/**
 * Base class for Api classes.
 *
 * @package Katana\Sdk\Api
 */
abstract class Api
{
    use ApiLoggerTrait;

    /**
     * @var Component
     */
    protected $component;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $frameworkVersion;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var Mapping
     */
    protected $mapping;

    /**
     * @param KatanaLogger $logger
     * @param Component $component
     * @param Mapping $mapping
     * @param string $path
     * @param string $name
     * @param string $version
     * @param string $frameworkVersion
     * @param array $variables
     * @param bool $debug
     */
    public function __construct(
        KatanaLogger $logger,
        Component $component,
        Mapping $mapping,
        $path,
        $name,
        $version,
        $frameworkVersion,
        array $variables = [],
        $debug = false
    ) {
        $this->logger = $logger;
        $this->component = $component;
        $this->mapping = $mapping;
        $this->path = $path;
        $this->name = $name;
        $this->version = $version;
        $this->frameworkVersion = $frameworkVersion;
        $this->variables = $variables;
        $this->debug = $debug;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getFrameworkVersion()
    {
        return $this->frameworkVersion;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param $name
     * @return string
     */
    public function getVariable($name)
    {
        if (!isset($this->variables[$name])) {
            return '';
        }

        return $this->variables[$name];
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasResource($name)
    {
        return $this->component->hasResource($name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getResource($name)
    {
        return $this->component->getResource($name);
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return array_map(function (ServiceSchema $service) {
            return [
                'service' => $service->getName(),
                'version' => $service->getVersion(),
            ];
        }, $this->mapping->getAll());
    }

    /**
     * @param string $name
     * @param string $version
     * @return ServiceSchema
     */
    public function getServiceSchema($name, $version)
    {
        return $this->mapping->find($name, $version);
    }
}
