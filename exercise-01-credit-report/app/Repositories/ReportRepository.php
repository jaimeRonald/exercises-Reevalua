<?php

namespace App\Repositories;

use App\Models\SubscriptionReport;
use Illuminate\Support\Collection;

class ReportRepository
{
    /**
     * Get all reports with relationships for export
     * Optimized with eager loading to avoid N+1 queries
     */
    public function getReportsForExport(?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = SubscriptionReport::query()  
            ->with([ // el with evita el problema de N+1 (que es hacer una consulta inecesaria )
                'subscription',
                'loans',
                'creditCards',
                'otherDebts'
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->get();
    }

    /**
     * Process reports in chunks for memory efficiency
     * 
     * @param int $chunkSize Number of records per chunk
     * @param callable $callback Function to process each chunk
     */
    public function processReportsInChunks(
        int $chunkSize,
        callable $callback,
        ?string $startDate = null,
        ?string $endDate = null
    ): void {
        $query = SubscriptionReport::query()
            ->with([
                'subscription',
                'loans',
                'creditCards',
                'otherDebts'
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $query->chunk($chunkSize, $callback);
    }

    /**
     * Get total count of reports for estimation
     */
    public function getReportsCount(?string $startDate = null, ?string $endDate = null): int
    {
        $query = SubscriptionReport::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->count();
    }
}