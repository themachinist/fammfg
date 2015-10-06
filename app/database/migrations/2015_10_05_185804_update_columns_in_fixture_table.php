<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnsInFixtureTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('fixtures', function($table) {
			$table->renameColumn('license_name', 'fixture_name');
			$table->renameColumn('license_email', 'fixture_email');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('fixtures', function($table) {
			$table->renameColumn('fixture_name', 'license_name');
			$table->renameColumn('fixture_email', 'license_email');
		});
	}

}
