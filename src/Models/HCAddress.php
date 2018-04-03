<?php
/**
 * @copyright 2018 interactivesolutions
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the 'Software'), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
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

namespace HoneyComb\Addresses\Models;

use HoneyComb\Core\Models\HCUser;
use HoneyComb\Core\Models\Traits\HCOwnership;
use HoneyComb\Regions\Models\HCCity;
use HoneyComb\Starter\Models\HCUuidModel;
use Illuminate\Database\Eloquent\Relations\HasOne;


/**
 * Class HCAddress
 * @package HoneyComb\Addresses\Models
 */
class HCAddress extends HCUuidModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'hc_address';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'address_line',
        'postal_code',
        'city_id',
        'user_id',
        'label',
    ];

    /**
     * @var array
     */
    protected $with = [
        'city',
        'user',
    ];

    protected $appends = [
        'full_address',
        'country_id',
    ];

    /**
     * @return string
     */
    public function getCountryIdAttribute(): string
    {
        return $this->city->country_id;
    }

    /**
     * @return HasOne
     */
    public function city(): HasOne
    {
        return $this->hasOne(HCCity::class, 'id', 'city_id');
    }

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(HCUser::class, 'id', 'user_id');
    }

    /**
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        return $this->address_line . ', ' . $this->city->translation->label;
    }
}