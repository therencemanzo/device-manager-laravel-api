<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('sim_card_id')->nullable()->constrained('sim_cards')->nullOnDelete()->cascadeOnUpdate();
            $table->string('serial_number')->unique();
            $table->string('imei')->unique();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->enum('operating_system', ['ios', 'andriod']);
            $table->enum('type', ['mobile', 'tablet']);
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('status_update_at')->useCurrent();
            $table->timestamps();
        });

        // Schema::create('device_status', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('device_id')->nullable()->constrained('devices')->cascadeOnDelete()->cascadeOnUpdate();
        //     $table->tinyInteger('is_active')->default(1);
        //     $table->timestamps();
        // });

        Schema::create('device_user_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
        Schema::dropIfExists('device_status');
        Schema::dropIfExists('device_user_history');
    }
};
