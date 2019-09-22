<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Support\Data;

use Czim\CmsCore\Support\Logging\CmsFormatterDecorator;
use Czim\CmsCore\Test\TestCase;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;
use Monolog\Formatter\FormatterInterface;

class CmsFormatterDecoratorTest extends TestCase
{

    /**
     * @test
     */
    function it_relays_batch_formatting_to_wrapped_formatter()
    {
        /** @var FormatterInterface|Mock|MockInterface $formatterMock */
        $formatterMock = Mockery::mock(FormatterInterface::class);
        $formatterMock->shouldReceive('formatBatch')->once()->with(['testing']);

        $decorator = new CmsFormatterDecorator($formatterMock);

        $decorator->formatBatch(['testing']);
    }

    /**
     * @test
     */
    function it_decorates_a_string_message()
    {
        /** @var FormatterInterface|Mock|MockInterface $formatterMock */
        $formatterMock = Mockery::mock(FormatterInterface::class);
        $formatterMock->shouldReceive('format')->once()
            ->with(['test'])->andReturn('test');

        $decorator = new CmsFormatterDecorator($formatterMock);

        static::assertEquals(CmsFormatterDecorator::CMS_RECORD_PREFIX . 'test', $decorator->format(['test']));
    }

    /**
     * @test
     */
    function it_decorates_an_array_message()
    {
        /** @var FormatterInterface|Mock|MockInterface $formatterMock */
        $formatterMock = Mockery::mock(FormatterInterface::class);
        $formatterMock->shouldReceive('format')->once()
            ->with(['test'])
            ->andReturn([
                'channel',
                'test',
                'trace',
                'level'
            ]);

        $decorator = new CmsFormatterDecorator($formatterMock);

        static::assertEquals(
            [
                'channel',
                CmsFormatterDecorator::CMS_RECORD_PREFIX . 'test',
                'trace',
                'level'
            ],
            $decorator->format(['test'])
        );
    }

}
