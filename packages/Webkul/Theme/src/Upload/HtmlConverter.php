<?php

namespace Webkul\Theme\Upload;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Dom\XPath;
use Illuminate\Support\Str;

class HtmlConverter
{
    /**
     * The placeholder an asset url of the theme is stored behind, resolved when the page renders.
     */
    public const ASSET_PLACEHOLDER = '__theme_asset__/';

    /**
     * The placeholder an editable image's url is stored behind, followed by its id and a closing `__`.
     */
    public const IMAGE_PLACEHOLDER = '__theme_image__';

    /**
     * The longest id an editable image may carry.
     */
    public const IMAGE_ID_MAX_LENGTH = 40;

    /**
     * The placeholder a link to the template's own home page is stored behind.
     */
    public const HOME_PLACEHOLDER = '__theme_home__';

    /**
     * The marker values a template can put in `data-bagisto` to have a storefront component rendered there.
     */
    public const SLOTS = ['logo', 'search', 'cart', 'account', 'categories', 'sections', 'products'];

    /**
     * The product filters a `products` marker can name in `data-type`.
     */
    public const PRODUCT_FILTERS = ['new', 'featured'];

    /**
     * The attributes that carry a url, rewritten when they point at a file of the theme.
     */
    public const URL_ATTRIBUTES = ['src', 'href', 'poster', 'data-src', 'data-background', 'data-bg'];

    /**
     * The comment a marker's content is swapped for until the region is split into parts.
     */
    protected const SLOT_TOKEN = 'bagisto-slot:';

    /**
     * The files of the archive, keyed by path, used to tell a theme asset from any other url.
     *
     * @var array
     */
    protected $files = [];

    /**
     * The markers met while converting, in the order their tokens were numbered.
     *
     * @var array
     */
    protected $slots = [];

    /**
     * Convert a template page into the regions, styles and scripts an uploaded theme renders.
     */
    public function convert(string $html, array $files): array
    {
        $this->files = array_flip($files);

        $this->slots = [];

        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);

        $this->removeComments($document);

        $title = trim((string) $document->title);

        $styles = $this->takeStyles($document);

        $scripts = $this->takeScripts($document);

        foreach ($document->querySelectorAll('base, meta, title') as $element) {
            $element->remove();
        }

        $body = $document->body;

        if (! $body) {
            return ['title' => $title, 'regions' => ['header' => null, 'footer' => null, 'main' => []], 'styles' => $styles, 'scripts' => $scripts, 'images' => [], 'blocks' => []];
        }

        $this->rewriteUrls($body);

        $images = $this->takeImages($body);

        $header = $body->querySelector('[data-bagisto-region="header"]') ?? $body->querySelector('header');

        $footers = $body->querySelectorAll('[data-bagisto-region="footer"], footer');

        $footer = $footers->length ? $footers->item($footers->length - 1) : null;

        if ($header && $footer && $header->contains($footer)) {
            $footer = null;
        }

        $headerParts = $header ? $this->outerParts($document, $header) : null;

        $footerParts = $footer ? $this->outerParts($document, $footer) : null;

        $header?->remove();

        $footer?->remove();

        $main = $body->querySelector('[data-bagisto-region="main"]') ?? $body->querySelector('main') ?? $body;

        $blocks = $this->takeBlocks($document, $main);

        return [
            'title' => $title,
            'regions' => [
                'header' => $headerParts,
                'footer' => $footerParts,
                'main' => $this->innerParts($document, $main),
            ],
            'styles' => $styles,
            'scripts' => $scripts,
            'images' => $images,
            'blocks' => $blocks,
        ];
    }

    /**
     * Put every image outside a marker behind a placeholder an admin can point at a new file, returning the
     * images in page order with their template urls.
     */
    protected function takeImages(Element $body): array
    {
        $images = [];

        foreach (iterator_to_array($body->querySelectorAll('img[src]')) as $image) {
            if ($image->closest('[data-bagisto]')) {
                continue;
            }

            $id = $this->imageId($image, array_keys($images));

            $images[$id] = [
                'id' => $id,
                'src' => (string) $image->getAttribute('src'),
                'alt' => Str::limit(trim((string) $image->getAttribute('alt')), 150, ''),
                'region' => $this->imageRegion($image),
            ];

            $image->setAttribute('src', self::IMAGE_PLACEHOLDER.$id.'__');

            foreach (['srcset', 'sizes', 'data-bagisto-image'] as $attribute) {
                $image->removeAttribute($attribute);
            }

            if (strtolower((string) $image->parentElement?->localName) === 'picture') {
                foreach (iterator_to_array($image->parentElement->querySelectorAll('source')) as $source) {
                    $source->remove();
                }
            }
        }

        return array_values($images);
    }

    /**
     * Describe each top-level block of the main content as the section the Appearance editor would hold it in: a
     * product carousel for a block with a products marker, and static markup for any other.
     */
    protected function takeBlocks(HTMLDocument $document, Element $main): array
    {
        $blocks = [];

        foreach (array_filter(iterator_to_array($main->childNodes), fn ($node) => $node instanceof Element) as $child) {
            $markers = array_values(array_filter(
                [$child, ...iterator_to_array($child->querySelectorAll('[data-bagisto="products"]'))],
                fn (Element $element) => $element->getAttribute('data-bagisto') === 'products'
            ));

            $name = $this->blockName($child);

            if ($markers) {
                foreach ($markers as $marker) {
                    $params = $this->slotParams('products', $marker);

                    $blocks[] = [
                        'type' => 'product_carousel',
                        'name' => $params['title'] ?: $name,
                        'title' => $params['title'] ?: (string) $name,
                        'filter' => $params['filter'],
                        'limit' => $params['limit'],
                    ];
                }

                continue;
            }

            if (in_array($child->getAttribute('data-bagisto'), self::SLOTS, true)) {
                continue;
            }

            $html = trim($document->saveHtml($child));

            if ($html === '') {
                continue;
            }

            $blocks[] = [
                'type' => 'static_content',
                'name' => $name,
                'html' => $html,
            ];
        }

        return $blocks;
    }

    /**
     * The name a block goes by in the editor: the one its template gives it, else its first heading, else none.
     */
    protected function blockName(Element $block): ?string
    {
        $name = trim((string) $block->getAttribute('data-bagisto-section'));

        if ($name === '') {
            $heading = $block->matches('h1, h2, h3') ? $block : $block->querySelector('h1, h2, h3');

            $name = trim(preg_replace('/\s+/', ' ', (string) $heading?->textContent));
        }

        return $name === '' ? null : Str::limit($name, 100, '');
    }

    /**
     * The region of the page an image sits in: the one its template names, else header or footer by the element
     * around it, else the main content.
     */
    protected function imageRegion(Element $image): string
    {
        $named = $image->closest('[data-bagisto-region]')?->getAttribute('data-bagisto-region');

        if (in_array($named, ['header', 'footer', 'main'], true)) {
            return $named;
        }

        return match (true) {
            ! is_null($image->closest('header')) => 'header',
            ! is_null($image->closest('footer')) => 'footer',
            default => 'main',
        };
    }

    /**
     * The id of an editable image, from the name its template gives it or its place on the page, unique on the page.
     */
    protected function imageId(Element $image, array $taken): string
    {
        $id = Str::limit(Str::slug((string) $image->getAttribute('data-bagisto-image')), self::IMAGE_ID_MAX_LENGTH, '');

        $id = trim($id, '-') ?: 'image-'.(count($taken) + 1);

        $base = $id;

        $suffix = 2;

        while (in_array($id, $taken, true)) {
            $id = Str::limit($base, self::IMAGE_ID_MAX_LENGTH - 4, '').'-'.$suffix++;
        }

        return $id;
    }

    /**
     * Drop every comment, which also takes any processing instruction the parser read as one.
     */
    protected function removeComments(HTMLDocument $document): void
    {
        foreach (iterator_to_array((new XPath($document))->query('//comment()')) as $comment) {
            $comment->remove();
        }
    }

    /**
     * Take the stylesheets and style blocks out of the page, in order, with their urls resolved.
     */
    protected function takeStyles(HTMLDocument $document): array
    {
        $styles = [];

        foreach (iterator_to_array($document->querySelectorAll('link[rel~="stylesheet" i], style')) as $element) {
            if ($element->localName === 'link') {
                $href = $this->resolveUrl((string) $element->getAttribute('href'));

                if ($href !== '') {
                    $styles[] = ['href' => $href];
                }
            } else {
                $styles[] = ['css' => $this->rewriteCssUrls($element->textContent)];
            }

            $element->remove();
        }

        foreach (iterator_to_array($document->querySelectorAll('link')) as $element) {
            $element->remove();
        }

        return $styles;
    }

    /**
     * Take the scripts out of the page, in order, so they can run once the storefront app has mounted.
     */
    protected function takeScripts(HTMLDocument $document): array
    {
        $scripts = [];

        foreach (iterator_to_array($document->querySelectorAll('script')) as $element) {
            $type = Str::lower(trim((string) $element->getAttribute('type')));

            if (! in_array($type, ['', 'text/javascript', 'application/javascript', 'module'], true)) {
                $element->remove();

                continue;
            }

            $script = ['module' => $type === 'module'];

            if ($element->hasAttribute('src')) {
                $script['src'] = $this->resolveUrl((string) $element->getAttribute('src'));
            } else {
                $script['code'] = $element->textContent;
            }

            $scripts[] = $script;

            $element->remove();
        }

        return $scripts;
    }

    /**
     * Rewrite every url in the body that points at a file of the theme.
     */
    protected function rewriteUrls(Element $root): void
    {
        foreach ([$root, ...iterator_to_array($root->querySelectorAll('*'))] as $element) {
            foreach (self::URL_ATTRIBUTES as $attribute) {
                if ($element->hasAttribute($attribute)) {
                    $element->setAttribute($attribute, $this->resolveUrl((string) $element->getAttribute($attribute)));
                }
            }

            if ($element->hasAttribute('srcset')) {
                $element->setAttribute('srcset', $this->rewriteSrcset((string) $element->getAttribute('srcset')));
            }

            if ($element->hasAttribute('style')) {
                $element->setAttribute('style', $this->rewriteCssUrls((string) $element->getAttribute('style')));
            }
        }
    }

    /**
     * Resolve a url to the theme's asset placeholder when it names a file of the archive, leaving any other alone.
     */
    protected function resolveUrl(string $url): string
    {
        $url = trim($url);

        if (
            $url === ''
            || str_starts_with($url, '#')
            || str_starts_with($url, '/')
            || preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)
        ) {
            return $url;
        }

        $suffix = '';

        if (preg_match('/^([^?#]*)([?#].*)$/', $url, $matches)) {
            [, $url, $suffix] = $matches;
        }

        $path = $this->normalizePath(rawurldecode($url));

        if (is_null($path)) {
            return $url.$suffix;
        }

        if (in_array($path, ['index.html', 'index.htm'], true)) {
            return self::HOME_PLACEHOLDER;
        }

        if (
            isset($this->files[$path])
            && ! in_array(Str::lower(pathinfo($path, PATHINFO_EXTENSION)), ['html', 'htm'], true)
        ) {
            return self::ASSET_PLACEHOLDER.str_replace('%2F', '/', rawurlencode($path)).$suffix;
        }

        return $url.$suffix;
    }

    /**
     * Collapse the dot segments of a relative path, or return null when it climbs out of the archive.
     */
    protected function normalizePath(string $path): ?string
    {
        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                if (empty($segments)) {
                    return null;
                }

                array_pop($segments);

                continue;
            }

            $segments[] = $segment;
        }

        return empty($segments) ? null : implode('/', $segments);
    }

    /**
     * Rewrite each candidate of a srcset.
     */
    protected function rewriteSrcset(string $srcset): string
    {
        return collect(explode(',', $srcset))
            ->map(function ($candidate) {
                $parts = preg_split('/\s+/', trim($candidate), 2);

                return trim($this->resolveUrl($parts[0]).' '.($parts[1] ?? ''));
            })
            ->implode(', ');
    }

    /**
     * Rewrite the `url()` references of a stylesheet written into the page.
     */
    protected function rewriteCssUrls(string $css): string
    {
        return preg_replace_callback(
            '/url\(\s*([\'"]?)([^\'")]+)\1\s*\)/i',
            fn ($matches) => 'url('.$matches[1].$this->resolveUrl($matches[2]).$matches[1].')',
            $css
        );
    }

    /**
     * Split an element, tags and all, into its parts.
     */
    protected function outerParts(HTMLDocument $document, Element $element): array
    {
        $this->protect($element);

        return $this->split($document->saveHtml($element));
    }

    /**
     * Split the content of an element, without its own tags, into its parts.
     */
    protected function innerParts(HTMLDocument $document, Element $element): array
    {
        $html = '';

        foreach (iterator_to_array($element->childNodes) as $child) {
            $child = $this->protectNode($document, $child);

            $html .= $document->saveHtml($child);
        }

        return $this->split($html);
    }

    /**
     * Keep the storefront's Vue app from compiling the template's markup, except where a marker needs it to.
     */
    protected function protect(Element $element): void
    {
        $slot = $element->getAttribute('data-bagisto');

        if (in_array($slot, self::SLOTS, true)) {
            $this->stripVueAttributes($element);

            $element->replaceChildren($element->ownerDocument->createComment(self::SLOT_TOKEN.count($this->slots)));

            $this->slots[] = ['slot' => $slot, 'params' => $this->slotParams($slot, $element)];

            return;
        }

        if (! $this->containsMarker($element)) {
            $element->setAttribute('v-pre', '');

            return;
        }

        $this->stripVueAttributes($element);

        foreach (iterator_to_array($element->childNodes) as $child) {
            $this->protectNode($element->ownerDocument, $child);
        }
    }

    /**
     * Protect a child node, wrapping text that Vue would read as an expression, and return what now stands in its place.
     */
    protected function protectNode($document, Node $node): Node
    {
        if ($node instanceof Element) {
            $this->protect($node);

            return $node;
        }

        if (
            $node->nodeType === XML_TEXT_NODE
            && str_contains($node->textContent, '{{')
        ) {
            $span = $document->createElement('span');

            $span->setAttribute('v-pre', '');

            $node->replaceWith($span);

            $span->append($node);

            return $span;
        }

        return $node;
    }

    /**
     * Whether a marker sits anywhere inside the element.
     */
    protected function containsMarker(Element $element): bool
    {
        foreach ($element->querySelectorAll('[data-bagisto]') as $marker) {
            if (in_array($marker->getAttribute('data-bagisto'), self::SLOTS, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove the attributes Vue would read as directives or bindings.
     */
    protected function stripVueAttributes(Element $element): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (Str::startsWith($attribute->name, ['v-', ':', '@', '#'])) {
                $element->removeAttribute($attribute->name);
            }
        }
    }

    /**
     * The settings a marker takes from its own attributes.
     */
    protected function slotParams(string $slot, Element $element): array
    {
        if ($slot !== 'products') {
            return [];
        }

        $filter = $element->getAttribute('data-type');

        return [
            'filter' => in_array($filter, self::PRODUCT_FILTERS, true) ? $filter : 'new',
            'title' => Str::limit(trim((string) $element->getAttribute('data-title')), 100, ''),
            'limit' => max(1, min(24, (int) ($element->getAttribute('data-limit') ?: 12))),
        ];
    }

    /**
     * Cut serialized markup at its slot tokens into html and slot parts.
     */
    protected function split(string $html): array
    {
        $pieces = preg_split('/<!--'.preg_quote(self::SLOT_TOKEN, '/').'(\d+)-->/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        $parts = [];

        foreach ($pieces as $position => $piece) {
            if ($position % 2 === 1) {
                $parts[] = ['type' => 'slot'] + $this->slots[(int) $piece];
            } elseif (trim($piece) !== '') {
                $parts[] = ['type' => 'html', 'html' => $piece];
            }
        }

        return $parts;
    }
}
