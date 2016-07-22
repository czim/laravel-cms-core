<?php
namespace Czim\CmsCore\Http\Controllers;

class DefaultController extends Controller
{

    public function index()
    {

        return view('cms::blank.index');
    }

}
