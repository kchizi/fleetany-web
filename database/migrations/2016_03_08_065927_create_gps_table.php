<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGpsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('gps', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('company_id')->index('fk_gps_companies1_idx');
			$table->integer('vehicle_id')->index('fk_gps_vehicles1_idx');
			$table->integer('driver_id')->index('fk_gps_contact1_idx');
			$table->decimal('latitude', 10, 7);
			$table->decimal('longitude', 10, 7);
			$table->decimal('accuracy')->nullable();
			$table->decimal('altitude')->nullable();
			$table->decimal('altitudeAccuracy')->nullable();
			$table->decimal('heading')->nullable();
			$table->decimal('speed')->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->engine = 'InnoDB';
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('gps');
	}

}
