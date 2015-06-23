<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMealsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('meals', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name');
			$table->float('kcals', 10, 0);
			$table->float('carbs', 10, 0);
			$table->float('prot', 10, 0);
			$table->float('fats', 10, 0);
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
		Schema::drop('meals');
	}

}
