<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('employer_id', 10);
            $table->string('job_id', 10);
            $table->string('inv_address', 55)->nullable();
            $table->string('inv_notes')->nullable();
            $table->string('inv_amount', 10);
            $table->boolean('paid')->default(false);
            $table->boolean('is_renewable')->default(false);
            $table->string('next_bump', 30)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
