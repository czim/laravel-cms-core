<?php
namespace Czim\CmsCore\Http\Requests;

class SetLocaleRequest extends Request
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => 'required|string',
        ];
    }

}
