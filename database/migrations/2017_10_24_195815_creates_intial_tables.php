<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatesIntialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('buildings_developers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('logo_url');
            $table->string('entity_id');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('street_address');
            $table->string('address_line_two');
            $table->string('city');
            $table->string('postal_code');
            $table->string('state');
            $table->string('country');
            $table->string('google_places_id');
            $table->string('coordinates');
            $table->string('operating_region_id');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('buildings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('location_id')->references('id')->on('locations');
            $table->integer('buildings_developer_id')->references('id')->on('buildings_developer');
            $table->integer('operating_region_id');
            $table->string('name');
            $table->integer('status');
            $table->string('logo_url');
            $table->string('entity_id');
            $table->string('short_code');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('buildings_metadata', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('buildings_id')->references('id')->on('buildings');
            $table->string('key');
            $table->string('value');
            $table->softDeletes();
            $table->timestamps();
        });
        
        Schema::create('buildings_units', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('buildings_id')->references('id')->on('buildings');
            $table->integer('entity_id');
            $table->integer('beds');
            $table->integer('baths');
            $table->integer('unit_type');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('buildings_units');
        Schema::drop('buildings_metadata');
        Schema::drop('buildings');
        Schema::drop('locations');
        Schema::drop('buildings_developers');
        
    }
}
