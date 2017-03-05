<?php
namespace Czim\CmsCore\Test\Support\Data;

use Czim\CmsCore\Support\Logging\CmsFormatterDecorator;
use Czim\CmsCore\Test\TestCase;
use Monolog\Formatter\FormatterInterface;

class CmsFormatterDecoratorTest extends TestCase
{

    /**
     * @test
     */
    function it_relays_batch_formatting_to_wrapped_formatter()
    {
        /** @var FormatterInterface|\PHPUnit_Framework_MockObject_MockObject $formatterMock */
        $formatterMock = $this->getMockBuilder(FormatterInterface::class)->getMock();
        $formatterMock->expects(static::once())
            ->method('formatBatch')
            ->with(['testing']);

        $decorator = new CmsFormatterDecorator($formatterMock);

        $decorator->formatBatch(['testing']);
    }

    /**
     * @test
     */
    function it_decorates_a_string_message()
    {
        /** @var FormatterInterface|\PHPUnit_Framework_MockObject_MockObject $formatterMock */
        $formatterMock = $this->getMockBuilder(FormatterInterface::class)->getMock();
        $formatterMock->expects(static::once())
            ->method('format')
            ->with(['test'])
            ->willReturn('test');

        $decorator = new CmsFormatterDecorator($formatterMock);

        static::assertEquals(CmsFormatterDecorator::CMS_RECORD_PREFIX . 'test', $decorator->format(['test']));
    }

    /**
     * @test
     */
    function it_decorates_an_array_message()
    {
        /** @var FormatterInterface|\PHPUnit_Framework_MockObject_MockObject $formatterMock */
        $formatterMock = $this->getMockBuilder(FormatterInterface::class)->getMock();
        $formatterMock->expects(static::once())
            ->method('format')
            ->with(['test'])
            ->willReturn([
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
