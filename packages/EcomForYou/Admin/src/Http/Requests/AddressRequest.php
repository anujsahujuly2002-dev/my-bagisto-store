<?php

namespace EcomForYou\Admin\Http\Requests;

use EcomForYou\Core\Rules\AddressLines;
use EcomForYou\Core\Rules\PhoneNumber;
use EcomForYou\Core\Rules\PostCode;
use EcomForYou\Core\Rules\StateBelongsToCountry;
use EcomForYou\Customer\Repositories\CustomerAddressRepository;
use EcomForYou\Customer\Repositories\CustomerRepository;
use EcomForYou\Customer\Rules\AllowedCountry;
use EcomForYou\Customer\Rules\VatIdRule;
use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'company_name' => ['nullable', 'max:255'],
            'first_name' => ['required', 'max:255'],
            'last_name' => ['required', 'max:255'],
            'address' => ['required', 'array', 'min:1', new AddressLines],
            'country' => ['required', new AllowedCountry($this->customerChannelCode())],
            'state' => ['required', 'max:255', new StateBelongsToCountry($this->input('country'))],
            'city' => ['required', 'max:255'],
            'postcode' => ['required', 'max:255', new PostCode],
            'phone' => ['required', 'max:255', new PhoneNumber],
            'vat_id' => ['max:255', (new VatIdRule)->setCountry($this->input('country'))],
            'email' => ['required', 'max:255'],
            'default_address' => ['sometimes', 'required', 'in:0,1'],
        ];
    }

    /**
     * Attributes.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'address.*' => 'address',
        ];
    }

    /**
     * Retrieve the channel of the customer the address belongs to, whose address settings apply.
     */
    protected function customerChannelCode(): ?string
    {
        $customer = $this->routeIs('admin.customers.customers.addresses.store')
            ? app(CustomerRepository::class)->find($this->route('id'))
            : app(CustomerAddressRepository::class)->find($this->route('id'))?->customer;

        return $customer?->channel?->code;
    }
}
