<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('co_mail', 55)->nullable();
            $table->string('co_twitter', 55)->nullable();
            $table->string('primary_tag', 55)->nullable();
            $table->text('tags')->nullable();
            $table->text('howto')->nullable();
            $table->string('salary',30)->nullable();
            $table->string('edit_link', 255)->unique();
            /** additives */
            $table->boolean('show_logo')->default(false);
            $table->boolean('bump')->default(false);
            $table->boolean('match')->default(false);
            $table->boolean('yellow_it')->default(false);
            $table->boolean('brand_color')->default(false);
            $table->boolean('sticky_day')->default(false);
            $table->boolean('sticky_week')->default(false);
            $table->boolean('sticky_month')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            //
        });
    }
}
