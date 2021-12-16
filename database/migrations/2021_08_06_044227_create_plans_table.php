<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->decimal('monthly_price',10,2)->nullable();
            $table->decimal('yearly_price',10,2)->nullable();
            $table->double('yearly_discount', 10, 0)->nullable();
            $table->{$this->jsonable()}('restrictions', 5000)->nullable();
            $table->integer('max_students')->unsigned();
            $table->integer('min_students')->unsigned();

            $table->{$this->jsonable()}('stripe_monthly_data', 10000)->nullable();
            $table->{$this->jsonable()}('stripe_yearly_data', 10000)->nullable();

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
        Schema::dropIfExists('plans');
    }

    protected function jsonable(): string
    {
        $driverName = DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        $dbVersion = DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
        $isOldVersion = version_compare($dbVersion, '5.7.8', 'lt');

        return $driverName === 'mysql' && $isOldVersion ? 'text' : 'json';
    }
}
