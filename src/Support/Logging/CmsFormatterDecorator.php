<?php
namespace Czim\CmsCore\Support\Logging;

use Monolog\Formatter\FormatterInterface;

class CmsFormatterDecorator implements FormatterInterface
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var string
     */
    protected $formatterPrefix = '[CMS] ';


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
     * @param array $record A record to format
     *
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $formattedRecord = $this->formatter->format($record);

        if (is_array($formattedRecord)) {
            list($channel, $message, $backtrace, $loglevel) = $formattedRecord;

            $formattedRecord = [
                $channel,
                $this->formatterPrefix . $message,
                $backtrace,
                $loglevel,
            ];
        } else {
            $formattedRecord = $this->formatterPrefix . $formattedRecord;
        }

        return $formattedRecord;
    }

    /**
     * Formats a set of log records.
     *
     * @param array $records A set of records to format
     *
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        return $this->formatter->formatBatch($records);
    }

}
