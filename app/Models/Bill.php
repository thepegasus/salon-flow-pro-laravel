<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Database\Factories\BillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id', 'client_id', 'appointment_id', 'bill_number', 'subtotal', 'tax_amount', 'total',
    'amount_paid', 'amount_refunded', 'status', 'created_by',
])]
#[ScopedBy([TenantScope::class])]
class Bill extends Model
{
    /** @use HasFactory<BillFactory> */
    use HasFactory, SoftDeletes;

    public const StatusUnpaid = 'unpaid';

    public const StatusPartial = 'partial';

    public const StatusPaid = 'paid';

    public const StatusVoid = 'void';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'amount_refunded' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return BelongsTo<Appointment, $this> */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<BillLineItem, $this> */
    public function lineItems(): HasMany
    {
        return $this->hasMany(BillLineItem::class);
    }

    /** @return HasMany<BillPayment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    /** @return HasMany<BillRefund, $this> */
    public function refunds(): HasMany
    {
        return $this->hasMany(BillRefund::class);
    }

    public function balanceDue(): string
    {
        return bcsub((string) $this->total, (string) $this->amount_paid, 2);
    }

    /** @param Builder<Bill> $query */
    public function scopeUnpaidOrPartial(Builder $query): Builder
    {
        return $query->whereIn('status', [self::StatusUnpaid, self::StatusPartial]);
    }
}
