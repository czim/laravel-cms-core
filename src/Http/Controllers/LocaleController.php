<?php
namespace Czim\CmsCore\Http\Controllers;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsCore\Http\Requests\SetLocaleRequest;

class LocaleController extends Controller
{

    /**
     * @var LocaleRepositoryInterface
     */
    protected $repository;

    /**
     * @param CoreInterface             $core
     * @param LocaleRepositoryInterface $repository
     */
    public function __construct(CoreInterface $core, LocaleRepositoryInterface $repository)
    {
        parent::__construct($core);

        $this->repository = $repository;
    }

    /**
     * Sets and stores the locale in the session.
     *
     * @param SetLocaleRequest $request
     * @return mixed
     */
    public function setLocale(SetLocaleRequest $request)
    {
        $locale = $request->input('locale');

        if ( ! $this->repository->isAvailable($locale)) {
            return redirect()->back()->withErrors("Locale {$locale} is not available");
        }

        session()->put($this->getSessionKey(), $locale);

        return redirect()->back();
    }

    /**
     * @return string
     */
    protected function getSessionKey()
    {
        return $this->core->config('session.prefix') . 'locale';
    }

}
