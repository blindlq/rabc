<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/25
 * Time: 16:09
 */

namespace Junhai\Rabc\Contacts;


interface UserInterface
{
    /**
     * 用户角色多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();

    /**
     * 当前用户是否拥有此角色通过角色名
     * @param string|array $name       Role name or array of role names.
     * @param bool         $requireAll 用户全部角色是否必须
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false);

    /**
     * 当前用户是否拥有此权限通过权限名
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool         $requireAll 用户全部权限是否必须
     *
     * @return bool
     */
    public function can($permission, $requireAll = false);

    /**
     * 多对多关系添加的二次封装
     *
     * @param mixed $role
     */
    public function attachRole($role);

    /**
     * 多对多关系删除的二次封装
     *
     * @param mixed $role
     */
    public function detachRole($role);

    /**
     *  为当前用户添加多个角色
     * @param $roles
     * @return mixed
     */
    public function attachRoles($roles);

    /**
     * 删除用户的多个角色
     *
     * @param mixed $roles
     */
    public function detachRoles($roles);
}