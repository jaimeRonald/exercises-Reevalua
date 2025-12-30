<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\CreditReportService;

use Illuminate\Support\Facades\Log;
class GenerateCreditReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     public ?string $startDate;
    public ?string $endDate;
    public string $jobId;

    /**
     * cantidad maxima de intentos 
     */
    public $tries = 3;

    /**
     * tiempo de espera en segundos (3)
     */
    public $timeout = 1800;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $startDate = null, ?string $endDate = null, string $jobId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->jobId = $jobId ?? uniqid('report_', true);
    }
 

    /**
     * Execute the job.
     */
    public function handle(CreditReportService $service): void
    {
        try {
            Log::info('Starting credit report generation', [
                'job_id' => $this->jobId,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate
            ]);

            $result = $service->generateReport($this->startDate, $this->endDate);

            // almacena el resultado del jobs para monitoreo de estados de los mismos
            cache()->put(
                "report_job_{$this->jobId}",
                [
                    'status' => 'completed',
                    'result' => $result
                ],
                now()->addHours(24) // y lo conserva durante 24 horas
            );

            Log::info('Credit report generated successfully', [
                'job_id' => $this->jobId,
                'filename' => $result['filename'],
                'total_reports' => $result['total_reports']
            ]);

        } catch (\Exception $e) {
            Log::error('Credit report generation failed', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            cache()->put(  // tambien almacena el error en cache
                "report_job_{$this->jobId}",
                [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ],
                now()->addHours(24)
            );

            // se veulve a lanzar para tomarlo como job fallido
            throw $e;
        }
    }

    // manejar un fallo del job
    public function failed(\Throwable $exception): void
    {
        Log::error('Credit report job failed permanently', [
            'job_id' => $this->jobId,
            'error' => $exception->getMessage()
        ]);

        cache()->put(
            "report_job_{$this->jobId}",
            [
                'status' => 'failed',
                'error' => $exception->getMessage()
            ],
            now()->addHours(24)
        );
    }
}
