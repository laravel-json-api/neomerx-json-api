<?php
/**
 * Copyright 2015-2020 info@neomerx.com
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

declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Schema;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Schema\Link;
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    /**
     * @return void
     */
    public function testAboutLink(): void
    {
        $link = new Link(false, 'https://example.com', false);
        $error = new Error(null, $link);

        $this->assertSame(['about' => $link], $error->getLinks());
    }

    /**
     * @return void
     */
    public function testMultipleLinks(): void
    {
        $link1 = new Link(false, 'https://example.com/foo', false);
        $link2 = new Link(false, 'https://example.com/bar', false);
        $error = new Error(null, $link1);
        $error->setLink('blah', $link2);

        $this->assertSame(['about' => $link1, 'blah' => $link2], $error->getLinks());
    }

    /**
     * @return void
     */
    public function testSetMissingAboutLinkToNull(): void
    {
        $error = new Error();
        $error->setLink('about', null);

        $this->assertNull($error->getLinks());
    }

    /**
     * @return void
     */
    public function testSetAboutLinkToNull(): void
    {
        $error = new Error(null, new Link(false, 'https://example.com', false));
        $error->setLink('about', null);

        $this->assertNull($error->getLinks());
    }
}
