<?php
namespace Czim\CmsCore\Http\ViewComposers;

use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Illuminate\Contracts\View\View;

/**
 * Class LocaleComposer
 *
 * Composes data for the locale switching partial.
 */
class LocaleComposer
{

    /**
     * @var LocaleRepositoryInterface
     */
    protected $repository;


    public function __construct(LocaleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     */
    public function compose(View $view): void
    {
        $view->with([
            'localized'        => $this->repository->isLocalized(),
            'currentLocale'    => app()->getLocale(),
            'availableLocales' => $this->repository->getAvailable(),
        ]);
    }

}
