<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDaymealsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('daymeals', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('day_id');
			$table->integer('meal_id');
			$table->integer('user_id');
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
		Schema::drop('daymeals');
	}

}
