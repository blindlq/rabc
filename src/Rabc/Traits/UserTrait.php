<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/26
 * Time: 15:32
 */

namespace Junhai\Rabc\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

trait UserTrait
{
    /**
     * 用户角色多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('rabc.role'), Config::get('rabc.role_user_table'), Config::get('rabc.user_foreign_key'), Config::get('rabc.role_foreign_key'));
    }
    /**
     * 初始化加载器，子类重写后使用
     */
    public static function boot()
    {
        parent::boot();
    }

    //查询缓存
    public function cachedRoles()
    {

        $userPrimaryKey = $this->primaryKey;
        $cacheKey = 'rabc_roles_for_user_'.$this->$userPrimaryKey;
        if(Cache::getStore() instanceof TaggableStore) {
            return Cache::tags(Config::get('rabc.role_user_table'))->remember($cacheKey, Config::get('cache.ttl',60), function () {
                return $this->roles()->wherePivot('status',1)->where(Config::get('rabc.roles_table').'.status',1)->get();
            });
        }
        else{
            return $this->roles()->wherePivot('status',1)->where(Config::get('rabc.roles_table').'.status',1)->get();
        }
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function save(array $options = [])
    {
        $result = parent::save($options);
        if(Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('rabc.role_user_table'))->flush();
        }
        return $result;
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function delete(array $options = [])
    {   //soft or hard
        $result = parent::delete($options);
        Cache::tags(Config::get('rabc.role_user_table'))->flush();
        return $result;
    }

    public function deleteUser()
    {
        $this->status = 0;

        $this->deleteUserRoleAll();

        $this->save();

        return true;
    }

    /**
     * 验证用户是否有该角色
     * @param $name
     * @param bool $requireAll
     * @return bool
     */
    public function hasRole($name, $requireAll = false)
    {
        if(is_array($name)){
            foreach ($name as $roleName){
                $hasRole = $this->hasRole($roleName);

                if ($hasRole && !$requireAll) {
                    return true;
                } elseif (!$hasRole && $requireAll) {
                    return false;
                }

            }

            return $requireAll;
        }else{
            foreach ($this->cachedRoles() as $role){
                if($role->role_name == $name){
                    return true;
                }
            }

        }

        return false;
    }

    /**
     * 用户是否有该权限
     * @param $permission
     * @param bool $requireAll
     * @return bool
     */
    public function can($permission, $requireAll = false)
    {

        if(is_array($permission)){
            foreach ($permission as $permName){
                $hasPerm = $this->can($permName);

                if ($hasPerm && !$requireAll) {
                    return true;
                } elseif (!$hasPerm && $requireAll) {
                    return false;
                }
            }

            return $requireAll;

        }else{

            foreach ($this->cachedRoles() as $role){
                foreach ($role->cachedPermissions() as $perm){

                    if (str_is( $permission, $perm->permission_name) ) {
                        return true;
                    }
                }
            }


        }
        return false;
    }

    /**
     *角色 和 权限 多样验证
     * @param $roles
     * @param $permissions
     * @param array $options
     * @return array|bool
     */
    public function ability($roles, $permissions, $options = [])
    {
        //分割角色
        if (!is_array($roles)) {
            $roles = explode(',', $roles);
        }
        //分割权限
        if (!is_array($permissions)) {
            $permissions = explode(',', $permissions);
        }
        //验证返回和设置返回类型
        if (!isset($options['validate_all'])) {
            $options['validate_all'] = false;
        } else {
            if ($options['validate_all'] !== true && $options['validate_all'] !== false) {
                throw new InvalidArgumentException();
            }
        }
        if (!isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        } else {
            if ($options['return_type'] != 'boolean' &&
                $options['return_type'] != 'array' &&
                $options['return_type'] != 'both') {
                throw new InvalidArgumentException();
            }
        }
        //验证权限和角色
        $checkedRoles = [];
        $checkedPermissions = [];
        foreach ($roles as $role) {
            $checkedRoles[$role] = $this->hasRole($role);
        }
        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] = $this->can($permission);
        }
        //范围验证
        if(($options['validate_all'] && !(in_array(false,$checkedRoles) || in_array(false,$checkedPermissions))) ||
            (!$options['validate_all'] && (in_array(true,$checkedRoles) || in_array(true,$checkedPermissions)))) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }
        //根据返回类型，返回数据
        if ($options['return_type'] == 'boolean') {
            return $validateAll;
        } elseif ($options['return_type'] == 'array') {
            return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
        } else {
            return [$validateAll, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
        }


    }

    /**
     * 多对多关系添加的二次封装
     *
     * @param mixed $role
     */
    public function attachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        if($this->isInUserRole($role)){
            $this->attachUserRole($this->isInUserRole($role));
        }else{

            if(Cache::getStore() instanceof TaggableStore) {
                Cache::tags(Config::get('rabc.role_user_table'))->flush();
            }

            $this->roles()->attach($role);
        }

    }

    /**
     * 多对多关系删除的二次封装
     *
     * @param mixed $role
     */
    public function detachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->deleteUserRole($role);

    }

    /**
     *  为当前用户添加多个角色
     * @param $roles
     * @return mixed
     */
    public function attachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->attachRole($role);
        }
    }

    /**
     * 删除用户的多个角色
     *
     * @param mixed $roles
     */
    public function detachRoles($roles)
    {
        if (!$roles) $roles = $this->roles()->wherePivot('status',1)->where(Config::get('rabc.role').'.status',1)->get();

        foreach ($roles as $role) {
            $this->detachRole($role);
        }
    }

    /**
     * 是否存在关联关系
     */
    protected function isInUserRole($role)
    {
        $user_role = $this->roles()->select(Config::get('rabc.role_user_table').'.*')
            ->where(Config::get('rabc.role_user_table') . '.role_id',$role)->get()->first();

        if($user_role && ($user_role->user_id == $this->id)){
            return $user_role->id;
        }

        return false;
    }

    /**
     * 添加中间表关系（软删除，状态恢复）
     */
    protected function attachUserRole($user_role_id)
    {
        if(Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('rabc.role_user_table'))->flush();
        }

        return DB::table(Config::get('rabc.role_user_table'))->where('id',$user_role_id)->update(['status' => 1]);
    }

    /**
     * 删除用户对应的所有角色关系
     * @return int
     */
    protected function deleteUserRoleAll()
    {
        if(Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('rabc.role_user_table'))->flush();
        }

        return  DB::table(Config::get('rabc.role_user_table'))->whereIn('id',$this->getUserRoleIds())->update(['status' => 0]);
    }

    /**
     * 删除用户对应的角色关系（单对单）
     * @return int
     */
    protected function deleteUserRole($role_id)
    {
        $user_role = $this->roles()->select(Config::get('rabc.role_user_table') . '.id')
            ->where(Config::get('rabc.role_user_table') . '.role_id', $role_id)->get();

        if($user_role){

            if(Cache::getStore() instanceof TaggableStore) {
                Cache::tags(Config::get('rabc.role_user_table'))->flush();
            }

            foreach ($user_role as $item) {
                $id = $item->id;
            }
            return  DB::table(Config::get('rabc.role_user_table'))->where('id',$id)->update(['status' => 0]);
        }
        return true;
    }

    /**
     * 获取所有中间表的的用户角色的主键id
     * @return array
     */
    private function getUserRoleIds()
    {
        $user_roles = $this->roles()->select(Config::get('rabc.role_user_table').'.id')
            ->where(Config::get('rabc.role_user_table').'.status',1)->get();

        $ids = [];
        foreach ($user_roles as $user_role){
            $ids[] = $user_role->id;
        }

        return $ids;
    }


}

