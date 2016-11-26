<?php
namespace Czim\CmsCore\Http\Controllers;

class DefaultController extends Controller
{

    /**
     * Shows a very boring blank page.
     *
     * Usable as a safe default fallback action.
     *
     * @return mixed
     */
    public function index()
    {
        return view('cms::blank.index');
    }

}
