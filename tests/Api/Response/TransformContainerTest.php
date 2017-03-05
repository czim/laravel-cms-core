<?php
namespace Czim\CmsCore\Test\Api;

use Czim\CmsCore\Api\Response\TransformContainer;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Support\Collection;

class TransformContainerTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_defaults_to_content_type_for_determining_is_collection_value()
    {
        $container = new TransformContainer([
            'content'    => new Collection,
            'collection' => null,
        ]);

        static::assertSame(true, $container->isCollection());
    }

}
