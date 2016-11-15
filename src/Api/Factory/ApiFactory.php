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

namespace Katana\Sdk\Api\Factory;

use Katana\Sdk\Api\Api;
use Katana\Sdk\Api\Mapper\PayloadReaderInterface;
use Katana\Sdk\Component\AbstractComponent;
use Katana\Sdk\Console\CliInput;

/**
 * Provides methods to get factories for any Api class.
 * @package Katana\Sdk\Api\Factory
 */
abstract class ApiFactory
{
    /**
     * @var AbstractComponent
     */
    protected $component;

    /**
     * Read mapper to translate an input into Api instances.
     *
     * @var PayloadReaderInterface
     */
    protected $mapper;

    /**
     * @param AbstractComponent $component
     * @param PayloadReaderInterface $mapper
     * @return ServiceApiFactory
     */
    public static function getServiceFactory(
        AbstractComponent $component,
        PayloadReaderInterface $mapper
    ) {
        return new ServiceApiFactory($component, $mapper);
    }

    /**
     * @param AbstractComponent $component
     * @param PayloadReaderInterface $mapper
     * @return MiddlewareApiFactory
     */
    public static function getMiddlewareFactory(
        AbstractComponent $component,
        PayloadReaderInterface $mapper
    ) {
        return new MiddlewareApiFactory($component, $mapper);
    }

    /**
     * @param AbstractComponent $component
     * @param PayloadReaderInterface $mapper
     */
    public function __construct(
        AbstractComponent $component,
        PayloadReaderInterface $mapper
    ) {
        $this->component = $component;
        $this->mapper = $mapper;
    }

    /**
     * Build an Api class given a command input.
     *
     * Will use the given read Mapper to translate from the command data to
     * an Api instance.
     *
     * The CliInput provides general information about the component that was
     * defined when the script was executed.
     *
     * @param string $action
     * @param array $command
     * @param CliInput $input
     * @return Api
     */
    abstract public function build($action, array $command, CliInput $input);
}
