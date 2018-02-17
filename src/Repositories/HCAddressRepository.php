<?php

declare(strict_types = 1);

namespace HoneyComb\Addresses\Repositories;

use HoneyComb\Addresses\Http\Requests\HCAddressRequest;
use HoneyComb\Addresses\Models\HCAddress;
use HoneyComb\Core\Repositories\Traits\HCQueryBuilderTrait;
use HoneyComb\Starter\Repositories\HCBaseRepository;
use Illuminate\Support\Collection;

class HCAddressRepository extends HCBaseRepository
{
    use HCQueryBuilderTrait;

    /**
     * @return string
     */
    public function model(): string
    {
        return HCAddress::class;
    }

    /**
     * Soft deleting records
     * @param $ids
     */
    public function deleteSoft(array $ids): void
    {
        $records = $this->makeQuery()->whereIn('id', $ids)->get();

        foreach ($records as $record) {
            /** @var HCAddress $record */
            $record->delete();
        }
    }

    /**
     * Restore soft deleted records
     *
     * @param array $ids
     * @return void
     */
    public function restore(array $ids): void
    {
        $records = $this->makeQuery()->withTrashed()->whereIn('id', $ids)->get();

        foreach ($records as $record) {
            /** @var HCAddress $record */
            $record->restore();
        }
    }

    /**
     * Force delete records by given id
     *
     * @param array $ids
     * @return void
     * @throws \Exception
     */
    public function deleteForce(array $ids): void
    {
        $records = $this->makeQuery()->withTrashed()->whereIn('id', $ids)->get();

        foreach ($records as $record) {
            /** @var HCAddress $record */
            $record->forceDelete();
        }
    }

    /**
     * @param \HoneyComb\Addresses\Http\Requests\HCAddressRequest $request
     * @return \Illuminate\Support\Collection
     */
    public function getOptions (HCAddressRequest $request): Collection
    {
        if (!$request->readyForOptions())
            return new Collection();

        return $this->createBuilderQuery($request)->get()->map (function ($record){

            return $this->formatForOptions($record);
        });
    }

    public function formatForOptions($record)
    {
        return [
            'id' => $record->id,
            'label' => $record->full_address
        ];
    }
}