<?php

namespace App\Http\Controllers;

use App\Http\Requests\Billing\SettleQuickBillRequest;
use App\Services\QuickBillService;
use App\Services\TenantUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class QuickBillController extends Controller
{
    public function __construct(
        private QuickBillService $quickBillService,
        private TenantUrl $tenantUrl,
    ) {}

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('billing.create'), 403);

        return view('admin.bills.quick');
    }

    public function lookupService(Request $request, string $subdomain, string $code): JsonResponse
    {
        abort_unless($request->user()->can('billing.create'), 403);

        $service = $this->quickBillService->findServiceByCode($code);

        if (! $service) {
            return response()->json(['found' => false], 404);
        }

        return response()->json([
            'found' => true,
            'id' => $service->id,
            'code' => $service->code,
            'name' => $service->name,
            'price' => (float) $service->price,
        ]);
    }

    public function lookupClient(Request $request, string $subdomain, string $phone): JsonResponse
    {
        abort_unless($request->user()->can('billing.create'), 403);

        $client = $this->quickBillService->findClientByPhone($phone);

        if (! $client) {
            return response()->json(['found' => false], 404);
        }

        return response()->json([
            'found' => true,
            'id' => $client->id,
            'name' => $client->name,
        ]);
    }

    public function settle(SettleQuickBillRequest $request): JsonResponse
    {
        abort_unless($request->user()->can('billing.create'), 403);

        $data = $request->validated();

        $clientId = null;
        if (! empty($data['client_phone'])) {
            $client = $this->quickBillService->findClientByPhone($data['client_phone']);

            if (! $client) {
                return response()->json(['message' => 'No client found for that phone number.'], 422);
            }

            $clientId = $client->id;
        }

        try {
            $bill = $this->quickBillService->createAndSettle(
                $data['codes'],
                $clientId,
                $data['payment_method'],
                $request->user()->id,
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'bill_id' => $bill->id,
            'bill_number' => $bill->bill_number,
            'total' => (float) $bill->total,
            'redirect' => $this->tenantUrl->route('bills.show', ['bill' => $bill]),
        ]);
    }
}
