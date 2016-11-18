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

namespace Katana\Sdk\Schema;

use Katana\Sdk\Exception\SchemaException;

class Mapping
{
    /**
     * @var ServiceSchema[]
     */
    private $services = [];

    /**
     * @param ServiceSchema[] $services
     */
    public function load(array $services)
    {
        $this->services = $services;
    }

    /**
     * @param $service
     * @param $version
     * @return ServiceSchema
     * @throws SchemaException
     */
    public function find($service, $version)
    {
        $search = array_filter(
            $this->services,
            function (ServiceSchema $serviceSchema) use ($service, $version) {
                return $serviceSchema->getName() === $service
                    && $serviceSchema->getVersion() === $version;
            }
        );

        if (!$search) {
            throw new SchemaException("Cannot resolve schema for Service: $service ($version)");
        }

        return current($search);
    }
}
