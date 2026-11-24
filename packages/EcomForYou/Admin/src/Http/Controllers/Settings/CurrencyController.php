<?php

namespace EcomForYou\Admin\Http\Controllers\Settings;

use EcomForYou\Admin\DataGrids\Settings\CurrencyDataGrid;
use EcomForYou\Admin\Http\Controllers\Controller;
use EcomForYou\Core\Enums\CurrencyPositionEnum;
use EcomForYou\Core\Repositories\CurrencyRepository;
use EcomForYou\Core\Rules\Code;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected CurrencyRepository $currencyRepository) {}

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CurrencyDataGrid::class)->process();
        }

        return view('admin::settings.currencies.index', [
            'currencyPositions' => CurrencyPositionEnum::options(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'code' => ['required', 'min:3', 'max:3', 'unique:currencies,code', new Code],
            'name' => 'required|max:255',
            ...$this->getFormatRules(),
        ]);

        Event::dispatch('core.currency.create.before');

        $currency = $this->currencyRepository->create(request()->only([
            'code',
            'name',
            'symbol',
            'decimal',
            'group_separator',
            'decimal_separator',
            'currency_position',
        ]));

        Event::dispatch('core.currency.create.after', $currency);

        return new JsonResponse([
            'message' => trans('admin::app.settings.currencies.index.create-success'),
        ]);
    }

    /**
     * Currency details.
     */
    public function edit(int $id): JsonResponse
    {
        $currency = $this->currencyRepository->findOrFail($id);

        return new JsonResponse($currency);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(): JsonResponse
    {
        $id = request('id');

        $this->validate(request(), [
            'name' => 'required|max:255',
            ...$this->getFormatRules(),
        ]);

        Event::dispatch('core.currency.update.before', $id);

        $currency = $this->currencyRepository->update(request()->only([
            'name',
            'symbol',
            'decimal',
            'group_separator',
            'decimal_separator',
            'currency_position',
        ]), $id);

        Event::dispatch('core.currency.update.after', $currency);

        return new JsonResponse([
            'message' => trans('admin::app.settings.currencies.index.update-success'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->currencyRepository->findOrFail($id);

        if ($this->currencyRepository->count() == 1) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.currencies.index.last-delete-error'),
            ], 400);
        }

        try {
            $this->currencyRepository->delete($id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.currencies.index.delete-success'),
            ], 200);
        } catch (\Exception $e) {
            report($e);

            return new JsonResponse([
                'message' => trans('admin::app.settings.currencies.index.delete-failed'),
            ], 500);
        }
    }

    /**
     * Get the rules for how a currency formats a price, whose symbol and separators are written into pages.
     */
    protected function getFormatRules(): array
    {
        return [
            'symbol' => ['nullable', 'string', 'not_regex:/[<>"\'`&]/'],
            'decimal' => ['nullable', 'integer', 'between:0,9'],
            'group_separator' => ['nullable', 'regex:/^[,.\' ]$/'],
            'decimal_separator' => ['nullable', 'regex:/^[,.]+$/'],
            'currency_position' => ['nullable', Rule::enum(CurrencyPositionEnum::class)],
        ];
    }
}
