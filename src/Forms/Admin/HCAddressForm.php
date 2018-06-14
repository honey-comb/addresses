<?php
/**
 * @copyright 2018 interactivesolutions
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Contact InteractiveSolutions:
 * E-mail: hello@interactivesolutions.lt
 * http://www.interactivesolutions.lt
 */

declare(strict_types = 1);

namespace HoneyComb\Addresses\Forms\Admin;

use HoneyComb\Core\Http\Requests\Admin\HCUserRequest;
use HoneyComb\Core\Repositories\HCUserRepository;
use HoneyComb\Regions\Http\Requests\Admin\HCCountryRequest;
use HoneyComb\Regions\Repositories\HCCountryRepository;
use HoneyComb\Starter\Forms\HCBaseForm;

/**
 * Class HCAddressForm
 * @package HoneyComb\Addresses\Forms\Admin
 */
class HCAddressForm extends HCBaseForm
{
    /**
     * @var \HoneyComb\Regions\Repositories\HCCountryRepository
     */
    private $countryRepository;
    /**
     * @var \HoneyComb\Core\Repositories\HCUserRepository
     */
    private $userRepository;

    /**
     * HCAddressForm constructor.
     * @param \HoneyComb\Regions\Repositories\HCCountryRepository $countryRepository
     * @param \HoneyComb\Core\Repositories\HCUserRepository $userRepository
     */
    public function __construct(HCCountryRepository $countryRepository, HCUserRepository $userRepository)
    {
        $this->countryRepository = $countryRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Creating form
     *
     * @param bool $edit
     * @return array
     * @throws \Illuminate\Container\EntryNotFoundException
     */
    public function createForm(bool $edit = false): array
    {
        $form = [
            'storageUrl' => route('admin.api.address'),
            'buttons' => [
                'submit' => [
                    'label' => $this->getSubmitLabel($edit),
                ],
            ],
            'structure' => $this->getStructure($edit),
        ];

        if ($this->multiLanguage) {
            $form['availableLanguages'] = getHCContentLanguages();
        }

        return $form;
    }

    /**
     * @param string $prefix
     * @return array
     */
    public function getStructureNew(string $prefix): array
    {
        $form = [

            $prefix . 'label' =>
                [
                    'type' => 'singleLine',
                    'label' => trans('HCAddress::address.label'),
                ],
            $prefix . 'country_id' =>
                [
                    'type' => 'dropDownList',
                    'label' => trans('HCAddress::address.country'),
                    'required' => 1,
                    'options' => $this->countryRepository->getOptions(new HCCountryRequest())
                ],
            $prefix . 'city_id' =>
                [
                    'type' => 'dropDownList',
                    'label' => trans('HCAddress::address.city_id'),
                    'required' => 1,
                    'url' => route('admin.api.regions.city.options', [app()->getLocale()]),
                    'dependencies' => [
                        $prefix . 'country_id' => [

                        ],
                    ],
                    'new' => route('admin.api.form-manager', ['regions.city-new']),
                ],
            $prefix . 'address_line' =>
                [
                    'type' => 'singleLine',
                    'label' => trans('HCAddress::address.address_line'),
                    'required' => 1,
                ],
            $prefix . 'postal_code' =>
                [
                    'type' => 'singleLine',
                    'label' => trans('HCAddress::address.postal_code'),
                    'required' => 1,
                ],
            $prefix . 'user' =>
                [
                    'type' => 'dropDownSearchable',
                    'label' => trans('HCAddress::address.owner'),
                    'required' => 1,
                    'searchUrl' => route('admin.api.user.options'),
                    'originalLabel' => 'email'
                ],
        ];

        if (request('hc_new'))
        {
            $form[$prefix . 'hc_new'] = [
                'type' => 'singleLine',
                'required' => 1,
                'value' => 1,
                'hidden' => 1,
            ];
        }

        return $form;
    }

    /**
     * @param string $prefix
     * @return array
     */
    public function getStructureEdit(string $prefix): array
    {
        return $this->getStructureNew($prefix);
    }
}
