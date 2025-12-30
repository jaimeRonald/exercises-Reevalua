<?php

namespace App\Exports;

use App\Repositories\ReportRepository;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CreditReportExport implements 
    FromGenerator, 
    WithHeadings, 
    WithMapping, 
    ShouldAutoSize,
    WithStyles
{
    private ?string $startDate;
    private ?string $endDate;
    private ReportRepository $repository;

    public function __construct(?string $startDate = null, ?string $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->repository = new ReportRepository();
    }

    /**
     * Se usa el generador para transmitir datos en lugar de cargarlos todos en la memoria
     */
    public function generator(): \Generator
    { 
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $query = \App\Models\SubscriptionReport::query()
            ->with([
                'subscription',
                'loans',
                'creditCards',
                'otherDebts'
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Use cursor instead of chunk for generators
        foreach ($query->cursor() as $report) {
            $hasDebts = false;

            // Yield loans
            foreach ($report->loans as $loan) {
                $hasDebts = true;
                yield [
                    'report' => $report,
                    'debt_type' => 'Préstamo',
                    'company' => $loan->bank,
                    'status' => $loan->status,
                    'expiration_days' => $loan->expiration_days,
                    'entity' => $loan->bank,
                    'amount' => $loan->amount,
                    'currency' => $loan->currency,
                    'total_line' => null,
                    'timeline' => null,
                ];
            }

            // Yield credit cards
            foreach ($report->creditCards as $card) {
                $hasDebts = true;
                yield [
                    'report' => $report,
                    'debt_type' => 'Tarjeta de crédito',
                    'company' => $card->bank,
                    'status' => null,
                    'expiration_days' => null,
                    'entity' => $card->bank,
                    'amount' => null,
                    'currency' => $card->currency,
                    'total_line' => $card->line,
                    'timeline' => $card->used,
                ];
            }

            // Yield other debts
            foreach ($report->otherDebts as $debt) {
                $hasDebts = true;
                yield [
                    'report' => $report,
                    'debt_type' => 'Otra deuda',
                    'company' => $debt->entity,
                    'status' => null,
                    'expiration_days' => $debt->expiration_days,
                    'entity' => $debt->entity,
                    'amount' => $debt->amount,
                    'currency' => $debt->currency,
                    'total_line' => null,
                    'timeline' => null,
                ];
            }

            // If no debts, show one row anyway
            if (!$hasDebts) {
                yield [
                    'report' => $report,
                    'debt_type' => 'N/A',
                    'company' => 'N/A',
                    'status' => null,
                    'expiration_days' => null,
                    'entity' => 'N/A',
                    'amount' => null,
                    'currency' => null,
                    'total_line' => null,
                    'timeline' => null,
                ];
            }
        }
    }

    /**
     * define los encabezados de las columnas del Excel 
     */
    public function headings(): array
    {
        return [
            'IDENTIFICACIÓN',
            'Nombre Completo',
            'DNI',
            'Correo electrónico',
            'Teléfono',
            'Compañía',
            'Tipo de deuda',
            'Situación',
            'Atraso',
            'Entidad',
            'Monto total',
            'Línea total',
            'Línea de tiempo',
            'Reporte subido el',
            'Estado'
        ];
    }

    /**
     * asignamos cada fila a la columnas del Excel 
     */
    public function map($row): array
    {
        $report = $row['report'];
        $subscription = $report->subscription;

        return [
            $report->id,
            $subscription->full_name,
            $subscription->document,
            $subscription->email,
            $subscription->phone,
            $row['company'],
            $row['debt_type'],
            $row['status'] ?? 'N/A',
            $row['expiration_days'] ?? 0,
            $row['entity'],
            $row['amount'] ?? 0,
            $row['total_line'] ?? 0,
            $row['timeline'] ?? 0,
            $report->created_at->format('Y-m-d H:i:s'),
            'Activo'
        ];
    }

    /**
     * definimos estilos del Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8']
                ]
            ],
        ];
    }
}