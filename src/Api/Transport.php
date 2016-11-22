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

use Katana\Sdk\Api\Value\VersionString;

/**
 * Api class that encapsulates the full Transport
 *
 * @package Katana\Sdk\Api
 */
class Transport
{
    /**
     * @var TransportMeta
     */
    private $meta;

    /**
     * @var File
     */
    private $body;

    /**
     * @var TransportFiles
     */
    private $files;

    /**
     * @var TransportData
     */
    private $data;

    /**
     * @var TransportRelations
     */
    private $relations;

    /**
     * @var TransportLinks
     */
    private $links;

    /**
     * @var TransportCalls
     */
    private $calls;

    /**
     * @var TransportTransactions
     */
    private $transactions;

    /**
     * @var TransportErrors
     */
    private $errors;

    /**
     * Creates an empty Transport
     *
     * @return Transport
     */
    public static function newEmpty()
    {
        return new Transport(
            new TransportMeta('', '', '', '', [], 0),
            new TransportFiles([]),
            new TransportData(),
            new TransportRelations(),
            new TransportLinks(),
            new TransportCalls(),
            new TransportTransactions(),
            new TransportErrors()
        );
    }

    /**
     * @param TransportMeta $meta
     * @param TransportFiles $files
     * @param TransportData $data
     * @param TransportRelations $relations
     * @param TransportLinks $links
     * @param TransportCalls $calls
     * @param TransportTransactions $transactions
     * @param TransportErrors $errors
     * @param File|null $body
     */
    public function __construct(
        TransportMeta $meta,
        TransportFiles $files,
        TransportData $data,
        TransportRelations $relations,
        TransportLinks $links,
        TransportCalls $calls,
        TransportTransactions $transactions,
        TransportErrors $errors,
        File $body = null
    ) {
        $this->meta = $meta;
        $this->files = $files;
        $this->data = $data;
        $this->relations = $relations;
        $this->links = $links;
        $this->calls = $calls;
        $this->transactions = $transactions;
        $this->errors = $errors;
        $this->body = $body;
    }

    /**
     * @return TransportMeta
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param File $file
     */
    public function setBody(File $file)
    {
        $this->body = $file;
    }

    /**
     * @return bool
     */
    public function hasBody()
    {
        return isset($this->body);
    }

    /**
     * @return File
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return TransportData
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return TransportRelations
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @return TransportLinks
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return bool
     */
    public function hasCalls()
    {
        return $this->calls->has();
    }

    /**
     * @return TransportCalls
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * @return bool
     */
    public function hasTransactions()
    {
        return $this->transactions->has();
    }

    /**
     * @return TransportTransactions
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @return TransportErrors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $service
     * @param string $version
     * @param string $action
     * @param string $name
     * @return bool
     */
    public function hasFile($service, $version, $action, $name)
    {
        return $this->files->has($service, $version, $action, $name);
    }

    /**
     * @return bool
     */
    public function hasFiles()
    {
        return !empty($this->files->getAll());
    }

    /**
     * @return TransportFiles
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param string $service
     * @param VersionString $version
     * @param string $action
     * @param File $file
     */
    public function addFile($service, VersionString $version, $action, File $file)
    {
        $this->files->add($service, $version, $action, $file);
    }

    /**
     * @param string $service
     * @param string $version
     * @param string $action
     * @param string $name
     * @return File
     */
    public function getFile($service, $version, $action, $name)
    {
        return $this->files->get($service, $version, $action, $name);
    }

    /**
     * @param string $service
     * @param string $version
     * @param string $action
     * @param array $data
     */
    public function setData($service, $version, $action, array $data)
    {
        $this->data->set($service, $version, $action, $data);
    }

    /**
     * @param string $service
     * @param string $version
     * @param string $action
     * @param array $collection
     */
    public function setCollection($service, $version, $action, array $collection)
    {
        $this->data->set($service, $version, $action, $collection);
    }

    /**
     * @param string $serviceFrom
     * @param string $idFrom
     * @param string $serviceTo
     * @param string $idTo
     */
    public function addSimpleRelation($serviceFrom, $idFrom, $serviceTo, $idTo)
    {
        $this->relations->addSimple($serviceFrom, $idFrom, $serviceTo, $idTo);
    }

    /**
     * @param string $serviceFrom
     * @param string $idFrom
     * @param string $serviceTo
     * @param array $idsTo
     */
    public function addMultipleRelation($serviceFrom, $idFrom, $serviceTo, array $idsTo)
    {
        $this->relations->addMultipleRelation($serviceFrom, $idFrom, $serviceTo, $idsTo);
    }

    /**
     * @param string $namespace
     * @param string $link
     * @param string $uri
     */
    public function setLink($namespace, $link, $uri)
    {
        $this->links->setLink($namespace, $link, $uri);
    }

    /**
     * @param Transaction $transaction
     */
    public function addTransaction(Transaction $transaction)
    {
        $this->transactions->add($transaction);
    }

    /**
     * @param Call $call
     */
    public function addCall(Call $call)
    {
        $this->calls->add($call);
    }

    /**
     * @param Error $error
     */
    public function addError(Error $error)
    {
        $this->errors->add($error);
    }
}
