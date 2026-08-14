<?php

namespace App\Models\Scopes;

use App\Exceptions\NoTenantContextException;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if ($context->isBypassed()) {
            return;
        }

        $tenant = $context->get();

        if (! $tenant) {
            if ($context->isNoneResolved()) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $modelClass = $model::class;

            throw new NoTenantContextException(
                "Query on tenant-scoped model [{$modelClass}] attempted without tenant context. ".
                'Set the tenant via TenantContext::set(), or explicitly call TenantContext::bypass() for a documented admin-only context.'
            );
        }

        $builder->where($model->getTable().'.tenant_id', $tenant->id);
    }
}
