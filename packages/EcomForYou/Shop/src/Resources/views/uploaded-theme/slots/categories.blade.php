@inject('categoryRepository', \EcomForYou\Category\Repositories\CategoryRepository::class)

<ul class="bagisto-categories">
    @foreach ($categoryRepository->getVisibleCategoryTree(core()->getCurrentChannel()->root_category_id) as $category)
        <li>
            <a href="{{ $category->url }}">
                {{ $category->name }}
            </a>
        </li>
    @endforeach
</ul>
