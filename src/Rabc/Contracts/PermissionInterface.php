<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/25
 * Time: 15:55
 */

namespace Junhai\Rabc\Contacts;


interface PermissionInterface
{
    /**
     * 角色权限多对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();

    /**
     *  权限删除（软删除）
     * @param object|array $permission
     * @return mixed
     */
    public function deletePermission();

}