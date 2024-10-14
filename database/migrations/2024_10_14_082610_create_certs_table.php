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
        Schema::create('certs', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->nullable();
            $table->string('project_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('pc_id')->nullable();
            $table->string('latest_pc_id')->nullable()->boolean();

            $table->string('advance_amount')->nullable();
            $table->string('retention_amount')->nullable();
            $table->string('deduction_amount')->nullable();   
            
            $table->string('user_id')->nullable();
    
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certs');
    }
};
