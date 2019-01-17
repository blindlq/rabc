<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/25
 * Time: 15:59
 */

namespace Junhai\Rabc\Contacts;


interface RoleInterface
{
    /**
     * 用户角色多对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users();

    /**
     * 权限角色多对多关系
     * .
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function perms();

    /**
     * 保存权限
     * @param $inputPermissions
     * @return mixed
     */
    public function savePermissions($inputPermissions);

    /**
     * 为当前角色添加单个权限
     * @param object|array $permission
     * @return mixed
     */
    public function attachPermission($permission);

    /**
     * 从当前角色删除单个权限
     * @param object|array $permission
     * @return mixed
     */
    public function detachPermission($permission);

    /**
     * 为当前角色添加多个权限
     * @param $permissions
     * @return mixed
     */
    public function attachPermissions($permissions);

    /**
     * 为当角色删除多个权限
     * @param $permissions
     * @return mixed
     */
    public function detachPermissions($permissions);
}