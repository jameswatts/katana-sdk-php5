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

namespace Katana\Sdk;

/**
 * Interface to the Transport object contained in a payload
 * @package Katana\Sdk
 */
interface Transport
{
    /**
     * Return the uuid of the request
     *
     * @return string
     */
    public function getRequestId();

    /**
     * Return the creation datetime of the request
     *
     * @return string
     */
    public function getRequestTimestamp();

    /**
     * Return the name of the service that was the origin of the request
     *
     * @return string
     */
    public function getOrigin();

    /**
     * Get a custom userland property
     *
     * The default value will be returned if the property does not exist
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getProperty($name, $default = '');

    /**
     * Get all userland properties as an array
     *
     * @return array
     */
    public function getProperties();

    /**
     * Determine if a file download has been registered for the HTTP response
     *
     * @return boolean
     */
    public function hasDownload();

    /**
     * Return the file download defined for the HTTP response
     *
     * @return File
     */
    public function getDownload();

    /**
     * Return the data stored in the Transport
     *
     * If the optional "service" argument is specified, only data stored under
     * that service is returned
     *
     * If the optional "version" argument is specified, only data stored under
     * that service and version is returned
     *
     * If the optional "action" argument is specified, only data stored under
     * that service, version and action is returned
     *
     * @param string $service
     * @param string $version
     * @param string $action
     * @return array
     */
    public function getData($service = '', $version = '', $action = '');

    /**
     * Return all the relations stored in the Transport
     *
     * If the optional "service" argument is specified, only relations under
     * that service are returned
     *
     * @param string $service
     * @return array
     */
    public function getRelations($service = '');

    /**
     * Return all the links stored in the Transport
     *
     * If the optional "service" argument is specified, only links under that
     * service are returned
     *
     * @param string $service
     * @return array
     */
    public function getLinks($service = '');

    /**
     * Return all the calls stored in the Transport
     *
     * If the optional "service" argument is specified, only calls under that
     * service are returned
     *
     * @param string $service
     * @return array
     */
    public function getCalls($service = '');

    /**
     * Return all the transactions stored in the Transport
     *
     * If the optional "service" argument is specified, only transactions under
     * that service are returned
     *
     * @param string $service
     * @return array
     */
    public function getTransactions($service = '');

    /**
     * Return all the errors stored in the Transport
     *
     * If the optional "service" argument is specified, only errors under
     * that service are returned
     *
     * @param string $service
     * @return array
     */
    public function getErrors($service = '');
}
