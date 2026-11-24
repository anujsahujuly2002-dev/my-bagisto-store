<?php

namespace EcomForYou\Admin\Http\Controllers\Reporting;

use EcomForYou\Admin\Exports\ReportingExport;
use EcomForYou\Admin\Helpers\Reporting as ReportingHelper;
use EcomForYou\Admin\Http\Controllers\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Controller extends BaseController
{
    /**
     * The file types a report may be exported as.
     */
    private const EXPORT_FORMATS = ['csv', 'xls', 'xlsx'];

    /**
     * Request param functions.
     *
     * @var array
     */
    protected $typeFunctions = [];

    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(protected ReportingHelper $reportingHelper) {}

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function stats()
    {
        $stats = $this->reportingHelper->{$this->resolveTypeFunction()}();

        return response()->json([
            'statistics' => $stats,
            'date_range' => $this->reportingHelper->getDateRange(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function viewStats()
    {
        $stats = $this->reportingHelper->{$this->resolveTypeFunction()}('table');

        return response()->json([
            'statistics' => $stats,
            'date_range' => $this->reportingHelper->getDateRange(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return BinaryFileResponse
     */
    public function export()
    {
        $stats = $this->reportingHelper->{$this->resolveTypeFunction()}('table');

        $format = in_array(request()->query('format'), self::EXPORT_FORMATS, true)
            ? request()->query('format')
            : self::EXPORT_FORMATS[0];

        return Excel::download(new ReportingExport($stats), request()->query('type').'.'.$format);
    }

    /**
     * Validate if the requested type is valid.
     *
     * @return void
     */
    protected function validateRequestedType()
    {
        return ! array_key_exists(request()->query('type'), $this->typeFunctions);
    }

    /**
     * Resolve the requested type into a valid function name.
     *
     * @return string
     */
    protected function resolveTypeFunction()
    {
        if ($this->validateRequestedType()) {
            abort(404);
        }

        return $this->typeFunctions[request()->query('type')];
    }
}
