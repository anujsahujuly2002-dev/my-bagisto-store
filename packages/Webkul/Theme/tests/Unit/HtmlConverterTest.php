<?php

use Webkul\Theme\Upload\HtmlConverter;

/**
 * A template page with a header, a footer, markers, styles and scripts.
 */
function templatePage(string $main = '<h1>Welcome</h1>'): string
{
    return <<<HTML
        <!DOCTYPE html>
        <html>
            <head>
                <title>Template Home</title>
                <meta charset="utf-8">
                <link rel="stylesheet" href="css/style.css?v=2">
                <link rel="stylesheet" href="https://cdn.example.com/lib.css">
                <link rel="icon" href="favicon.ico">
                <style>.hero { background: url('images/hero.jpg'); }</style>
                <script src="js/app.js"></script>
                <script type="application/ld+json">{"@type": "Store"}</script>
            </head>
            <body>
                <header class="top">
                    <a href="index.html"><img src="images/logo.png" alt="Logo"></a>
                    <nav><a href="about.html">About</a></nav>
                    <div class="tools" @click="steal()">
                        <span>Hi {{ customer }}</span>
                        <div data-bagisto="cart">Cart placeholder</div>
                    </div>
                </header>
                <main>
                    {$main}
                </main>
                <footer><p>Copyright</p></footer>
                <script>console.log('ready');</script>
                <script type="module" src="https://cdn.example.com/module.js"></script>
            </body>
        </html>
        HTML;
}

/**
 * Convert a page against the files of a typical template.
 */
function convertPage(string $html): array
{
    return (new HtmlConverter)->convert($html, [
        'index.html', 'about.html', 'css/style.css', 'js/app.js',
        'images/hero.jpg', 'images/logo.png', 'images/banner.webp', 'images/banner@2x.webp', 'favicon.ico',
    ]);
}

/**
 * Join a region's html parts back into one string.
 */
function regionHtml(?array $parts): string
{
    return collect($parts)->where('type', 'html')->pluck('html')->implode('');
}

// ============================================================================
// Regions
// ============================================================================

it('should split the page into its header, footer and main content', function () {
    $page = convertPage(templatePage());

    expect(regionHtml($page['regions']['header']))->toContain('<header class="top"')
        ->and(regionHtml($page['regions']['footer']))->toContain('Copyright')
        ->and(regionHtml($page['regions']['main']))->toContain('Welcome')
        ->and(regionHtml($page['regions']['main']))->not->toContain('<main')
        ->and(regionHtml($page['regions']['main']))->not->toContain('Copyright')
        ->and($page['title'])->toBe('Template Home');
});

it('should leave a region out when the page has none', function () {
    $page = convertPage('<html><body><h1>Only content</h1></body></html>');

    expect($page['regions']['header'])->toBeNull()
        ->and($page['regions']['footer'])->toBeNull()
        ->and(regionHtml($page['regions']['main']))->toContain('Only content');
});

it('should take the regions a template names over the elements it uses', function () {
    $page = convertPage('<html><body><div data-bagisto-region="header">Named header</div><header>Other</header><section data-bagisto-region="main">Named main</section></body></html>');

    expect(regionHtml($page['regions']['header']))->toContain('Named header')
        ->and(regionHtml($page['regions']['main']))->toBe('Named main');
});

// ============================================================================
// Urls
// ============================================================================

it('should point the urls of theme files at the asset placeholder', function () {
    $page = convertPage(templatePage('<img src="./images/banner.webp"><video poster="images/hero.jpg"><source srcset="images/banner.webp 1x, images/banner@2x.webp 2x"></video><div style="background: url(images/hero.jpg)"></div>'));

    $main = regionHtml($page['regions']['main']);

    expect(collect($page['images'])->pluck('src')->all())->toBe(['__theme_asset__/images/logo.png', '__theme_asset__/images/banner.webp'])
        ->and($main)->toContain('poster="__theme_asset__/images/hero.jpg"')
        ->and($main)->toContain('__theme_asset__/images/banner.webp 1x, __theme_asset__/images/banner%402x.webp 2x')
        ->and($main)->toContain('url(__theme_asset__/images/hero.jpg)');
});

it('should point a link to the template home page at the storefront home', function () {
    expect(regionHtml(convertPage(templatePage())['regions']['header']))->toContain('href="__theme_home__"');
});

it('should leave urls alone that are not theme files', function () {
    $page = convertPage(templatePage('<a href="https://example.com">Out</a><a href="#top">Top</a><img src="missing.png"><a href="../outside.css">Up</a>'));

    $main = regionHtml($page['regions']['main']);

    expect($main)->toContain('href="https://example.com"')
        ->and($main)->toContain('href="#top"')
        ->and($main)->toContain('href="../outside.css"')
        ->and(regionHtml($page['regions']['header']))->toContain('href="about.html"')
        ->and(collect($page['images'])->pluck('src')->all())->toContain('missing.png');
});

// ============================================================================
// Styles And Scripts
// ============================================================================

it('should collect the stylesheets in order with their urls resolved', function () {
    expect(convertPage(templatePage())['styles'])->toBe([
        ['href' => '__theme_asset__/css/style.css?v=2'],
        ['href' => 'https://cdn.example.com/lib.css'],
        ['css' => ".hero { background: url('__theme_asset__/images/hero.jpg'); }"],
    ]);
});

it('should collect the scripts in order and drop the ones that are not javascript', function () {
    $page = convertPage(templatePage());

    expect($page['scripts'])->toBe([
        ['module' => false, 'src' => '__theme_asset__/js/app.js'],
        ['module' => false, 'code' => "console.log('ready');"],
        ['module' => true, 'src' => 'https://cdn.example.com/module.js'],
    ]);

    $html = regionHtml($page['regions']['header']).regionHtml($page['regions']['main']).regionHtml($page['regions']['footer']);

    expect($html)->not->toContain('<script')
        ->and($html)->not->toContain('<link');
});

// ============================================================================
// Markers
// ============================================================================

it('should turn a marker into a slot in place of its content', function () {
    $header = convertPage(templatePage())['regions']['header'];

    $slot = collect($header)->firstWhere('type', 'slot');

    expect($slot)->toBe(['type' => 'slot', 'slot' => 'cart', 'params' => []])
        ->and(regionHtml($header))->toContain('<div data-bagisto="cart">')
        ->and(regionHtml($header))->not->toContain('Cart placeholder');
});

it('should read a product marker\'s settings within their limits', function () {
    $page = convertPage(templatePage('<div data-bagisto="products" data-type="featured" data-title="Top picks" data-limit="99"></div><div data-bagisto="products" data-type="secret"></div>'));

    $slots = collect($page['regions']['main'])->where('type', 'slot')->values();

    expect($slots[0]['params'])->toBe(['filter' => 'featured', 'title' => 'Top picks', 'limit' => 24])
        ->and($slots[1]['params'])->toBe(['filter' => 'new', 'title' => '', 'limit' => 12]);
});

it('should leave an element with an unknown marker as markup', function () {
    $main = convertPage(templatePage('<div data-bagisto="checkout">Static</div>'))['regions']['main'];

    expect(collect($main)->where('type', 'slot'))->toBeEmpty()
        ->and(regionHtml($main))->toContain('Static');
});

// ============================================================================
// Keeping Markup Inert
// ============================================================================

it('should keep Vue from compiling markup that holds no marker', function () {
    $page = convertPage(templatePage('<p>{{ 1111 * 3 }}</p>'));

    expect(regionHtml($page['regions']['footer']))->toContain('<footer v-pre="">')
        ->and(regionHtml($page['regions']['main']))->toContain('<p v-pre="">{{ 1111 * 3 }}</p>');
});

it('should strip Vue bindings and shield expressions on the way to a marker', function () {
    $header = regionHtml(convertPage(templatePage())['regions']['header']);

    expect($header)->not->toContain('@click')
        ->and($header)->toContain('<span v-pre="">Hi {{ customer }}</span>')
        ->and($header)->toContain('<nav v-pre="">');
});

it('should drop comments and processing instructions', function () {
    $main = regionHtml(convertPage(templatePage('<!-- note --><?php echo "x"; ?><p>Kept</p>'))['regions']['main']);

    expect($main)->not->toContain('<!--')
        ->and($main)->not->toContain('<?php')
        ->and($main)->not->toContain('<?')
        ->and($main)->toContain('Kept');
});

// ============================================================================
// Editable Images
// ============================================================================

it('should put every image behind a placeholder and list it with its template url', function () {
    $page = convertPage(templatePage('<img src="images/hero.jpg" alt="Hero"><img src="https://cdn.example.com/photo.jpg">'));

    expect($page['images'])->toBe([
        ['id' => 'image-1', 'src' => HtmlConverter::ASSET_PLACEHOLDER.'images/logo.png', 'alt' => 'Logo', 'region' => 'header'],
        ['id' => 'image-2', 'src' => HtmlConverter::ASSET_PLACEHOLDER.'images/hero.jpg', 'alt' => 'Hero', 'region' => 'main'],
        ['id' => 'image-3', 'src' => 'https://cdn.example.com/photo.jpg', 'alt' => '', 'region' => 'main'],
    ])
        ->and(regionHtml($page['regions']['header']))->toContain('src="'.HtmlConverter::IMAGE_PLACEHOLDER.'image-1__"')
        ->and(regionHtml($page['regions']['main']))->toContain('src="'.HtmlConverter::IMAGE_PLACEHOLDER.'image-2__"');
});

it('should take the name a template gives an image as its id, kept unique', function () {
    $page = convertPage(templatePage('<img src="images/hero.jpg" data-bagisto-image="Main Hero"><img src="images/banner.webp" data-bagisto-image="main-hero">'));

    expect(collect($page['images'])->pluck('id')->all())->toBe(['image-1', 'main-hero', 'main-hero-2'])
        ->and(regionHtml($page['regions']['main']))->not->toContain('data-bagisto-image');
});

it('should drop an image\'s other sources so a replacement shows', function () {
    $main = regionHtml(convertPage(templatePage('<picture><source srcset="images/banner.webp"><img src="images/hero.jpg" srcset="images/banner@2x.webp 2x" sizes="100vw"></picture>'))['regions']['main']);

    expect($main)->not->toContain('<source')
        ->and($main)->not->toContain('srcset')
        ->and($main)->not->toContain('sizes=');
});

it('should leave an image inside a marker alone', function () {
    $page = convertPage(templatePage('<div data-bagisto="logo"><img src="images/hero.jpg"></div>'));

    expect(collect($page['images'])->pluck('src')->all())->not->toContain(HtmlConverter::ASSET_PLACEHOLDER.'images/hero.jpg');
});

// ============================================================================
// Sections
// ============================================================================

it('should describe each top-level block of the main content as a section', function () {
    $page = convertPage(templatePage('<section class="hero"><h1>Big Sale</h1><img src="images/hero.jpg"></section><section data-bagisto-section="Our Story"><h2>Ignored Heading</h2><p>Story</p></section><div><h2>Picks</h2><div data-bagisto="products" data-type="featured" data-limit="8"></div></div><p>Plain</p>'));

    expect($page['blocks'])->toHaveCount(4)
        ->and($page['blocks'][0])->toMatchArray(['type' => 'static_content', 'name' => 'Big Sale'])
        ->and($page['blocks'][0]['html'])->toContain('<section class="hero">')
        ->and($page['blocks'][0]['html'])->toContain(HtmlConverter::IMAGE_PLACEHOLDER.'image-2__')
        ->and($page['blocks'][1])->toMatchArray(['type' => 'static_content', 'name' => 'Our Story'])
        ->and($page['blocks'][2])->toBe(['type' => 'product_carousel', 'name' => 'Picks', 'title' => 'Picks', 'filter' => 'featured', 'limit' => 8])
        ->and($page['blocks'][3])->toMatchArray(['type' => 'static_content', 'name' => null]);
});

it('should keep the markup of a block free of the attributes added for the storefront app', function () {
    $html = convertPage(templatePage('<section><p>{{ 1 + 1 }}</p></section>'))['blocks'][0]['html'];

    expect($html)->not->toContain('v-pre');
});
