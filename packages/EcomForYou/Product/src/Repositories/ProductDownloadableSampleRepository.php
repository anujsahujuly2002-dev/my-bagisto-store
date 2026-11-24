<?php

namespace EcomForYou\Product\Repositories;

use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Product\Contracts\ProductDownloadableSample;
use Illuminate\Support\Str;

class ProductDownloadableSampleRepository extends Repository
{
    /**
     * Specify the model class name.
     */
    public function model(): string
    {
        return ProductDownloadableSample::class;
    }

    /**
     * Store an uploaded sample file and return the path it was saved at.
     *
     * @param  array  $data
     * @param  int  $productId
     * @return mixed
     */
    public function upload($data, $productId)
    {
        if (! request()->hasFile('file')) {
            return [];
        }

        return [
            'file' => $path = request()->file('file')->store('products/'.$productId.'/downloadable-samples', 'private'),
            'file_name' => request()->file('file')->getClientOriginalName(),
        ];
    }

    /**
     * Save the downloadable samples of a product, removing the ones left out.
     *
     * @param  EcomForYou\Product\Contracts\Product  $product
     * @return void
     */
    public function saveSamples(array $data, $product)
    {
        $previousSampleIds = $product->downloadable_samples()->pluck('id');

        if (isset($data['downloadable_samples'])) {
            foreach ($data['downloadable_samples'] as $sampleId => $data) {
                if (Str::contains($sampleId, 'sample_')) {
                    $this->create(array_merge([
                        'product_id' => $product->id,
                    ], $data));
                } else {
                    if (is_numeric($index = $previousSampleIds->search($sampleId))) {
                        $previousSampleIds->forget($index);
                    }

                    $this->update($data, $sampleId);
                }
            }
        }

        foreach ($previousSampleIds as $sampleId) {
            $this->delete($sampleId);
        }
    }
}
