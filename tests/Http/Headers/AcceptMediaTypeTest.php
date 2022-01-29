<?php namespace Neomerx\Tests\JsonApi\Http\Headers;

/**
 * Copyright 2015-2017 info@neomerx.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Http\Headers\AcceptMediaType;

/**
 * @package Neomerx\Tests\JsonApi
 */
class AcceptMediaTypeTest extends BaseTestCase
{
    /**
     * Test invalid constructor parameters.
     */
    public function testInvalidConstructorParams1()
    {
        $this->expectException(\InvalidArgumentException::class);

        new AcceptMediaType(1, null, 'subtype');
    }

    /**
     * Test invalid constructor parameters.
     */
    public function testInvalidConstructorParams2()
    {
        $this->expectException(\InvalidArgumentException::class);

        new AcceptMediaType(1, 'type', null);
    }

    /**
     * Test invalid constructor parameters.
     */
    public function testInvalidConstructorParams4()
    {
        $this->expectException(\InvalidArgumentException::class);

        new AcceptMediaType(1, 'type', 'subtype', null, 5);
    }

    /**
     * Test invalid constructor parameters.
     */
    public function testInvalidConstructorParams6()
    {
        $this->expectException(\InvalidArgumentException::class);

        new AcceptMediaType(1, 'type', 'subtype', null, 0.4, 1234);
    }

    /**
     * Test invalid constructor parameters.
     */
    public function testInvalidConstructorParams7()
    {
        $this->expectException(\InvalidArgumentException::class);

        new AcceptMediaType(-1, 'type', 'subtype', null, 0.4, null);
    }
}
