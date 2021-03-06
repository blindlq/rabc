<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/25
 * Time: 15:23
 */

//角色权限管理解决方案


return [

    /*
    |--------------------------------------------------------------------------
    | 角色表模型
    |--------------------------------------------------------------------------
    |
    | 根据项目实际命名空间设置路径
    |
    */

    'role' => 'App\Models\Role',

    /*
    |--------------------------------------------------------------------------
    | 角色表
    |--------------------------------------------------------------------------
    |
    */
    'roles_table' => 'role',

    /*
    |--------------------------------------------------------------------------
    | 权限表模型
    |--------------------------------------------------------------------------
    |
    | 根据项目实际命名空间设置路径
    |
    */

    'permission' => 'App\Models\Permission',

    /*
    |--------------------------------------------------------------------------
    | 权限表
    |--------------------------------------------------------------------------
    |
    */

    'permissions_table' => 'permission',


    /*
    |--------------------------------------------------------------------------
    | 角色权限关系表
    |--------------------------------------------------------------------------
    |
    */

    'permission_role_table' => 'permission_role',

    /*
    |--------------------------------------------------------------------------
    | 用户角色关系表
    |--------------------------------------------------------------------------
    |
    */
    'role_user_table' => 'role_user',

    /*
    |--------------------------------------------------------------------------
    | 用户角色关系表的用户外键
    |--------------------------------------------------------------------------
    */
    'user_foreign_key' => 'user_id',

    /*
    |--------------------------------------------------------------------------
    | 用户角色关系表的角色外键
    | 角色权限关系表的角色外键
    |--------------------------------------------------------------------------
    */
    'role_foreign_key' => 'role_id',

    /*
    |--------------------------------------------------------------------------
    | 角色权限关系表的权限外键
    |--------------------------------------------------------------------------
    */
    'permission_foreign_key' =>'permission_id'


];