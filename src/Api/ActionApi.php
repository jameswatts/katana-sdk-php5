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

use Katana\Sdk\Action;
use Katana\Sdk\Api\Value\VersionString;
use Katana\Sdk\Component\AbstractComponent;
use Katana\Sdk\Exception\TransportException;
use Katana\Sdk\Logger\KatanaLogger;

class ActionApi extends Api implements Action
{
    /**
     * @var string
     */
    private $actionName;

    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var array
     */
    private $params = [];

    /**
     * Action constructor.
     * @param KatanaLogger $logger
     * @param AbstractComponent $component
     * @param string $path
     * @param string $name
     * @param string $version
     * @param string $platformVersion
     * @param array $variables
     * @param bool $debug
     * @param string $actionName
     * @param Transport $transport
     * @param Param[] $params
     */
    public function __construct(
        KatanaLogger $logger,
        AbstractComponent $component,
        $path,
        $name,
        $version,
        $platformVersion,
        array $variables,
        $debug,
        $actionName,
        Transport $transport,
        array $params = []
    ) {
        parent::__construct(
            $logger,
            $component,
            $path,
            $name,
            $version,
            $platformVersion,
            $variables,
            $debug
        );

        $this->actionName = $actionName;
        $this->transport = $transport;

        foreach ($params as $param) {
            $this->params[$param->getLocation()][$param->getName()] = $param;
        }
    }

    /**
     * @return Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return bool
     */
    public function isOrigin()
    {
        return $this->transport->getMeta()->getOrigin() === $this->name;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setProperty($name, $value)
    {
        $this->transport->getMeta()->setProperty($name, $value);
    }

    /**
     * @param string $name
     * @param string $location
     * @return Param
     */
    public function getParam($name, $location = 'query')
    {
        if (isset($this->params[$location][$name])) {
            return $this->params[$location][$name];
        }

        return new Param($name, $location);
    }

    /**
     * @param string $location
     * @return Param[]
     */
    public function getParams($location = null)
    {
        if ($location) {
            if (!isset($this->params[$location])) {
                return [];
            }

            return $this->params[$location];
        }

        $params = [];
        foreach ($this->params as $location => $locationParams) {
            foreach ($locationParams as $name => $param) {
                $params[$name] = $param;
            }
        }

        return $params;
    }

    /**
     * @param string $name
     * @param string $location
     * @param string $value
     * @param string $type
     * @return Param
     */
    public function newParam($name, $location = 'query', $value = '', $type = Param::TYPE_STRING)
    {
        return new Param($name, $location, $value, $type);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasFile($name)
    {
        return $this->transport->hasFile($this->name, $this->version, $this->actionName, $name);
    }

    /**
     * @param string $name
     * @return File
     */
    public function getFile($name)
    {
        if ($this->transport->hasFile($this->name, $this->version, $this->actionName, $name)) {
            return $this->transport->getFile($this->name, $this->version, $this->actionName, $name);
        } else {
            return $this->newFile($name, '');
        }
    }

    /**
     * @return File[]
     */
    public function getFiles()
    {
        $files = [];

        foreach ($this->transport->getFiles()->getAll() as $serviceFiles) {
            foreach ($serviceFiles as $versionFiles) {
                foreach ($versionFiles as $actionFiles) {
                    $files = array_merge($files, array_values($actionFiles));
                }
            }
        }

        return $files;
    }

    /**
     * @param string $name
     * @param string $path
     * @param string $mime
     * @return File
     */
    public function newFile($name, $path, $mime = '')
    {
        return new File($name, $path, $mime);
    }

    /**
     * @param File $file
     */
    public function setDownload(File $file)
    {
        $this->transport->setBody($file);
    }

    /**
     * @param array $entity
     * @throws TransportException
     */
    public function setEntity(array $entity)
    {
        if (count(preg_grep('/^\d+$/', array_keys($entity)))) {
            throw new TransportException('Unexpected collection');
        }

        $this->transport->setData($this->name, $this->version, $this->actionName, $entity);
    }

    /**
     * @param array $collection
     * @throws TransportException
     */
    public function setCollection(array $collection)
    {
        if (count(preg_grep('/^\d+$/', array_keys($collection))) < count($collection)) {
            throw new TransportException('Unexpected entity');
        }

        $this->transport->setCollection($this->name, $this->version, $this->actionName, $collection);
    }

    /**
     * @param string $primaryKey
     * @param string $service
     * @param string $foreignKey
     */
    public function relateOne($primaryKey, $service, $foreignKey)
    {
        $this->transport->addSimpleRelation($this->name, $primaryKey, $service, $foreignKey);
    }

    /**
     * @param string $primaryKey
     * @param string $service
     * @param array $foreignKeys
     */
    public function relateMany($primaryKey, $service, array $foreignKeys)
    {
        $this->transport->addMultipleRelation($this->name, $primaryKey, $service, $foreignKeys);
    }

    /**
     * @param string $link
     * @param string $uri
     */
    public function setLink($link, $uri)
    {
        $this->transport->setLink($this->name, $link, $uri);
    }

    /**
     * @param string $action
     * @param array $params
     * @return boolean
     */
    public function commit($action, $params = [])
    {
        $this->transport->addTransaction(
            new Transaction(
                'commit',
                new ServiceOrigin($this->name, $this->version),
                $action,
                $params
            )
        );
    }

    /**
     * @param string $action
     * @param array $params
     * @return boolean
     */
    public function rollback($action, $params = [])
    {
        $this->transport->addTransaction(
            new Transaction(
                'rollback',
                new ServiceOrigin($this->name, $this->version),
                $action,
                $params
            )
        );
    }

    /**
     * @param string $service
     * @param string $version
     * @param string $action
     * @param Param[] $params
     * @param File[] $files
     */
    public function call(
        $service,
        $version,
        $action,
        array $params = [],
        array $files = []
    ) {
        $versionString = new VersionString($version);
        $this->transport->addCall(new Call(
            new ServiceOrigin($this->name, $this->version),
            $service,
            $versionString,
            $action,
            $params
        ));

        foreach ($files as $file) {
            $this->transport->addFile($service, $versionString, $action, $file);
        }
    }

    /**
     * @param string $message
     * @param int $code
     * @param string $status
     */
    public function error($message, $code = 0, $status = '')
    {
        $this->transport->addError(new Error($this->name, $this->version, $message, $code, $status));
    }
}
