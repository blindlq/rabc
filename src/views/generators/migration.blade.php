<?php echo '<?php' ?>

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class EntrustSetupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create table for storing roles
        Schema::create('{{ $rolesTable }}', function (Blueprint $table) {
            $table->charset='utf8mb4';
            $table->engine='innodb';
            $table->comment = '角色表';

            $table->increments('id')->comment('角色ID');
            $table->string('role_name',20)->default('')->comment('角色名');
            $table->string('description',30)->default('')->comment('角色描述');
            $table->tinyInteger('status')->default(1)->comment('状态: 0禁用, 1启用');
            $table->dateTime('create_time')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->dateTime('update_time')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新时间');

            $table->index('role_name','role_name_index');
            $table->comment = '角色表';
        });

        // Create table for associating roles to users (Many-to-Many)
        Schema::create('{{ $roleUserTable }}', function (Blueprint $table) {
            $table->charset='utf8mb4';
            $table->engine='innodb';
            $table->comment = '用户角色关系表';


            $table->increments('id')->comment('用户角色关系ID');
            $table->integer('user_id')->comment('用户ID');
            $table->integer('role_id')->comment('角色ID');
            $table->tinyInteger('status')->default(1)->comment('状态: 0禁用, 1启用');
            $table->dateTime('create_time')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->dateTime('update_time')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新时间');

            $table->index('user_id','role_user_index');
            $table->index('role_id','user_role_index');
        });

        // Create table for storing permissions
        Schema::create('{{ $permissionsTable }}', function (Blueprint $table) {
            $table->charset='utf8mb4';
            $table->engine='innodb';
            $table->comment = '权限表';


            $table->increments('id')->comment('权限ID');
            $table->string('permission_name',20)->unique()->comment('权限名');
            $table->string('description',30)->default('')->comment('角色描述');
            $table->tinyInteger('status')->default(1)->comment('状态: 0禁用, 1启用');
            $table->dateTime('create_time')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->dateTime('update_time')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新时间');

            $table->index('permission_name','permission_name_index');
        });

        // Create table for associating permissions to roles (Many-to-Many)
        Schema::create('{{ $permissionRoleTable }}', function (Blueprint $table) {
            $table->charset='utf8mb4';
            $table->engine='innodb';
            $table->comment = '角色权限关系表';

            $table->increments('id')->comment('角色权限关系ID');
            $table->integer('permission_id')->comment('权限ID');
            $table->integer('role_id')->comment('角色ID');
            $table->tinyInteger('status')->default(1)->comment('状态: 0禁用, 1启用');
            $table->dateTime('create_time')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->dateTime('update_time')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新时间');

            $table->index('permission_id','role_permission_index');
            $table->index('role_id','permission_role_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('{{ $permissionRoleTable }}');
        Schema::drop('{{ $permissionsTable }}');
        Schema::drop('{{ $roleUserTable }}');
        Schema::drop('{{ $rolesTable }}');
    }
}
