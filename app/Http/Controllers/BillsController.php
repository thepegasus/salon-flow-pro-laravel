<?php

namespace App\Http\Controllers;

use App\Http\Requests\Billing\GenerateBillFromAppointmentRequest;
use App\Http\Requests\Billing\RecordPaymentRequest;
use App\Http\Requests\Billing\RefundBillRequest;
use App\Http\Requests\Billing\StoreManualBillRequest;
use App\Models\Appointment;
use App\Models\Bill;
use App\Repositories\Contracts\BillRepositoryInterface;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class BillsController extends Controller
{
    public function __construct(
        private BillRepositoryInterface $billRepository,
        private BillingService $billingService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('billing.view'), 403);

        $date = $request->query('date', now()->toDateString());
        $bills = $this->billRepository->getForDate($date);

        return view('admin.bills.index', ['bills' => $bills, 'date' => $date]);
    }

    public function generateFromAppointment(GenerateBillFromAppointmentRequest $request, string $subdomain, Appointment $appointment): RedirectResponse
    {
        abort_unless($request->user()->can('billing.create'), 403);

        $bill = $this->billingService->generateFromAppointment(
            $appointment,
            $request->user()->id,
            $request->validated()['manual_items'] ?? [],
        );

        return redirect()->route('bills.show', $bill)->with('status', 'Bill generated.');
    }

    public function storeManual(StoreManualBillRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('billing.create'), 403);

        $data = $request->validated();
        $bill = $this->billingService->createManualBill($data['client_id'], $request->user()->id, $data['items']);

        return redirect()->route('bills.show', $bill)->with('status', 'Bill created.');
    }

    public function show(Request $request, string $subdomain, Bill $bill): View
    {
        abort_unless($request->user()->can('billing.view'), 403);

        $bill->load(['lineItems', 'payments', 'refunds', 'client']);

        return view('admin.bills.show', ['bill' => $bill]);
    }

    public function recordPayment(RecordPaymentRequest $request, string $subdomain, Bill $bill): RedirectResponse
    {
        abort_unless($request->user()->can('billing.create'), 403);

        $this->billingService->recordPayments($bill, $request->validated()['payments'], $request->user()->id);

        return redirect()->route('bills.show', $bill)->with('status', 'Payment recorded.');
    }

    public function refund(RefundBillRequest $request, string $subdomain, Bill $bill): RedirectResponse
    {
        abort_unless($request->user()->can('billing.edit'), 403);

        $data = $request->validated();

        try {
            $this->billingService->refund($bill, $data['amount'], $data['reason'], $request->user()->id);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['amount' => $exception->getMessage()]);
        }

        return redirect()->route('bills.show', $bill)->with('status', 'Refund recorded.');
    }
}
