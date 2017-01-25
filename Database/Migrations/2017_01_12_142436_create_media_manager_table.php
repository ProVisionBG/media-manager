<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMediaManagerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_manager', function (Blueprint $table) {
            $table->increments('id');

            $table->string('mediaable_type')->index();
            $table->string('mediaable_sub_type')->index()->nullable()->default(null);
            $table->integer('mediaable_id')->unsigned()->index();

            $table->integer('user_id')->nullable()->unsigned()->index()->comment('Кой е качил файла');

            $table->string('file')->nullable()->default(null);
            $table->string('mime_type')->nullable()->default(null)->index();
            $table->boolean('is_image')->default(false)->index()->comment('Дали файла е картинка?');

            $table->integer('order_index')->unsigned()->index();

            $table->timestamps();
        });

        Schema::create('media_manager_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('media_manager_id')->unsigned()->index();
            $table->string('title');
            $table->longText('description')->nullable()->default(null);
            $table->string('locale')->index();
            $table->boolean('visible')->default(true)->index();

            $table->timestamps();

            $table->unique([
                'media_manager_id',
                'locale',
            ]);

            $table->foreign('media_manager_id')->references('id')->on('media_manager')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::drop('media_manager');
        Schema::drop('media_manager_translations');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
