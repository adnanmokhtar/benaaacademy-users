<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MakeUsersTable extends Migration
{
    /*
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->index();
            $table->renameColumn('name', 'first_name');
            $table->index('first_name');
            $table->string('last_name')->nullable()->index();
            $table->index('created_at');
            $table->index('updated_at');
            $table->string('provider')->nullable()->index();
            $table->string('provider_id')->nullable()->index();
            $table->string('api_token', 60)->nullable()->unique();
            $table->string('code')->nullable()->index();
            $table->integer('role_id')->default(0)->index();
            $table->integer('last_login')->nullable()->index();
            $table->integer('status')->default(0)->index();
            $table->integer('backend')->default(0)->index();
            $table->integer('root')->default(0)->index();
            $table->integer('photo_id')->default(0)->index();
            $table->string('lang', 5)->default("en")->index();
            $table->string('color', 20)->default("blue")->index();
            $table->text('about')->nullable();
            $table->string('facebook')->nullable()->index();
            $table->string('twitter')->nullable()->index();
            $table->string('linked_in')->nullable()->index();
            $table->string('google_plus')->nullable()->index();
        });
    }

    /*
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('first_name', 'name');
            $table->dropIndex('users_first_name_index');
            $table->dropIndex('users_created_at_index');
            $table->dropIndex('users_updated_at_index');
            $table->dropColumn("last_name");
            $table->dropColumn("username");
            $table->dropColumn("provider");
            $table->dropColumn("provider_id");
            $table->dropColumn("api_token");
            $table->dropColumn("code");
            $table->dropColumn("remember_token");
            $table->dropColumn("role_id");
            $table->dropColumn("last_login");
            $table->dropColumn("status");
            $table->dropColumn("root");
            $table->dropColumn('backend');
            $table->dropColumn("photo_id");
            $table->dropColumn("lang");
            $table->dropColumn("color");
            $table->dropColumn("about");
            $table->dropColumn("facebook");
            $table->dropColumn("twitter");
            $table->dropColumn("linked_in");
            $table->dropColumn("google_plus");
        });
    }
}
