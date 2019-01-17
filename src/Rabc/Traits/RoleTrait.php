<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/25
 * Time: 18:04
 */

namespace Junhai\Rabc\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait RoleTrait
{
    /**
     * 用户角色多对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(){
        return $this->belongsToMany(Config::get('auth.providers.users.model'), Config::get('rabc.role_user_table'), Config::get('rabc.role_foreign_key'), Config::get('rabc.user_foreign_key'));
    }

    /**
     * 权限角色多对多关系
     * .
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function perms(){

        return $this->belongsToMany(Config::get('rabc.permission'), Config::get('rabc.permission_role_table'), Config::get('rabc.role_foreign_key'), Config::get('rabc.permission_foreign_key'));
    }
    /**
     * 初始化加载器，子类重写后使用
     */
    public static function boot()
    {
        parent::boot();
    }
    //查询缓存
    public function cachedPermissions(){
        $rolePrimaryKey = $this->primaryKey;
        $cacheKey = 'rabc_permissions_for_role_' . $this->$rolePrimaryKey;
        if(Cache::getStore() instanceof TaggableStore){
            return Cache::tags(Config::get('rabc.permission_role_table'))->remember($cacheKey, Config::get('cache.ttl', 60), function () {

                return $this->perms()->wherePivot('status',1)->where(Config::get('rabc.permissions_table').'.status',1)->get();
            });
        }else{
            return $this->perms()->wherePivot('status',1)->where(Config::get('rabc.permissions_table').'.status',1)->get();
        }
    }

    public function save(array $options = [])
    {   //both inserts and updates
        if (!parent::save($options)) {
            return false;
        }
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('rabc.permission_role_table'))->flush();
        }
        return parent::save($options);
    }

    public function delete(array $options = [])
    {   //soft or hard
        if (!parent::delete($options)) {
            return false;
        }
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('rabc.permission_role_table'))->flush();
        }
        return parent::delete($options);
    }

    /**
     * @return bool
     */
    public function deleteRole()
    {
        $this->status = 0;

        $this->deleteRolePreAll();

        $this->deleteRoleUserAll();

        $this->save();

        return true;
    }


    /**
     * 验证角色是否有该权限
     * @param $name
     * @param bool $requireAll
     * @return bool
     */
    public function hasPermission($name,$requireAll = false)
    {
        if(is_array($name)){
            foreach ($name as $permissionName) {
                $hasPermission = $this->hasPermission($permissionName);

                if($hasPermission && !$requireAll){
                    return true;
                } elseif (!$hasPermission && $requireAll){
                    return false;
                }
            }

            return $requireAll;
        }else{
            foreach ($this->cachedPermissions() as $permission){
                if($permission->permission_name == $name){
                    return true;
                }
            }
        }

        return false;

    }

    /**
     * 保存权限
     * @param $inputPermissions
     * @return viod
     */
    public function savePermissions($inputPermissions)
    {
        if(!empty($inputPermissions)){
            $this->perms()->sync($inputPermissions);
        }else{
            $this->perms()->detach();
        }

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('rabc.permission_role_table'))->flush();
        }
    }

    /**
     * 为当前角色添加单个权限
     * @param object|array $permission
     * @return mixed
     */
    public function attachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission))
            $permission = $permission['id'];

        if($this->isInRolePerm($permission)){
            $this->attachRolePerm($this->isInRolePerm($permission));
        }else{
            $this->perms()->attach($permission);

            if (Cache::getStore() instanceof TaggableStore) {
                Cache::tags(Config::get('rabc.permission_role_table'))->flush();
            }
        }

    }

    /**
     * 从当前角色删除单个权限
     * @param object|array $permission
     * @return mixed
     */
    public function detachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission))
            $permission = $permission['id'];

       $this->deleteRolePre($permission);
    }

    /**
     *是否存在关联关系
     */
    protected function isInRolePerm($permission_id)
    {
        $role_perm = $this->perms()->select(Config::get('rabc.permission_role_table').'.*')
            ->where(Config::get('rabc.permission_role_table') . '.permission_id',$permission_id)->get()->first();

        if($role_perm && ($role_perm->role_id == $this->id)){
            return $role_perm->id;
        }

        return false;

    }

    /**
     * 添加中间表关系（软删除，状态恢复）
     */
    protected function attachRolePerm($role_perm_id)
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('rabc.permission_role_table'))->flush();
        }

        return  DB::table(Config::get('rabc.permission_role_table'))->where('id',$role_perm_id)->update(['status' => 1]);
    }

    /**
     * 中间表删除角色权限关联
     * @param $permission_id
     * @return bool|int
     */
    protected function deleteRolePre($permission_id)
    {
        $role_per = $this->perms()->select(Config::get('rabc.permission_role_table') . '.id')
            ->where(Config::get('rabc.permission_role_table') . '.permission_id', $permission_id)->get();


        if($role_per){
            if (Cache::getStore() instanceof TaggableStore) {
                Cache::tags(Config::get('rabc.permission_role_table'))->flush();
            }

            foreach ($role_per as $item) {
                $id = $item->id;
            }
            return DB::table(Config::get('rabc.permission_role_table'))->where('id',$id)->update(['status' => 0]);
        }

        return true;
    }

    /**
     * 为当角色删除多个权限
     * @param $permissions
     */
    public function detachPermissions($permissions)
    {
        foreach ($permissions as $permission){
            $this->detachPermission($permission);
        }
    }

    /**
     * 为当前角色添加多个权限
     * @param $permissions
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission){
            $this->attachPermission($permission);
        }
    }

    /**
     * 删除角色对应的所有权限关系
     * @return int
     */
    protected function deleteRolePreAll()
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('rabc.permission_role_table'))->flush();
        }

      return DB::table(Config::get('rabc.permission_role_table'))->whereIn('id',$this->getRolePerIds())->update(['status' => 0]);
    }

    /**
     * 删除角色对应的所有用户关系
     * @return int
     */
    protected function deleteRoleUserAll()
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('rabc.role_user_table'))->flush();
        }

        return DB::table(Config::get('rabc.role_user_table'))->whereIn('id',$this->getRoleUserIds())->update(['status' => 0]);
    }

    /**
     * 获取所有中间表的的角色权限的主键id
     * @return array
     */
    private function getRolePerIds()
    {
        $role_perms = $this->perms()->select(Config::get('rabc.permission_role_table').'.id')
            ->where(Config::get('rabc.permission_role_table').'.status',1)
            ->get();

        $ids = [];
        foreach ($role_perms as $role_perm){
            $ids[] = $role_perm->id;
        }

        return $ids;
    }

    /**
     * 获取所有中间表的的角色用户的主键id
     * @return array
     */
    private function getRoleUserIds()
    {
        $role_users = $this->users()->select(Config::get('rabc.role_user_table').'.id')
            ->where(Config::get('rabc.role_user_table').'.status',1)->get();

        $ids = [];
        foreach ($role_users as $role_user){
            $ids[] = $role_user->id;
        }

        return $ids;
    }






}