<?php

namespace Tests\Concerns;

use App\Models\MainDomain;
use App\Models\Tenant;
use Illuminate\Testing\TestResponse;

trait ActsAsTenant
{
    protected ?Tenant $tenant = null;

    protected ?MainDomain $mainDomain = null;

    protected function setUpTenant(): Tenant
    {
        $this->mainDomain = MainDomain::factory()->create([
            'domain' => 'salonflow.test',
        ]);

        $this->tenant = Tenant::factory()->create([
            'subdomain' => 'mejora',
        ]);

        return $this->tenant;
    }

    protected function tenantUrl(string $uri): string
    {
        return 'http://mejora.salonflow.test'.$uri;
    }

    protected function bySlugUrl(string $uri): string
    {
        return 'http://salonflow.test/'.$this->tenant->slug.$uri;
    }

    protected function getFromTenant(string $uri): TestResponse
    {
        return $this->get($this->tenantUrl($uri));
    }

    protected function postToTenant(string $uri, array $data = []): TestResponse
    {
        return $this->post($this->tenantUrl($uri), $data);
    }

    protected function putToTenant(string $uri, array $data = []): TestResponse
    {
        return $this->put($this->tenantUrl($uri), $data);
    }

    protected function deleteFromTenant(string $uri): TestResponse
    {
        return $this->delete($this->tenantUrl($uri));
    }
}
