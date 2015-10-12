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
			$table->date('build_date')->nullable();
			$table->string('job_number_built_on', 32);
			$table->tinyInteger('maintenance_interval')->nullable();
			$table->renameColumn('license_name', 'designer_name');
			$table->renameColumn('license_email', 'designer_email');
			$table->dropColumn('termination_date');
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
			$table->dropColumn('build_date');
			$table->dropColumn('job_number_built_on');
			$table->dropColumn('maintenance_interval');
			$table->renameColumn('designer_name', 'license_name');
			$table->renameColumn('designer_email', 'license_email');
			$table->renameColumn('copies', 'seats');
			DB::statement('ALTER TABLE fixtures MODIFY COLUMN serial text');
			$table->boolean('reassignable');
			$table->date('termination_date')->nullable();
		});
	}

}
