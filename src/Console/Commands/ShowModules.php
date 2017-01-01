<?php
namespace Czim\CmsCore\Console\Commands;

use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Illuminate\Console\Command;

class ShowModules extends Command
{

    protected $signature = 'cms:modules:show {module? : Module key to show for a specific module}
                                {--keys : Shows only the module keys}';

    protected $description = 'Show module information';


    /**
     * Execute the console command.
     *
     * @param ModuleManagerInterface $manager
     */
    public function handle(ModuleManagerInterface $manager)
    {
        $key      = $this->argument('module');
        $onlyKeys = $this->option('keys');

        if ( ! $key) {
            // Show all modules
            if ($onlyKeys) {
                $this->displayKeys($manager->getModules());
            } else {
                $this->displayList($manager->getModules());
            }
            return;
        }

        // Try by key
        $module = $manager->get($key);

        if ($module) {
            $this->displaySingle($module);
            return;
        }

        $this->error("Unable to find information for module by key '{$key}'");
    }

    /**
     * @param ModuleInterface[] $modules
     */
    protected function displayKeys($modules)
    {
        foreach ($modules as $module) {
            $this->comment($module->getKey());
        }
    }

    /**
     * @param ModuleInterface[] $modules
     */
    protected function displayList($modules)
    {
        foreach ($modules as $module) {
            $this->displaySingle($module);
        }
    }

    /**
     * @param ModuleInterface $module
     */
    protected function displaySingle(ModuleInterface $module)
    {
        $this->comment($module->getKey());
        $this->infoIndent('Name             : ' . $module->getName());
        $this->infoIndent('Version          : ' . $module->getVersion());

        if ($module->getAssociatedClass()) {
            $this->infoIndent('Associated Class : ' . $module->getAssociatedClass());
        }
        
        $this->info('');
    }

    /**
     * @param string $info
     * @param int    $indents
     */
    protected function infoIndent($info, $indents = 1)
    {
        $this->info(str_repeat(' ', $indents * 4) . $info);
    }

}
