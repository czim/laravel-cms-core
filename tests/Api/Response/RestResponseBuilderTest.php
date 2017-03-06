<?php
namespace Czim\CmsCore\Test\Api;

use Czim\CmsCore\Api\Response\RestResponseBuilder;
use Czim\CmsCore\Api\Response\TransformContainer;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Test\CmsBootTestCase;
use Czim\CmsCore\Test\Helpers\Api\TestTransformer;
use Czim\CmsCore\Test\Helpers\Exceptions\WithGetStatusCodeException;
use Czim\CmsCore\Test\Helpers\Exceptions\WithStatusCodePropertyException;
use Czim\CmsCore\Test\Helpers\Support\BasicDataObject;
use Czim\CmsCore\Test\Helpers\Support\TestDataObject;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\TransformerAbstract;

class RestResponseBuilderTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_leaves_non_transform_container_content_as_is()
    {
        $builder = new RestResponseBuilder($this->getMockCore(), $this->getMockFractalManager());

        /** @var JsonResponse $result */
        $result = $builder->response('testing');

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals(200, $result->getStatusCode());
        static::assertEquals('testing', $result->getData());
    }

    /**
     * @test
     */
    function it_leaves_container_content_as_is_if_no_transformer_is_set()
    {
        $builder = new RestResponseBuilder($this->getMockCore(), $this->getMockFractalManager());

        $container = new TransformContainer();
        $container->content     = 'testing';
        $container->transformer = null;

        /** @var JsonResponse $result */
        $result = $builder->response($container);

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals(200, $result->getStatusCode());
        static::assertEquals('testing', $result->getData());
    }

    // ------------------------------------------------------------------------------
    //      Single item
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_uses_a_transformer_instance_if_set_on_container()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        $manager->expects(static::once())
            ->method('createData')
            ->willReturnCallback(function ($resource) {

                static::assertInstanceOf(FractalItem::class, $resource);

                return new BasicDataObject([ 'test' => 'testing' ]);
            });

        /** @var TransformerAbstract|\PHPUnit_Framework_MockObject_MockObject $transformerMock */
        $transformerMock = $this->getMockBuilder(TransformerAbstract::class)->getMock();

        $container = new TransformContainer();
        $container->content     = 'testing';
        $container->transformer = $transformerMock;

        /** @var JsonResponse $result */
        $result = $builder->response($container);

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals(200, $result->getStatusCode());
        static::assertEquals((object) ['test' => 'testing'], $result->getData());
    }

    /**
     * @test
     */
    function it_uses_a_transformer_class_if_set_on_container()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        $manager->expects(static::once())
            ->method('createData')
            ->willReturnCallback(function ($resource) {

                static::assertInstanceOf(FractalItem::class, $resource);

                return new BasicDataObject([
                    'test' => 'testing',
                ]);
            });

        $container = new TransformContainer();
        $container->content     = 'testing';
        $container->transformer = TestTransformer::class;

        /** @var JsonResponse $result */
        $result = $builder->response($container);

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals(200, $result->getStatusCode());
        static::assertEquals((object) ['test' => 'testing'], $result->getData());
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp #Czim\\CmsCore\\Test\\Helpers\\Support\\TestDataObject#
     */
    function it_throws_an_exception_if_transformer_class_is_invalid()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        $container = new TransformContainer();
        $container->content     = 'testing';
        $container->transformer = TestDataObject::class;

        $builder->response($container);
    }


    // ------------------------------------------------------------------------------
    //      Collection
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_transforms_a_collection()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        $manager->expects(static::once())
            ->method('createData')
            ->willReturnCallback(function ($resource) {
                /** @var FractalCollection $resource */
                static::assertInstanceOf(FractalCollection::class, $resource);
                static::assertCount(2, $resource->getData());

                return new BasicDataObject([ 'test' => 'testing' ]);
            });

        /** @var TransformerAbstract|\PHPUnit_Framework_MockObject_MockObject $transformerMock */
        $transformerMock = $this->getMockBuilder(TransformerAbstract::class)->getMock();

        $container = new TransformContainer();
        $container->content     = ['test-a', 'test-b'];
        $container->transformer = $transformerMock;
        $container->collection  = true;

        /** @var JsonResponse $result */
        $result = $builder->response($container);

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals(200, $result->getStatusCode());
        static::assertEquals((object) ['test' => 'testing'], $result->getData());
    }

    /**
     * @test
     */
    function it_transforms_a_paginated_collection()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        $manager->expects(static::once())
            ->method('createData')
            ->willReturnCallback(function ($resource) {
                /** @var FractalCollection $resource */
                static::assertInstanceOf(FractalCollection::class, $resource);
                static::assertCount(2, $resource->getData());
                /** @var IlluminatePaginatorAdapter $paginator */
                $paginator = $resource->getPaginator();
                static::assertInstanceOf(IlluminatePaginatorAdapter::class, $paginator);
                static::assertInstanceOf(LengthAwarePaginator::class, $paginator->getPaginator());

                return new BasicDataObject([ 'test' => 'testing' ]);
            });

        /** @var TransformerAbstract|\PHPUnit_Framework_MockObject_MockObject $transformerMock */
        $transformerMock = $this->getMockBuilder(TransformerAbstract::class)->getMock();

        $paginator = new LengthAwarePaginator(['test-a', 'test-b'], 4, 2);

        $container = new TransformContainer();
        $container->content     = $paginator;
        $container->transformer = $transformerMock;
        $container->collection  = true;

        /** @var JsonResponse $result */
        $result = $builder->response($container);

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals(200, $result->getStatusCode());
        static::assertEquals((object) ['test' => 'testing'], $result->getData());
    }


    // ------------------------------------------------------------------------------
    //      Errors
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_renders_an_error_response_for_array_data()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        /** @var JsonResponse $result */
        $result = $builder->error(['message' => 'test error']);

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals(500, $result->getStatusCode());
        static::assertEquals((object) ['message' => 'test error'], $result->getData());
    }

    /**
     * @test
     */
    function it_renders_an_error_response_for_array_data_with_custom_status_code()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        /** @var JsonResponse $result */
        $result = $builder->error(['message' => 'test error'], 418);

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals(418, $result->getStatusCode());
    }

    /**
     * @test
     */
    function it_normalizes_error_to_array()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        /** @var JsonResponse $result */
        $result = $builder->error('test error');
        static::assertEquals((object) ['message' => 'test error'], $result->getData());

        /** @var JsonResponse $result */
        $result = $builder->error(new BasicDataObject(['message' => 'test error']));
        static::assertEquals((object) ['message' => 'test error'], $result->getData());
    }

    /**
     * @test
     */
    function it_renders_an_error_response_for_exception_with_standard_message_for_status_422()
    {
        $this->standardExceptionMessageTest(422, 'Unprocessable Entity');
    }

    /**
     * @test
     */
    function it_renders_an_error_response_for_exception_with_standard_message_for_status_404()
    {
        $this->standardExceptionMessageTest(404, 'Not Found');
    }

    /**
     * @test
     */
    function it_renders_an_error_response_for_exception_with_standard_message_for_status_403()
    {
        $this->standardExceptionMessageTest(403, 'Forbidden');
    }

    /**
     * @test
     */
    function it_renders_an_error_response_for_exception_with_standard_message_for_status_401()
    {
        $this->standardExceptionMessageTest(401, 'Unauthorized');
    }

    /**
     * @test
     */
    function it_renders_an_error_response_for_exception_with_standard_message_for_status_400()
    {
        $this->standardExceptionMessageTest(400, 'Bad Request');
    }

    /**
     * @test
     */
    function it_renders_an_error_response_for_a_standard_exception()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        /** @var JsonResponse $result */
        $result = $builder->error(new \Exception('test error'));

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals(500, $result->getStatusCode());
        static::assertEquals((object) ['message' => 'test error'], $result->getData());
    }

    /**
     * @test
     */
    function it_uses_status_code_and_includes_extra_content_for_a_http_response_exception()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        /** @var JsonResponse $result */
        $result = $builder->error(
            new HttpResponseException(response()->json(['test' => 'data'], 422))
        );

        static::assertEquals(
            (object) [
                'message' => 'Unprocessable Entity',
                'data'    => (object) [ 'test' => 'data' ]
            ],
            $result->getData()
        );
    }

    /**
     * @test
     */
    function it_uses_status_code_of_an_exception_with_a_get_status_code_method()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        /** @var JsonResponse $result */
        $result = $builder->error(new WithGetStatusCodeException());

        static::assertEquals(418, $result->getStatusCode());
    }

    /**
     * @test
     */
    function it_uses_status_code_of_an_exception_with_a_http_status_code_property()
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        /** @var JsonResponse $result */
        $result = $builder->error(new WithStatusCodePropertyException());

        static::assertEquals(418, $result->getStatusCode());
    }

    /**
     * @test
     */
    function it_formats_exception_response_with_extra_information_for_debug_mode()
    {
        $coreMock = $this->getMockCore();

        $this->app['config']->set('app.debug', true);
        $this->app['config']->set('cms-core.debug', true);
        $this->app['config']->set('cms-api.debug.local-exception-trace', true);

        $coreMock->method('config')
            ->willReturnCallback(function ($key, $default = null) {
                switch ($key) {
                    case 'cms-core.debug':
                        return true;
                }
                return $default;
            });

        $coreMock->method('apiConfig')
            ->willReturnCallback(function ($key, $default = null) {
                switch ($key) {
                    case 'debug.local-exception-trace':
                        return true;
                }
                return $default;
            });


        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($coreMock, $manager);

        /** @var JsonResponse $result */
        $result = $builder->error(new \Exception());

        $data = (array) $result->getData();

        static::assertArrayHasKey('file', $data);
        static::assertArrayHasKey('line', $data);
        static::assertInternalType('integer', $data['line']);
        static::assertArrayHasKey('trace', $data);
        static::assertInternalType('array', $data['trace']);
    }


    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------
    
    /**
     * @param int    $status
     * @param string $message
     */
    protected function standardExceptionMessageTest($status, $message)
    {
        $manager = $this->getMockFractalManager();
        $builder = new RestResponseBuilder($this->getMockCore(), $manager);

        /** @var JsonResponse $result */
        $result = $builder->error(new \Exception(), $status);

        static::assertInstanceOf(JsonResponse::class, $result);
        static::assertEquals($status, $result->getStatusCode());
        static::assertEquals((object) ['message' => $message], $result->getData());
    }


    /**
     * @return CoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCore()
    {
        return $this->getMockBuilder(CoreInterface::class)->getMock();
    }

    protected function getMockFractalManager()
    {
        return $this->getMockBuilder(Manager::class)->getMock();
    }

}
