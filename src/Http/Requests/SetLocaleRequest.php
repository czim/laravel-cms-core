<?php
namespace Czim\CmsCore\Http\Requests;

class SetLocaleRequest extends Request
{

    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'locale' => 'required|string',
        ];
    }

}
