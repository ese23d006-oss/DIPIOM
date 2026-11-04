<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Reports\ReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    private const PER_PAGE = 30;

    public function index(): View
    {
        return view('admin.reports.index', ['titles' => ReportBuilder::TITLES]);
    }

    public function show(Request $request, string $report, ReportBuilder $builder): View
    {
        $data = $builder->build($report);
        $page = max(1, $request->integer('page', 1));

        return view('admin.reports.show', [
            'report' => $report,
            'title' => $data['title'],
            'headings' => $data['headings'],
            'rows' => new LengthAwarePaginator(
                $data['rows']->forPage($page, self::PER_PAGE)->values(),
                $data['rows']->count(),
                self::PER_PAGE,
                $page,
                ['path' => $request->url()]
            ),
        ]);
    }

    public function download(string $report, string $format, ReportBuilder $builder): Response|BinaryFileResponse|RedirectResponse
    {
        $data = $builder->build($report);
        $filename = $report.'-'.now()->format('Y-m-d').'.'.$format;
        $limit = config('lms.reports.pdf_max_rows');

        // PDF үүсгэгч мөр тутамд их санах ой зарцуулж, том тайлан дээр сервер унагадаг.
        if ($format === 'pdf' && $data['rows']->count() > $limit) {
            return redirect()->route('admin.reports.show', $report)
                ->with('error', "Энэ тайлан {$data['rows']->count()} мөртэй тул PDF боломжгүй (хязгаар {$limit} мөр). Excel файлаар татна уу.");
        }

        return $format === 'xlsx' ? $this->excel($data, $filename) : $this->pdf($data, $filename);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function excel(array $data, string $filename): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($data['title'], 0, 31));
        $spreadsheet->getProperties()->setTitle($data['title']);

        foreach ($data['headings'] as $column => $heading) {
            $sheet->setCellValueExplicit([$column + 1, 1], $heading, DataType::TYPE_STRING);
            $sheet->getColumnDimensionByColumn($column + 1)->setAutoSize(true);
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($data['headings']));
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->freezePane('A2');

        foreach ($data['rows'] as $index => $row) {
            foreach ($row as $column => $value) {
                // Нэр "=" -ээр эхэлбэл Excel томьёо болгож ажиллуулахаас хамгаална.
                is_int($value)
                    ? $sheet->setCellValueExplicit([$column + 1, $index + 2], $value, DataType::TYPE_NUMERIC)
                    : $sheet->setCellValueExplicit([$column + 1, $index + 2], (string) $value, DataType::TYPE_STRING);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'report');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()
            ->download($path, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
            ->deleteFileAfterSend();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function pdf(array $data, string $filename): Response
    {
        return Pdf::loadView('admin.reports.pdf', $data + ['generatedAt' => now()->format('Y-m-d H:i')])
            ->setPaper('a4', count($data['headings']) > 4 ? 'landscape' : 'portrait')
            ->download($filename);
    }
}
