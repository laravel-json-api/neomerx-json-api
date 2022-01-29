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
use \Neomerx\JsonApi\Http\Headers\MediaType;

/**
 * @package Neomerx\Tests\JsonApi
 */
class MediaTypeTest extends BaseTestCase
{
    /**
     * Test invalid constructor parameters.
     */
    public function testInvalidConstructorParams1()
    {
        $this->expectException(\InvalidArgumentException::class);

        new MediaType(null, 'subtype');
    }

    /**
     * Test invalid constructor parameters.
     */
    public function testInvalidConstructorParams2()
    {
        $this->expectException(\InvalidArgumentException::class);

        new MediaType('type', null);
    }

    /**
     * Test invalid parse parameters.
     */
    public function testInvalidParseParams1()
    {
        $this->expectException(\InvalidArgumentException::class);

        MediaType::parse(1, 'boo.bar+baz');
    }

    /**
     * Test invalid parse parameters.
     */
    public function testInvalidParseParams2()
    {
        $this->expectException(\InvalidArgumentException::class);

        MediaType::parse(1, 'boo/bar+baz;param');
    }

    /**
     * Test compare media types
     */
    public function testCompareMediaTypes()
    {
        $type1 = MediaType::parse(null, 'text/html;charset=utf-8');
        $type2 = MediaType::parse(null, 'Text/HTML; Charset="utf-8"');
        $type3 = MediaType::parse(null, 'text/plain;charset=utf-8');
        $type4 = MediaType::parse(null, 'text/html;otherParam=utf-8');
        $type5 = MediaType::parse(null, 'text/html;charset=UTF-8');
        $type6 = MediaType::parse(null, 'text/html;charset=UTF-8;oneMore=param');

        $this->assertTrue($type1->equalsTo($type2));
        $this->assertFalse($type1->equalsTo($type3));
        $this->assertFalse($type1->equalsTo($type4));
        $this->assertTrue($type1->equalsTo($type5));
        $this->assertFalse($type1->equalsTo($type6));
    }
}
