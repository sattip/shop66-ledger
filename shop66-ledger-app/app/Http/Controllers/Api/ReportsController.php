<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\Reports\ReportsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReportsController extends Controller
{
    public function __construct(private readonly ReportsService $reportsService)
    {
    }

    public function financialSummary(Request $request, Store $store): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $data = $this->reportsService->getFinancialSummary(
            $store,
            $request->start_date,
            $request->end_date
        );

        return response()->json($data);
    }

    public function vendorReport(Request $request, Store $store): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $data = $this->reportsService->getVendorReport(
            $store,
            $request->start_date,
            $request->end_date
        );

        return response()->json($data);
    }

    public function categoryReport(Request $request, Store $store): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $data = $this->reportsService->getCategoryReport(
            $store,
            $request->start_date,
            $request->end_date
        );

        return response()->json($data);
    }

    public function export(Request $request, Store $store): JsonResponse
    {
        $request->validate([
            'type' => ['required', Rule::in(['financial_summary', 'vendor', 'category'])],
            'format' => ['required', Rule::in(['excel', 'pdf'])],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Get the report data
        $data = match ($request->type) {
            'financial_summary' => $this->reportsService->getFinancialSummary(
                $store,
                $request->start_date,
                $request->end_date
            ),
            'vendor' => $this->reportsService->getVendorReport(
                $store,
                $request->start_date,
                $request->end_date
            )->toArray(),
            'category' => $this->reportsService->getCategoryReport(
                $store,
                $request->start_date,
                $request->end_date
            )->toArray(),
        };

        // Generate the export file
        $filename = match ($request->format) {
            'excel' => $this->reportsService->exportToExcel($data, $request->type),
            'pdf' => $this->reportsService->exportToPdf($data, $request->type),
        };

        return response()->json([
            'message' => 'Report generated successfully',
            'filename' => $filename,
            'download_url' => url('/api/stores/' . $store->id . '/reports/download/' . $filename),
        ]);
    }

    public function download(Store $store, string $filename): Response
    {
        if (!Storage::exists($filename)) {
            abort(404, 'File not found');
        }

        $path = Storage::path($filename);
        $mimeType = Storage::mimeType($filename);

        return response()->download($path, $filename, [
            'Content-Type' => $mimeType,
        ]);
    }
}
