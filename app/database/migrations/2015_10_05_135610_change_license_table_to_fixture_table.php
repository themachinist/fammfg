<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLicenseTableToFixtureTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::rename('licenses', 'fixtures');
		Schema::rename('license_seats', 'fixture_seats');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::rename('fixtures', 'licenses' );
		Schema::rename('fixture_seats', 'license_seats');
	}

}
