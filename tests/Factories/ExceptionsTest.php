<?php namespace Neomerx\Tests\JsonApi\Factories;

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
use \Neomerx\JsonApi\Factories\Exceptions;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ExceptionsTest extends BaseTestCase
{
    /**
     * Test throwInvalidArgument.
     */
    public function testThrowInvalidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);

        Exceptions::throwInvalidArgument('param', 'some value');
    }

    /**
     * Test throwLogicException.
     */
    public function testThrowLogicException()
    {
        $this->expectException(\LogicException::class);

        Exceptions::throwLogicException('message');
    }
}
