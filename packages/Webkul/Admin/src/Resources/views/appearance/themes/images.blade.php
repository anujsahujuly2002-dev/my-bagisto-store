<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.appearance.themes.images.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.appearance.themes.images.before', ['theme' => $theme]) !!}

    <!-- Page Header -->
    <div class="flex items-start justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.appearance.themes.images.title')
            </p>

            <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                @lang('admin::app.appearance.themes.images.info', ['theme' => $theme->name])
            </p>
        </div>

        <a
            href="{{ route('admin.appearance.themes.index') }}"
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
        >
            @lang('admin::app.appearance.themes.images.back-btn')
        </a>
    </div>

    @if ($errors->has('image'))
        <p class="mt-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-200">
            {{ $errors->first('image') }}
        </p>
    @endif

    @if (empty($images))
        <p class="box-shadow mt-8 rounded-sm bg-white p-6 text-sm text-gray-600 dark:bg-gray-900 dark:text-gray-300">
            @lang('admin::app.appearance.themes.images.empty')
        </p>
    @else
        <div class="mt-8 grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-6">
            @foreach ($images as $image)
                <div class="box-shadow flex flex-col overflow-hidden rounded-sm bg-white dark:bg-gray-900">
                    <!-- Thumbnail -->
                    <div class="relative flex h-47.5 items-center justify-center bg-gray-100 dark:bg-gray-950">
                        <img
                            class="h-full w-full object-cover"
                            src="{{ $image['url'] }}"
                            alt="{{ $image['alt'] }}"
                        />

                        @if ($image['is_replaced'])
                            <span class="label-active absolute top-3 ltr:left-3 rtl:right-3">
                                @lang('admin::app.appearance.themes.images.replaced')
                            </span>
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="flex flex-1 flex-col gap-3 p-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                {{ $image['alt'] ?: $image['id'] }}
                            </p>

                            <p class="text-xs text-gray-500 dark:text-gray-300">
                                {{ $image['id'] }}
                            </p>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('admin.appearance.themes.images.update', [$theme->code, $image['id']]) }}"
                            enctype="multipart/form-data"
                            class="grid gap-2"
                        >
                            @csrf

                            <input
                                type="file"
                                name="image"
                                accept="{{ collect(\Webkul\Theme\Upload\ThemeImages::MIMES)->map(fn ($mime) => '.'.$mime)->implode(',') }}"
                                required
                                class="w-full text-xs text-gray-600 dark:text-gray-300"
                                aria-label="@lang('admin::app.appearance.themes.images.upload-btn') {{ $image['alt'] ?: $image['id'] }}"
                            />

                            <p class="text-xs text-gray-500 dark:text-gray-300">
                                @lang('admin::app.appearance.themes.images.hint', ['size' => \Webkul\Theme\Upload\ThemeImages::MAX_KILOBYTES / 1024])
                            </p>

                            <button
                                type="submit"
                                class="primary-button justify-center"
                            >
                                @lang('admin::app.appearance.themes.images.upload-btn')
                            </button>
                        </form>

                        @if ($image['is_replaced'])
                            <form
                                method="POST"
                                action="{{ route('admin.appearance.themes.images.delete', [$theme->code, $image['id']]) }}"
                            >
                                @csrf

                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="secondary-button w-full justify-center"
                                >
                                    @lang('admin::app.appearance.themes.images.reset-btn')
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {!! view_render_event('bagisto.admin.appearance.themes.images.after', ['theme' => $theme]) !!}
</x-admin::layouts>
