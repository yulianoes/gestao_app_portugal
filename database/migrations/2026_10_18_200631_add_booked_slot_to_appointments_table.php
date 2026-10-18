<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "booked_slot" holds the date while the appointment is active and NULL once it is cancelled.
     * The unique index lets the database reject two active appointments at the same time,
     * even when two requests pass the "isSlotTaken" check at the same moment.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dateTime('booked_slot')->nullable()->unique()->after('date');
        });

        DB::table('appointments')
            ->where('status', '!=', 'cancelled')
            ->update(['booked_slot' => DB::raw('date')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique(['booked_slot']);
            $table->dropColumn('booked_slot');
        });
    }
};
