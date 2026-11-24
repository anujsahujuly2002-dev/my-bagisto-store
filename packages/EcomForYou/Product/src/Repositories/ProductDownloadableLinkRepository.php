<?php

namespace EcomForYou\Product\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Product\Contracts\ProductDownloadableLink;
use EcomForYou\Product\Models\Product;
use Illuminate\Support\Str;

class ProductDownloadableLinkRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return ProductDownloadableLink::class;
    }

    /**
     * Upload.
     *
     * @param  array  $data
     * @param  int  $productId
     * @return array
     */
    public function upload($data, $productId)
    {
        foreach ($data as $type => $file) {
            if (! request()->hasFile($type)) {
                continue;
            }

            return [
                $type => request()->file($type)->store('products/'.$productId.'/downloadable-links', 'private'),
                $type.'_name' => $file->getClientOriginalName(),
            ];
        }

        return [];
    }

    /**
     * Save links.
     *
     * @param  Product  $product
     * @return void
     */
    public function saveLinks(array $data, $product)
    {
        $previousLinkIds = $product->downloadable_links()->pluck('id');

        if (isset($data['downloadable_links'])) {
            foreach ($data['downloadable_links'] as $linkId => $data) {
                if (Str::contains($linkId, 'link_')) {
                    $this->create(array_merge([
                        'product_id' => $product->id,
                    ], $data));
                } else {
                    if (is_numeric($index = $previousLinkIds->search($linkId))) {
                        $previousLinkIds->forget($index);
                    }

                    $this->update($data, $linkId);
                }
            }
        }

        foreach ($previousLinkIds as $linkId) {
            $this->delete($linkId);
        }
    }
}
