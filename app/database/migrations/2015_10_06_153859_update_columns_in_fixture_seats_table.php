<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnsInFixtureSeatsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('fixture_seats', function($table) {
			$table->renameColumn('license_id', 'fixture_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('fixture_seats', function($table) {
			$table->renameColumn('fixture_id', 'license_id');
		});
	}

}
