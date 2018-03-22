<?php

declare(strict_types = 1);

namespace HoneyComb\Addresses\Http\Controllers\Admin;

use HoneyComb\Addresses\Services\HCAddressService;
use HoneyComb\Addresses\Http\Requests\HCAddressRequest;
use HoneyComb\Addresses\Models\HCAddress;

use HoneyComb\Core\Http\Controllers\HCBaseController;
use HoneyComb\Core\Http\Controllers\Traits\HCAdminListHeaders;
use HoneyComb\Starter\Helpers\HCFrontendResponse;
use Illuminate\Database\Connection;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

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
            /** @var HCAddress $model */
            $model = $this->service->getRepository()->create($request->getRecordData());

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            return $this->response->error($e->getMessage());
        }

        return $this->response->success("Created", $this->responseData($request, $model->id));
    }

    /**
     * @param \HoneyComb\Addresses\Http\Requests\HCAddressRequest $request
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
        $model = $this->service->getRepository()->findOneBy(['id' => $id]);
        $model->update($request->getRecordData());

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
            $this->service->getRepository()->deleteSoft($request->getListIds());

            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();

            return $this->response->error($exception->getMessage());
        }

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
            $this->service->getRepository()->restore($request->getListIds());

            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();

            return $this->response->error($exception->getMessage());
        }

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
            $this->service->getRepository()->deleteForce($request->getListIds());

            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();

            return $this->response->error($exception->getMessage());
        }

        return $this->response->success('Successfully deleted');
    }

    /**
     * @param \HoneyComb\Addresses\Http\Requests\HCAddressRequest $request
     * @return \Illuminate\Support\Collection
     */
    public function getOptions(HCAddressRequest $request)
    {
        return $this->service->getRepository()->getOptions($request);
    }
}