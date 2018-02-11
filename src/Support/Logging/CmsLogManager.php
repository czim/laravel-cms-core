<?php
namespace Czim\CmsCore\Support\Logging;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Log\LogManager;

/**
 * Class CmsLogManager
 *
 * Extension of the Laravel log manager with its own default channel
 * for the CMS-context.
 */
class CmsLogManager extends LogManager
{

    /**
     * @var string
     */
    protected $defaultChannel;


    /**
     * Create a new Log manager instance.
     *
     * @param Application|FoundationApplication $app
     * @param string                            $defaultChannel
     */
    public function __construct($app, $defaultChannel = 'cms')
    {
        parent::__construct($app);

        $this->defaultChannel = $defaultChannel;
    }


    /**
     * Get the default log driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->defaultChannel;
    }

    /**
     * Set the default log driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver($name)
    {
        $this->defaultChannel = $name;
    }

}
