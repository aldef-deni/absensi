<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly AttendanceService $service)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user->companyContext($request->integer('company_id'));
        $month = $request->input('month', now()->format('Y-m'));

        if (! $company) {
            return view('reports.index', [
                'period' => now()->startOfMonth(),
                'rows' => collect(),
                'companies' => $user->companyOptions(),
                'companyId' => null,
            ]);
        }

        $report = $this->service->monthlyReport($company->id, $month);

        return view('reports.index', [
            'period' => $report['period'],
            'rows' => $report['rows'],
            'companies' => $user->companyOptions($company),
            'companyId' => $company->id,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $company = $request->user()->companyContext($request->integer('company_id'));

        if (! $company) {
            abort(404);
        }

        $month = $request->input('month', now()->format('Y-m'));
        $report = $this->service->monthlyReport($company->id, $month);

        $filename = 'rekap-absensi-'.$month.'.csv';

        return Response::streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nama', 'NIP', 'Jabatan', 'Hari Kerja', 'Hadir', 'Telat', 'Absen', 'Total Jam Kerja']);

            foreach ($report['rows'] as $row) {
                fputcsv($out, [
                    $row['employee']->name,
                    $row['employee']->employee_code,
                    $row['employee']->position,
                    $row['workdays'],
                    $row['present'],
                    $row['late'],
                    $row['absent'],
                    $row['work_hours'],
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
