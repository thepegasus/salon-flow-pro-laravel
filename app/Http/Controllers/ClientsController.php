<?php

namespace App\Http\Controllers;

use App\Http\Requests\Clients\StoreClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientsController extends Controller
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private TenantContext $tenantContext,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('clients.view'), 403);

        $search = $request->query('search');
        $clients = $search ? $this->clientRepository->search($search) : $this->clientRepository->getAll();

        return view('admin.clients.index', ['clients' => $clients, 'search' => $search]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('clients.create'), 403);

        return view('admin.clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('clients.create'), 403);

        $client = $this->clientRepository->create([
            ...$request->validated(),
            'tenant_id' => $this->tenantContext->get()->id,
        ]);

        return redirect()->route('clients.show', $client)->with('status', 'Client created.');
    }

    public function show(Request $request, string $subdomain, Client $client): View
    {
        abort_unless($request->user()->can('clients.view'), 403);

        $client->load(['appointments' => fn ($query) => $query->latest('start_at')->with('services')]);

        return view('admin.clients.show', ['client' => $client]);
    }

    public function edit(Request $request, string $subdomain, Client $client): View
    {
        abort_unless($request->user()->can('clients.edit'), 403);

        return view('admin.clients.edit', ['client' => $client]);
    }

    public function update(UpdateClientRequest $request, string $subdomain, Client $client): RedirectResponse
    {
        abort_unless($request->user()->can('clients.edit'), 403);

        $this->clientRepository->update($client, $request->validated());

        return redirect()->route('clients.show', $client)->with('status', 'Client updated.');
    }
}
