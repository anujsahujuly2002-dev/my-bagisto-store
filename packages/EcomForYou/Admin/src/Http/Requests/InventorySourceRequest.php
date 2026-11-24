<?php

namespace EcomForYou\Admin\Http\Requests;

use EcomForYou\Core\Rules\Address;
use EcomForYou\Core\Rules\Code;
use EcomForYou\Core\Rules\PhoneNumber;
use EcomForYou\Core\Rules\PostCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventorySourceRequest extends FormRequest
{
    /**
     * Determine if the Configuraion is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => ['required', 'max:255', Rule::unique('inventory_sources', 'code')->ignore($this->id), new Code],
            'name' => ['required', 'max:255'],
            'latitude' => ['numeric', 'between:-90,90'],
            'longitude' => ['numeric', 'between:-180,180'],
            'priority' => ['numeric'],
            'contact_name' => ['required', 'max:255'],
            'contact_email' => ['required', 'max:255', 'email'],
            'contact_number' => ['required', 'max:255', new PhoneNumber],
            'street' => ['required', 'max:255', new Address],
            'country' => ['required'],
            'state' => ['required', 'max:255'],
            'city' => ['required', 'max:255'],
            'postcode' => ['required', 'max:255', new PostCode],
        ];
    }
}
