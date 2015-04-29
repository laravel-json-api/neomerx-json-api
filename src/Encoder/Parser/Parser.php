<?php namespace Neomerx\JsonApi\Encoder\Parser;

/**
 * Copyright 2015 info@neomerx.com (www.neomerx.com)
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

use \Generator;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Schema\LinkObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserReplyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFactoryInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserFactoryInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameReadOnlyInterface;

/**
 * The main purpose of the parser is to reach **every resource** that is targeted for inclusion and its
 * relations if data schema describes them as 'included'. Parser manager is managing this decision making.
 * Parser helps to filter resource attributes at the moment of their creation.
 *   ^^^^
 *     This is 'sparse' JSON API feature and 'fields set' feature (for attributes)
 *
 * Parser does not decide if particular resource or its relations are actually added to final JSON document.
 * Parsing reply interpreter does this job. Parser interpreter might not include some intermediate resources
 * that parser has found while reaching targets.
 *   ^^^^
 *     This is 'sparse' JSON API feature again and 'fields set' feature (for links)
 *
 * The final JSON view of an element is chosen by document which uses settings to decide if 'self', 'meta', and
 * other members should be rendered.
 *   ^^^^
 *     This is generic JSON API features
 *
 * Once again, it basically works this way:
 *   - Parser finds all targeted relations and outputs them with all intermediate results (looks like a tree).
 *     Resource attributes are already filtered.
 *   - Reply interpreter filters intermediate results and resource relations sending results to document.
 *   - The document is just a renderer which saves the input data in a few slight variations depending on settings.
 *   - When all data are parsed the document converts collected data to json.
 *
 * @package Neomerx\JsonApi
 */
class Parser implements ParserInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ParserFactoryInterface
     */
    private $parserFactory;

    /**
     * @var StackFactoryInterface
     */
    private $stackFactory;

    /**
     * @var StackInterface
     */
    private $stack;

    /**
     * @var ParserManagerInterface|null
     */
    private $manager;

    /**
     * @param ParserFactoryInterface      $parserFactory
     * @param StackFactoryInterface       $stackFactory
     * @param ContainerInterface          $container
     * @param ParserManagerInterface|null $manager
     */
    public function __construct(
        ParserFactoryInterface $parserFactory,
        StackFactoryInterface $stackFactory,
        ContainerInterface $container,
        ParserManagerInterface $manager = null
    ) {
        $this->manager       = $manager;
        $this->container     = $container;
        $this->stackFactory  = $stackFactory;
        $this->parserFactory = $parserFactory;
    }

    /**
     * @inheritdoc
     */
    public function parse($data)
    {
        assert('is_array($data) || is_object($data) || is_null($data)');

        $this->stack = $this->stackFactory->createStack();
        $this->stack->push();

        foreach ($this->parseData($data) as $parseReply) {
            yield $parseReply;
        }

        $this->stack = null;
    }

    /**
     * @param array|object|null $data
     *
     * @return Generator
     */
    private function parseData($data)
    {
        $curFrame = $this->stack->end();

        if (empty($data) === true) {
            yield $this->createReplyForEmptyData($data);
        } elseif ($this->isRefFrame()) {
            yield $this->createReplyForRef();
        } else {
            if (is_array($data) === true) {
                $isOriginallyArrayed = true;
                $schema = $this->container->getSchema($data[0]);
            } else {
                $isOriginallyArrayed = false;
                $schema = $this->container->getSchema($data);
                $data   = [$data];
            }

            // duplicated are allowed in data however they shouldn't be in includes
            $isDupAllowed = $curFrame->getLevel() < 2;

            foreach ($data as $resource) {
                $fieldSet       = $this->getFieldSet($schema->getResourceType());
                $resourceObject = $schema->createResourceObject($resource, $isOriginallyArrayed, $fieldSet);
                $isCircular     = $this->checkCircular($resourceObject);

                $this->stack->setCurrentResourceObject($resourceObject);
                yield $this->createReplyResourceStarted();

                if (($isCircular === true && $isDupAllowed === false) ||
                    $this->shouldParseLinks($resourceObject, $isCircular) === false
                ) {
                    continue;
                }

                foreach ($schema->getLinkObjectIterator($resource) as $linkObject) {
                    /** @var LinkObjectInterface $linkObject */
                    $nextFrame = $this->stack->push();
                    $nextFrame->setLinkObject($linkObject);
                    try {
                        foreach ($this->parseData($linkObject->getLinkedData()) as $parseResult) {
                            yield $parseResult;
                        }
                    } finally {
                        $this->stack->pop();
                    }
                }

                yield $this->createReplyResourceCompleted();
            }
        }
    }

    /**
     * @param array|null $data
     *
     * @return ParserReplyInterface
     */
    private function createReplyForEmptyData($data)
    {
        assert('empty($data) && (is_array($data) || is_null($data))');

        $replyType = ($data === null ? ParserReplyInterface::REPLY_TYPE_NULL_RESOURCE_STARTED :
            ParserReplyInterface::REPLY_TYPE_EMPTY_RESOURCE_STARTED);

        return $this->parserFactory->createEmptyReply($replyType, $this->stack);
    }

    /**
     * @return ParserReplyInterface
     */
    private function createReplyForRef()
    {
        $replyType = ParserReplyInterface::REPLY_TYPE_REFERENCE_STARTED;

        return $this->parserFactory->createEmptyReply($replyType, $this->stack);
    }

    /**
     * @return ParserReplyInterface
     */
    private function createReplyResourceStarted()
    {
        return $this->parserFactory->createReply(ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED, $this->stack);
    }

    /**
     * @return ParserReplyInterface
     */
    private function createReplyResourceCompleted()
    {
        return $this->parserFactory->createReply(ParserReplyInterface::REPLY_TYPE_RESOURCE_COMPLETED, $this->stack);
    }

    /**
     * @param ResourceObjectInterface $resource
     * @param bool                    $isCircular
     *
     * @return bool
     */
    private function shouldParseLinks(ResourceObjectInterface $resource, $isCircular)
    {
        return $this->manager === null ? true :
            $this->manager->isShouldParseLinks($resource, $isCircular, $this->stack);
    }

    /**
     * @param ResourceObjectInterface $resourceObject
     *
     * @return bool
     */
    private function checkCircular(ResourceObjectInterface $resourceObject)
    {
        foreach ($this->stack as $frame) {
            /** @var StackFrameReadOnlyInterface $frame */
            if (($stackResource = $frame->getResourceObject()) !== null &&
                $stackResource->getType() === $resourceObject->getType() &&
                $stackResource->getId()   === $resourceObject->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * If current stack frame should be shown as a reference.
     *
     * @return bool
     */
    private function isRefFrame()
    {
        /** @var LinkObjectInterface $curLinkObject */
        $curLinkObject = $this->stack->end()->getLinkObject();
        return ($curLinkObject !== null && $curLinkObject->isShowAsReference() === true);
    }

    /**
     * @param string $resourceType
     *
     * @return array|null
     */
    private function getFieldSet($resourceType)
    {
        return ($this->manager === null ? null : $this->manager->getFieldSet($resourceType));
    }
}