<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add-ons were services that could never be booked on their own — every
     * booking line item required a base service_id, with the add-on riding
     * along as an optional extra. In practice there was no such thing as an
     * "add-on only" service: a head massage or beard trim is just as
     * bookable by itself as any other service. This converts every existing
     * service_add_ons row into a real, independently bookable services row,
     * and rewrites any appointment_service pivot rows that referenced an
     * add-on into a second, plain pivot row against the new service — so
     * historical bookings keep both the base service and the (now
     * standalone) former add-on as separate line items instead of one
     * merged one.
     */
    public function up(): void
    {
        $addOns = DB::table('service_add_ons')->whereNull('deleted_at')->get();

        $addOnIdToServiceId = [];

        foreach ($addOns as $addOn) {
            $addOnIdToServiceId[$addOn->id] = DB::table('services')->insertGetId([
                'tenant_id' => $addOn->tenant_id,
                'name' => $addOn->name,
                'code' => null,
                'category_id' => null,
                'price' => $addOn->price,
                'duration_minutes' => max($addOn->duration_minutes, 1),
                'is_active' => $addOn->is_active,
                'created_at' => $addOn->created_at,
                'updated_at' => now(),
            ]);
        }

        $bookedAddOns = DB::table('appointment_service')->whereNotNull('service_add_on_id')->get();

        foreach ($bookedAddOns as $row) {
            $newServiceId = $addOnIdToServiceId[$row->service_add_on_id] ?? null;

            if ($newServiceId === null) {
                continue;
            }

            $addOn = $addOns->firstWhere('id', $row->service_add_on_id);

            DB::table('appointment_service')->insert([
                'appointment_id' => $row->appointment_id,
                'service_id' => $newServiceId,
                'service_add_on_id' => null,
                'price_at_booking' => $addOn->price ?? 0,
                'duration_minutes_at_booking' => $addOn->duration_minutes ?? 0,
                'created_at' => $row->created_at,
                'updated_at' => now(),
            ]);
        }

        Schema::table('appointment_service', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('service_add_on_id');
        });

        Schema::dropIfExists('service_add_ons');
    }
};
