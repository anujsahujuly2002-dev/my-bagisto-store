<?php

namespace EcomForYou\RMA\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\RMA\Contracts\RMAReason;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;

class RMAReasonRepository extends Repository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        protected RMAReasonResolutionRepository $rmaReasonResolutionRepository,
        Container $container,
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return RMAReason::class;
    }

    /**
     * Get RMA Reasons by Resolution Type
     */
    public function getRMAReasonsByResolutionType(string $resolutionType): Collection
    {
        $existResolutions = $this->rmaReasonResolutionRepository
            ->where('resolution_type', $resolutionType)
            ->pluck('rma_reason_id');

        return $this->whereIn('id', $existResolutions)
            ->where('status', 1)
            ->get();
    }
}
