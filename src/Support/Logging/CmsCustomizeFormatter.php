<?php
namespace Czim\CmsCore\Support\Logging;

class CmsCustomizeFormatter
{

    /**
     * Customize the given Monolog instance.
     *
     * @param \Monolog\Logger $monolog
     */
    public function __invoke($monolog)
    {
        foreach ($monolog->getHandlers() as $handler) {

            $handler->setFormatter(
                new CmsFormatterDecorator($handler->getFormatter())
            );
        }
    }

}
