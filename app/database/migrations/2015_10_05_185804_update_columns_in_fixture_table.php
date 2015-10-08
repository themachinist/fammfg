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
			$table->decimal('build_cost', 8, 2)->nullable();
			$table->string('job_number_built_on', 32);
			$table->renameColumn('license_name', 'designer_name');
			$table->renameColumn('license_email', 'designer_email');
			$table->renameColumn('termination_date', 'maintenance_interval');
			$table->renameColumn('seats', 'copies');
			$table->renameColumn('maintained', 'needs_maintenance');
			DB::statement('ALTER TABLE fixtures MODIFY COLUMN serial VARCHAR (255)');
			$table->dropColumn('reassignable');
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
			$table->dropColumn('build_cost');
			$table->dropColumn('job_number_built_on');
			$table->renameColumn('designer_name', 'license_name');
			$table->renameColumn('designer_email', 'license_email');
			$table->renameColumn('maintenance_interval', 'termination_date');
			$table->renameColumn('copies', 'seats');
			$table->renameColumn('needs_maintenance', 'maintained');
			DB::statement('ALTER TABLE fixtures MODIFY COLUMN serial text');
			$table->boolean('reassignable');
		});
	}

}
