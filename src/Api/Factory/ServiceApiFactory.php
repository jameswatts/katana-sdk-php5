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

use Katana\Sdk\Api\ActionApi;
use Katana\Sdk\Console\CliInput;
use Katana\Sdk\Schema\Mapping;

/**
 * @package Katana\Sdk\Api\Factory
 */
class ServiceApiFactory extends ApiFactory
{
    /**
     * Build an Action Api class instance
     *
     * @param string $action
     * @param array $data
     * @param CliInput $input
     * @param Mapping $mapping
     * @return ActionApi
     */
    public function build(
        $action,
        array $data,
        CliInput $input,
        Mapping $mapping
    ) {
        return new ActionApi(
            $this->logger,
            $this->component,
            $mapping,
            dirname(realpath($_SERVER['SCRIPT_FILENAME'])),
            $input->getName(),
            $input->getVersion(),
            $input->getFrameworkVersion(),
            $input->getVariables(),
            $input->isDebug(),
            $action,
            $this->mapper->getTransport($data),
            $this->mapper->getParams($data)
        );
    }
}
