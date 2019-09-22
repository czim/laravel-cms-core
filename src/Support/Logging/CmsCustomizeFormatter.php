<?php
namespace Czim\CmsCore\Support\Logging;

use Monolog\Logger;

class CmsCustomizeFormatter
{

    /**
     * Customize the given Monolog instance.
     *
     * @param Logger $monolog
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
