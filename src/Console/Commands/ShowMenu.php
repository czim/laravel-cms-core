<?php
namespace Czim\CmsCore\Console\Commands;

use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Illuminate\Console\Command;

class ShowMenu extends Command
{

    protected $signature = 'cms:menu:show 
        {--locale= : Show translation information for given locale}';

    protected $description = 'Show menu information';


    /**
     * Execute the console command.
     *
     * @param MenuRepositoryInterface $repository
     */
    public function handle(MenuRepositoryInterface $repository)
    {
        $this->setLocale();

        $repository->ignorePermission()->initialize();

        $groups = $repository->getMenuLayout();

        if (count($groups)) {

            $this->comment('Menu layout:');
            $this->info('');

            foreach ($groups as $key => $group) {

                $this->displaySingle($group, $key, 1);
            }
        }
    }

    /**
     * Sets locale if option is set.
     */
    protected function setLocale()
    {
        if ( ! ($locale = $this->option('locale'))) {
            return;
        }

        app()->setLocale($locale);
    }

    /**
     * @param MenuPresenceInterface $presence
     * @param string|null           $key
     * @param int                   $depth
     */
    protected function displaySingle(MenuPresenceInterface $presence, $key = null, $depth = 0)
    {
        $indent = str_repeat(' ', $depth * 2);

        $this->comment($indent . ucfirst($presence->type()) . ($key && ! is_numeric($key) ? " (key: {$key})" : null));

        if ($presence->label()) {
            $this->info($indent . 'Label       : ' . $presence->label());
        }

        if ($presence->translationKey()) {

            // Add a flag for currently undefined translation keys
            $isTranslated = $presence->label(true) !== $presence->label(false);

            $this->info(
                $indent . 'Transl. key : ' . $presence->translationKey()
                . ($isTranslated ? null : ' (' .  app()->getLocale() . ' unset)')
            );
        }

        if ($presence->action()) {
            $this->info($indent . 'Action      : ' . $presence->action());
        }

        if (count($presence->permissions())) {
            $this->info($indent . 'Permissions : ' . implode(', ', $presence->permissions()));
        }

        $children = $presence->children();

        if (count($children)) {

            $this->info($indent . 'Children :');
            $this->info('');

            foreach ($children as $key => $child) {

                $this->displaySingle($child, ! is_numeric($key) ? $key : null, $depth + 1);
            }
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
