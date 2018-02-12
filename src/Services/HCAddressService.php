<?php

declare(strict_types = 1);

namespace HoneyComb\Addresses\Services;

use HoneyComb\Addresses\Repositories\HCAddressRepository;

class HCAddressService
{
    /**
     * @var HCAddressRepository
     */
    private $repository;

    /**
     * HCAddressService constructor.
     * @param HCAddressRepository $repository
     */
    public function __construct(HCAddressRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return HCAddressRepository
     */
    public function getRepository(): HCAddressRepository
    {
        return $this->repository;
    }
}