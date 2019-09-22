<?php
namespace Czim\CmsCore\Console\Commands;

use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

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
    public function handle(ModuleManagerInterface $manager): void
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
     * @param Collection|ModuleInterface[] $modules
     */
    protected function displayKeys(Collection $modules): void
    {
        foreach ($modules as $module) {
            $this->comment($module->getKey());
        }
    }

    /**
     * @param Collection|ModuleInterface[] $modules
     */
    protected function displayList(Collection $modules): void
    {
        foreach ($modules as $module) {
            $this->displaySingle($module);
        }
    }

    /**
     * @param ModuleInterface $module
     */
    protected function displaySingle(ModuleInterface $module): void
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
    protected function infoIndent(string $info, int $indents = 1): void
    {
        $this->info(str_repeat(' ', $indents * 4) . $info);
    }

}
