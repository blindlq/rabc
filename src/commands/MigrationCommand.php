<?php

namespace Junhai\Rabc;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class MigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabc:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a migration following the Rabc specifications.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->laravel->view->addNamespace('Rabc', substr(__DIR__, 0, -8).'views');
        //待创建的表格
        $rolesTable          = Config::get('rabc.roles_table');
        $roleUserTable       = Config::get('rabc.role_user_table');
        $permissionsTable    = Config::get('rabc.permissions_table');
        $permissionRoleTable = Config::get('rabc.permission_role_table');
        //开始命令
        $this->line('');
        $this->info( "Tables: $rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable" );
        $message = "A migration that creates '$rolesTable', '$roleUserTable', '$permissionsTable', '$permissionRoleTable'".
            " tables will be created in database/migrations directory";

        $this->comment($message);
        $this->line('');
        //开始创建
        if ($this->confirm("Proceed with the migration creation? [Yes|no]", "Yes")) {

            $this->line('');

            $this->info("Creating migration...");
            if ($this->createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable)) {

                $this->info("Migration successfully created!");
            } else {
                $this->error(
                    "Couldn't create migration.\n Check the write permissions".
                    " within the database/migrations directory."
                );
            }

            $this->line('');

        }
    }

    /**
     * Create the migration.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function createMigration($rolesTable, $roleUserTable, $permissionsTable, $permissionRoleTable)
    {
        //迁移文件路径和迁移文件名
        $migrationFile = base_path("/database/migrations")."/".date('Y_m_d_His')."_rabc_setup_tables.php";
        //获取用户表信息
        $userModel = Config::get('auth.providers.users.model');
        $userModel = new $userModel;
        $userKeyName = $userModel->getKeyName();
        $usersTable  = $userModel->getTable();

        $data = compact('rolesTable', 'roleUserTable', 'permissionsTable', 'permissionRoleTable', 'usersTable', 'userKeyName');
        //输出视图渲染

        $output = $this->laravel->view->make('Rabc::generators.migration')->with($data)->render();
        //写入可执行文件
        if(!file_exists($migrationFile) && $fs =fopen($migrationFile,'x')){
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;

    }
}
