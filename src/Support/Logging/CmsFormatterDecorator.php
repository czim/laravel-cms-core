<?php
namespace Czim\CmsCore\Support\Logging;

use Monolog\Formatter\FormatterInterface;

class CmsFormatterDecorator implements FormatterInterface
{
    const CMS_RECORD_PREFIX = '[CMS] ';


    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var string
     */
    protected $formatterPrefix = self::CMS_RECORD_PREFIX;


    /**
     * @param FormatterInterface $formatter
     */
    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }


    /**
     * Formats a log record.
     *
     * @param array $record     a record to format
     * @return mixed    the formatted record
     */
    public function format(array $record)
    {
        $formattedRecord = $this->formatter->format($record);

        if (is_array($formattedRecord)) {
            list($channel, $message, $backtrace, $loglevel) = $formattedRecord;

            return [
                $channel,
                $this->formatterPrefix . $message,
                $backtrace,
                $loglevel,
            ];
        }

        return $this->formatterPrefix . $formattedRecord;
    }

    /**
     * Formats a set of log records.
     *
     * @param array $records    a set of records to format
     * @return mixed    the formatted set of records
     */
    public function formatBatch(array $records)
    {
        return $this->formatter->formatBatch($records);
    }

}
