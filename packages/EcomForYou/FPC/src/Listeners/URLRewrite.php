<?php

namespace EcomForYou\FPC\Listeners;

use EcomForYou\FPC\Concerns\ForgetsPages;
use EcomForYou\Marketing\Repositories\URLRewriteRepository;

class URLRewrite
{
    use ForgetsPages;

    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected URLRewriteRepository $urlRewriteRepository) {}

    /**
     * After URL rewrite update.
     *
     * @param  \EcomForYou\Marketing\Contracts\URLRewrite  $urlRewrite
     * @return void
     */
    public function afterUpdate($urlRewrite)
    {
        $this->forgetPages(['/'.$urlRewrite->request_path]);
    }

    /**
     * Before URL rewrite delete.
     *
     * @param  int  $urlRewriteId
     * @return void
     */
    public function beforeDelete($urlRewriteId)
    {
        $urlRewrite = $this->urlRewriteRepository->find($urlRewriteId);

        if (! $urlRewrite) {
            return;
        }

        $this->forgetPages(['/'.$urlRewrite->request_path]);
    }
}
