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

namespace HoneyComb\Addresses\Http\Controllers\Admin;

use HoneyComb\Addresses\Events\Admin\HCAddressCreated;
use HoneyComb\Addresses\Events\Admin\HCAddressUpdated;
use HoneyComb\Addresses\Events\Admin\HCAddressRestored;
use HoneyComb\Addresses\Events\Admin\HCAddressSoftDeleted;
use HoneyComb\Addresses\Events\Admin\HCAddressForceDeleted;
use HoneyComb\Addresses\Services\HCAddressService;
use HoneyComb\Addresses\Http\Requests\Admin\HCAddressRequest;
use HoneyComb\Addresses\Models\HCAddress;

use HoneyComb\Core\Http\Controllers\HCBaseController;
use HoneyComb\Core\Http\Controllers\Traits\HCAdminListHeaders;
use HoneyComb\Starter\Helpers\HCFrontendResponse;
use Illuminate\Database\Connection;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Class HCAddressController
 * @package HoneyComb\Addresses\Http\Controllers\Admin
 */
class HCAddressController extends HCBaseController
{
    use HCAdminListHeaders;

    /**
     * @var HCAddressService
     */
    protected $service;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var HCFrontendResponse
     */
    private $response;

    /**
     * HCAddressController constructor.
     * @param Connection $connection
     * @param HCFrontendResponse $response
     * @param HCAddressService $service
     */
    public function __construct(Connection $connection, HCFrontendResponse $response, HCAddressService $service)
    {
        $this->connection = $connection;
        $this->response = $response;
        $this->service = $service;
    }

    /**
     * Admin panel page view
     *
     * @return View
     */
    public function index(): View
    {
        $config = [
            'title' => trans('HCAddress::address.page_title'),
            'url' => route('admin.api.address'),
            'form' => route('admin.api.form-manager', ['address']),
            'headers' => $this->getTableColumns(),
            'actions' => $this->getActions('honey_comb_addresses_address'),
        ];

        return view('HCCore::admin.service.index', ['config' => $config]);
    }

    /**
     * Get admin page table columns settings
     *
     * @return array
     */
    public function getTableColumns(): array
    {
        $columns = [
            'label' => $this->headerText(trans('HCAddress::address.label')),
            'address_line' => $this->headerText(trans('HCAddress::address.address_line')),
            'postal_code' => $this->headerText(trans('HCAddress::address.postal_code')),
            'city.translation.label' => $this->headerText(trans('HCAddress::address.city_id')),
        ];

        return $columns;
    }

    /**
     * @param string $id
     * @return HCAddress|null
     */
    public function getById(string $id): ? HCAddress
    {
        return $this->service->getRepository()->findOneBy(['id' => $id]);
    }

    /**
     * Creating data list
     * @param HCAddressRequest $request
     * @return JsonResponse
     */
    public function getListPaginate(HCAddressRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->getRepository()->getListPaginate($request)
        );
    }

    /**
     * Create record
     *
     * @param HCAddressRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(HCAddressRequest $request): JsonResponse
    {
        $this->connection->beginTransaction();

        try {
            /** @var HCAddress $record */
            $record = $this->service->getRepository()->create($request->getRecordData());

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            return $this->response->error($e->getMessage());
        }

        event(new HCAddressCreated($record));

        return $this->response->success("Created", $this->responseData($request, $record->id));
    }

    /**
     * @param \HoneyComb\Addresses\Http\Requests\Admin\HCAddressRequest $request
     * @param string $id
     * @return array|null
     */
    protected function responseData(HCAddressRequest $request, string $id)
    {
        if ($request->isResponseForOptions())
            return $this->service->getRepository()->formatForOptions($this->getById($id));

        return null;
    }


    /**
     * Update record
     *
     * @param HCAddressRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(HCAddressRequest $request, string $id): JsonResponse
    {
        $record = $this->service->getRepository()->findOneBy(['id' => $id]);
        $record->update($request->getRecordData());

        if ($record) {
            $record = $this->service->getRepository()->find($id);

            event(new HCAddressUpdated($record));
        }

        return $this->response->success("Updated");
    }


    /**
     * Soft delete record
     *
     * @param HCAddressRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteSoft(HCAddressRequest $request): JsonResponse
    {
        $this->connection->beginTransaction();

        try {
            $deletedIds = $this->service->getRepository()->deleteSoft($request->getListIds());

            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();

            return $this->response->error($exception->getMessage());
        }

        event(new HCAddressSoftDeleted($deletedIds));

        return $this->response->success('Successfully deleted');
    }


    /**
     * Restore record
     *
     * @param HCAddressRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function restore(HCAddressRequest $request): JsonResponse
    {
        $this->connection->beginTransaction();

        try {
            $restoredIds = $this->service->getRepository()->restore($request->getListIds());

            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();

            return $this->response->error($exception->getMessage());
        }

        event(new HCAddressRestored($restoredIds));

        return $this->response->success('Successfully restored');
    }


    /**
     * Force delete record
     *
     * @param HCAddressRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteForce(HCAddressRequest $request): JsonResponse
    {
        $this->connection->beginTransaction();

        try {
            $deleted = $this->service->getRepository()->deleteForce($request->getListIds());

            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();

            return $this->response->error($exception->getMessage());
        }

        event(new HCAddressForceDeleted($deleted));

        return $this->response->success('Successfully deleted');
    }

    /**
     * @param \HoneyComb\Addresses\Http\Requests\Admin\HCAddressRequest $request
     * @return \Illuminate\Support\Collection
     */
    public function getOptions(HCAddressRequest $request)
    {
        return $this->service->getRepository()->getOptions($request);
    }
}