<?php
use Katana\Sdk\Exception\SchemaException;
use Katana\Sdk\Mapper\SchemaMapper;
use Katana\Sdk\Schema\Mapping;

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

class SchemaMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mapping
     */
    private $mapping;

    /**
     * @var SchemaMapper
     */
    private $mapper;

    public function setUp()
    {
        $this->mapping = new Mapping();
        $this->mapper = new SchemaMapper();
        $map = json_decode(
            file_get_contents(__DIR__ . '/service_mapping.json'),
            true
        );

        $services = [];
        foreach ($map as $service => $serviceMap) {
            foreach ($serviceMap as $version => $schema) {
                $services[] = $this->mapper->getServiceSchema($service, $version, $schema);
            }
        }
        $this->mapping->load($services);
    }

    public function testServiceNotFound()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Cannot resolve schema for Service: comments (1.0.0)');
        $this->mapping->find('comments', '1.0.0');
    }

    public function testVersionNotFound()
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Cannot resolve schema for Service: posts (0.1.0)');
        $this->mapping->find('posts', '0.1.0');
    }

    public function testServiceMapping()
    {
        $service = $this->mapping->find('posts', '1.0.0');
        $this->assertEquals('posts', $service->getName());
        $this->assertEquals('1.0.0', $service->getVersion());

        $this->assertCount(1, $service->getActions());
        $this->assertTrue($service->hasAction('list'));
        $this->assertFalse($service->hasAction('create'));

        $this->assertTrue($service->getHttpSchema()->isAccessible());
        $this->assertEquals('/1.0.0', $service->getHttpSchema()->getBasePath());
    }

    public function testActionNotFound()
    {
        $service = $this->mapping->find('posts', '1.0.0');
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Cannot resolve schema for action: foo');
        $service->getActionSchema('foo');
    }

    public function testActionMapping()
    {
        $service = $this->mapping->find('posts', '1.0.0');
        $action = $service->getActionSchema('list');

        $this->assertEquals('list', $action->getName());
        $this->assertEquals(false, $action->isDeprecated());
        $this->assertCount(3, $action->getParams());
        $this->assertTrue($action->hasParam('user_id'));
        $this->assertFalse($action->hasParam('foo'));

        // Assert entity
        $this->assertEquals('entity:data', $action->getEntityPath());
        $this->assertEquals(':', $action->getPathDelimiter());
        $this->assertEquals('uid', $action->getPrimaryKey());
        $this->assertEquals(true, $action->isCollection());

        // Assert http
        $http = $action->getHttpSchema();
        $this->assertEquals(true, $http->isAccessible());
        $this->assertEquals('/posts/{user_id}', $http->getPath());
        $this->assertEquals('get', $http->getMethod());
        $this->assertEquals('query', $http->getInput());
        $this->assertEquals('text/plain', $http->getBody());
    }

    public function testParamNotFound()
    {
        $service = $this->mapping->find('posts', '1.0.0');
        $action = $service->getActionSchema('list');
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Cannot resolve schema for parameter: foo');
        $action->getParamSchema('foo');
    }

    public function testParameterMapping()
    {
        $service = $this->mapping->find('posts', '1.0.0');
        $action = $service->getActionSchema('list');
        $param = $action->getParamSchema('user_id');

        $this->assertEquals('user_id', $param->getName());
        $this->assertFalse($param->hasDefaultValue());
        $this->assertNull($param->getDefaultValue());

        // Assert http
        $http = $param->getHttpSchema();
        $this->assertTrue($http->isAccessible());
        $this->assertEquals('path', $http->getInput());
        $this->assertEquals('user_id', $http->getParam());

        // Assert type
        $this->assertEquals('string', $param->getType());
        $this->assertEquals('uuid', $param->getFormat());
        $this->assertEquals('csv', $param->getArrayFormat());
        $this->assertEquals('', $param->getItems());

        // Assert expectation
        $this->assertFalse($param->hasDefaultValue());
        $this->assertNull($param->getDefaultValue());
        $this->assertTrue($param->isRequired());
        $this->assertFalse($param->allowEmpty());

        // Assert validation
        $this->assertEquals('', $param->getPattern());
        $this->assertEquals(PHP_INT_MAX, $param->getMax());
        $this->assertFalse($param->isExclusiveMax());
        $this->assertEquals(~PHP_INT_MAX, $param->getMin());
        $this->assertFalse($param->isExclusiveMin());
        $this->assertEquals(-1, $param->getMaxLength());
        $this->assertEquals(-1, $param->getMinLength());
        $this->assertEquals(-1, $param->getMaxItems());
        $this->assertEquals(-1, $param->getMinItems());
        $this->assertFalse($param->hasUniqueItems());
        $this->assertEquals([], $param->getEnum());
        $this->assertEquals(-1, $param->getMultipleOf());
    }

    public function testArrayParameterMapping()
    {
        $service = $this->mapping->find('posts', '1.0.0');
        $action = $service->getActionSchema('list');
        $param = $action->getParamSchema('tags');

        $this->assertEquals('tags', $param->getName());
        $this->assertFalse($param->hasDefaultValue());
        $this->assertNull($param->getDefaultValue());

        // Assert http
        $http = $param->getHttpSchema();
        $this->assertTrue($http->isAccessible());
        $this->assertEquals('query', $http->getInput());
        $this->assertEquals('user_tags', $http->getParam());

        // Assert type
        $this->assertEquals('array', $param->getType());
        $this->assertEquals('', $param->getFormat());
        $this->assertEquals('ssv', $param->getArrayFormat());
        $this->assertEquals('string', $param->getItems());

        // Assert expectation
        $this->assertFalse($param->hasDefaultValue());
        $this->assertNull($param->getDefaultValue());
        $this->assertFalse($param->isRequired());
        $this->assertTrue($param->allowEmpty());

        // Assert validation
        $this->assertEquals('', $param->getPattern());
        $this->assertEquals(PHP_INT_MAX, $param->getMax());
        $this->assertFalse($param->isExclusiveMax());
        $this->assertEquals(~PHP_INT_MAX, $param->getMin());
        $this->assertFalse($param->isExclusiveMin());
        $this->assertEquals(-1, $param->getMaxLength());
        $this->assertEquals(-1, $param->getMinLength());
        $this->assertEquals(3, $param->getMaxItems());
        $this->assertEquals(-1, $param->getMinItems());
        $this->assertFalse($param->hasUniqueItems());
        $this->assertEquals([
            "first",
            "second",
            "third",
            "forth",
            "fifth"
        ], $param->getEnum());
        $this->assertEquals(-1, $param->getMultipleOf());
    }

    public function testFileNotFound()
    {
        $service = $this->mapping->find('admin', '1.0.0');
        $action = $service->getActionSchema('check');
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Cannot resolve schema for file parameter: foo');
        $action->getFileSchema('foo');
    }

    public function testFileMapping()
    {
        $service = $this->mapping->find('admin', '1.0.0');
        $action = $service->getActionSchema('check');
        $this->assertCount(1, $action->getFiles());
        $this->assertTrue($action->hasFile('rules'));
        $this->assertFalse($action->hasFile('foo'));

        $file = $action->getFileSchema('rules');
        $this->assertEquals('rules', $file->getName());
        $this->assertEquals('text/plain,text/csv', $file->getMime());
        $this->assertEquals(true, $file->isRequired());
        $this->assertEquals(128000, $file->getMax());
        $this->assertEquals(false, $file->isExclusiveMax());
        $this->assertEquals(256, $file->getMin());
        $this->assertEquals(false, $file->isExclusiveMin());

        // Assert http
        $http = $file->getHttpSchema();
        $this->assertEquals(true, $http->isAccessible());
        $this->assertEquals('rules', $http->getParam());
    }

    public function testEntityMapping()
    {
        $service = $this->mapping->find('posts', '0.2.3');
        $action = $service->getActionSchema('list');
        $this->assertTrue($action->hasEntity());

        $entity = $action->getEntity();
        $this->assertCount(2, $entity);
        $this->assertEquals('integer', $entity['id']);
        $this->assertCount(2, $entity['details']);
        $this->assertEquals('string', $entity['details']['name']);
        $this->assertEquals('boolean', $entity['details']['active']);
    }

    public function testResolveEntity()
    {
        $service = $this->mapping->find('posts', '0.2.3');
        $action = $service->getActionSchema('list');

        $data = [
            'meta' => ['id' => '1234-1qwe'],
            'entity' => [
                'data' => [
                    'name' => 'test',
                    'age' => 20,
                ],
                'relation' => [
                    'client' => 'ads3-45rt',
                ],
            ],
        ];

        $this->assertEquals([
            'name' => 'test',
            'age' => 20,
        ], $action->resolveEntity($data));
    }

    public function testResolveEntityFailure()
    {
        $service = $this->mapping->find('posts', '0.2.3');
        $action = $service->getActionSchema('list');

        $data = [
            'data' => [
                'name' => 'test',
                'age' => 20,
            ],
            'relation' => [
                'client' => 'ads3-45rt',
            ],
        ];

        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage('Cannot resolve entity for action: list');
        $action->resolveEntity($data);
    }

    public function testNoEntity()
    {
        $service = $this->mapping->find('admin', '1.0.0');
        $action = $service->getActionSchema('check');
        $this->assertFalse($action->hasEntity());
        $this->assertEquals([], $action->getEntity());

        $data = [
            'data' => [
                'name' => 'test',
                'age' => 20,
            ],
            'relation' => [
                'client' => 'ads3-45rt',
            ],
        ];

        $this->assertEquals($data, $action->resolveEntity($data));
    }
}
