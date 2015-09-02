<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccessoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		 Schema::create('tools', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable()->default(NULL);
            $table->integer('category_id')->nullable()->default(NULL);
            $table->integer('user_id')->nullable()->default(NULL);
            $table->integer('qty')->default(0);
            $table->boolean('requestable')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('asset_logs', function ($table) {
			$table->integer('tool_id')->nullable()->default(NULL);
		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
		Schema::drop('tools');

		Schema::table('asset_logs', function ($table) {
			$table->dropColumn('tool_id');
		});
	}

}
