<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveEulaFields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('categories', function($table){
			$table->dropColumn(array( 'eula_text', 'use_default_eula' ));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('categories', function($table){
			$table->longText( 'eula_text' );
			$table->tinyInteger( 'use_default_eula' );
		});
	}

}
