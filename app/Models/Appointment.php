<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id', 'client_id', 'staff_profile_id', 'start_at', 'end_at', 'status', 'notes', 'cancellation_reason',
    'is_on_location', 'venue_address', 'bridal_engagement_id', 'engagement_role',
])]
#[ScopedBy([TenantScope::class])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory, SoftDeletes;

    public const StatusBooked = 'booked';

    public const StatusInProgress = 'in_progress';

    public const StatusCompleted = 'completed';

    public const StatusCancelled = 'cancelled';

    public const StatusNoShow = 'no_show';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_on_location' => 'boolean',
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

    /** @return BelongsTo<StaffProfile, $this> */
    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    /** @return BelongsToMany<Service, $this> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'appointment_service')
            ->withPivot(['price_at_booking', 'duration_minutes_at_booking']);
    }

    /** @return HasMany<AppointmentStatusHistory, $this> */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(AppointmentStatusHistory::class);
    }

    /** @return HasMany<AppointmentReminder, $this> */
    public function reminders(): HasMany
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    /** @return BelongsTo<BridalEngagement, $this> */
    public function bridalEngagement(): BelongsTo
    {
        return $this->belongsTo(BridalEngagement::class);
    }

    /** @param Builder<Appointment> $query */
    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('start_at', $date);
    }

    /** @param Builder<Appointment> $query */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_at', '>=', now())
            ->whereNotIn('status', [self::StatusCancelled, self::StatusCompleted, self::StatusNoShow]);
    }
}
