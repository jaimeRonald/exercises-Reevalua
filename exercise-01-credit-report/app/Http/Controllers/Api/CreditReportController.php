<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateCreditReportJob;
use App\Repositories\ReportRepository;
use App\Services\CreditReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreditReportController extends Controller
{
    private ReportRepository $repository;
    private CreditReportService $service;

    public function __construct(ReportRepository $repository, CreditReportService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * generador de reporte crediticio via colas asincrono
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(Request $request): JsonResponse
    {
        // validar las entradas (rango de fechas)
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // verifica si hay reportes para exportar
        $reportCount = $this->repository->getReportsCount($startDate, $endDate);

        if ($reportCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No reports found for the specified date range.'
            ], 404);
        }

        // genera un job ID unico
        $jobId = uniqid('report_', true);

        // inicia el status del jobs en cache
        cache()->put(
            "report_job_{$jobId}",
            ['status' => 'processing'],
            now()->addHours(24)
        );

        // despacho del trabajo a la cola
        GenerateCreditReportJob::dispatch($startDate, $endDate, $jobId);

        // Get estimated time
        $estimatedTime = $this->service->getEstimatedProcessingTime($reportCount);

        return response()->json([
            'success' => true,
            'message' => 'Report generation started',
            'data' => [
                'job_id' => $jobId,
                'status' => 'processing',
                'total_reports' => $reportCount,
                'estimated_time' => $estimatedTime,
                'status_url' => route('credit-reports.status', ['jobId' => $jobId])
            ]
        ], 202); // 202 Accepted
    }

    /**
     * Check job status
     * 
     * @param string $jobId
     * @return JsonResponse
     */
    public function status(string $jobId): JsonResponse
    {
        $jobData = cache()->get("report_job_{$jobId}");

        if (!$jobData) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or expired'
            ], 404);
        }

        $response = [
            'success' => true,
            'data' => [
                'job_id' => $jobId,
                'status' => $jobData['status']
            ]
        ];

        if ($jobData['status'] === 'completed') {
            $response['data']['result'] = $jobData['result'];
        } elseif ($jobData['status'] === 'failed') {
            $response['data']['error'] = $jobData['error'];
        }

        return response()->json($response);
    }

    /**
     * generador de reporte crediticio via colas sincronico (solo para testing)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function generateSync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->service->generateReport(
                $request->input('start_date'),
                $request->input('end_date')
            );

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Report generation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}