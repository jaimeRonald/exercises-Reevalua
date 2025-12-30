<?php

namespace App\Services;

use App\Exports\CreditReportExport;
use App\Repositories\ReportRepository;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CreditReportService
{
    private ReportRepository $repository;

    public function __construct(ReportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generate credit report and save to storage
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     * @throws \Exception
     */
    public function generateReport(?string $startDate = null, ?string $endDate = null): array
    {
        // validar fechas
        $this->validateDates($startDate, $endDate);

        // obtener cantidad de reportes 
        $totalReports = $this->repository->getReportsCount($startDate, $endDate);

        if ($totalReports === 0) {
            throw new \Exception('No reports found for the specified date rangefff.');
        }

        // generar el archivo 
        $filename = $this->generateFilename($startDate, $endDate);
        $filePath = 'reports/' . $filename;

        // gnerar el archivo excel 
        Excel::store(
            new CreditReportExport($startDate, $endDate),
            $filePath,
            'public'
        );

        // tamao del archivo excel 
        $fileSize = Storage::disk('public')->size($filePath);

        return [
            'filename' => $filename,
            'path' => $filePath,
            'download_url' => Storage::disk('public')->url($filePath),
            'file_size' => $this->formatBytes($fileSize),
            'total_reports' => $totalReports,
            'generated_at' => now()->toDateTimeString()
        ];
    }

    /**
     * Validate date inputs
     * 
     * @throws \Exception
     */
    private function validateDates(?string $startDate, ?string $endDate): void
    {
        if ($startDate && !strtotime($startDate)) {
            throw new \Exception('Invalid start date format. Use Y-m-d (e.g., 2024-01-01)');
        }

        if ($endDate && !strtotime($endDate)) {
            throw new \Exception('Invalid end date format. Use Y-m-d (e.g., 2024-12-31)');
        }

        if ($startDate && $endDate && strtotime($startDate) > strtotime($endDate)) {
            throw new \Exception('Start date cannot be greater than end date.');
        }
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(?string $startDate, ?string $endDate): string
    {
        $dateRange = '';
        
        if ($startDate && $endDate) {
            $dateRange = '_' . date('Ymd', strtotime($startDate)) . '-' . date('Ymd', strtotime($endDate));
        }

        return 'credit_report' . $dateRange . '_' . time() . '.xlsx';
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get estimated processing time based on report count
     */
    public function getEstimatedProcessingTime(int $reportCount): string
    {
        // Rough estimation: 1000 reports = ~10 seconds
        $seconds = ceil(($reportCount / 1000) * 10);

        if ($seconds < 60) {
            return $seconds . ' seconds';
        }

        $minutes = ceil($seconds / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    }
}