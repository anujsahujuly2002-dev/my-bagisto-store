<div
    role="status"
    style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 16px; padding: 8px 16px; background: #060c3b; color: #ffffff; font-size: 14px;"
>
    <span>
        {{ trans('shop::app.layouts.theme-preview.previewing', ['theme' => themes()->current()?->name ?? '']) }}
    </span>

    <form
        method="POST"
        action="{{ route('admin.appearance.themes.stop_preview') }}"
    >
        @csrf

        <button
            type="submit"
            style="background: transparent; border: 1px solid #ffffff; border-radius: 6px; padding: 4px 12px; color: #ffffff; cursor: pointer;"
        >
            {{ trans('shop::app.layouts.theme-preview.exit') }}
        </button>
    </form>
</div>
