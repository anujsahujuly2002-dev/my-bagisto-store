<?php

use EcomForYou\Shop\Http\Controllers\WebMcpController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'webmcp'], function () {
    Route::get('product', [WebMcpController::class, 'product'])->name('shop.webmcp.product');

    Route::get('wishlist/add', [WebMcpController::class, 'addToWishlist'])->name('shop.webmcp.wishlist.add');
});
