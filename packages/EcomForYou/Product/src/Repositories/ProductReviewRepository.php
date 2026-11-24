<?php

namespace EcomForYou\Product\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Product\Contracts\ProductReview;
use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductReviewRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductReviewAttachmentRepository $productReviewAttachmentRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return ProductReview::class;
    }

    /**
     * Create the review together with its attachments, so an attachment that cannot be
     * sanitized leaves no review behind.
     */
    public function createWithAttachments(array $data): ProductReview
    {
        return DB::transaction(function () use ($data) {
            $review = $this->create($data);

            $this->productReviewAttachmentRepository->upload($data['attachments'] ?? [], $review);

            return $review;
        });
    }

    /**
     * Retrieve the reviews a customer has written.
     */
    public function getCustomerReviews(int $customerId): LengthAwarePaginator
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->with('product')
            ->paginate(5);
    }
}
