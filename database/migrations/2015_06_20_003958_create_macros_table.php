<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMacrosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('macros', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('kcals');
			$table->integer('carbs');
			$table->integer('fats');
			$table->integer('prot');
			$table->integer('wkcals');
			$table->integer('wcarbs');
			$table->integer('wfats');
			$table->integer('wprot');
			$table->integer('user_id');
			$table->integer('bulking');
			$table->string('name');
			$table->string('current');
			$table->timestamp('updated_at');
			$table->timestamp('created_at');
		});

	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('macros');
	}

}
