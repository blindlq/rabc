<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/25
 * Time: 16:31
 */

namespace Junhai\Rabc\Traits;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

trait PermissionTrait
{
    /**
     * 角色权限多对多关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(){
        return $this->belongsToMany(Config::get('rabc.role'),Config::get('rabc.permission_role_table'),Config::get('rabc.permission_foreign_key'),Config::get('rabc.role_foreign_key'));
    }

    /**
     * 权限删除（中间表关系也删除）
     * @param object|array $permission
     * @return bool
     */
    public function deletePermission()
    {
        $this->status = 0;

        $this->deletePerRoleAll();

        $this->save();

        return true;


//        if(is_string($permission)){
//            $per = $this->entiy->where('permission_name',$permission)->get();
//            $per->status = 0;
//
//            $this->deletePerRole($per);
//
//            $per->save();
//            return true;
//        }

    }

    /**
     * 删除单一中间表关系
     * @param object"|array  $role
     * @return mixed
     */
    public function detachPerRole($role)
    {
        $per_role = 0;
        if(is_object($role)) {
            $per_role = $this->roles()->select(Config::get('rabc.permission_role_table') . '.id')
                ->where(Config::get('rabc.permission_role_table') . '.role_id', $role->getKey())->get();
        }

        if(is_array($role)){
            return  $this->detachPerRoles($role);

        }

        return $this->deletePerRole($per_role);

    }

    /**
     * 初始化加载器，子类重写后使用
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * 获取中间表关联id
     * @param object  $permission
     * @return array
     */
    protected function getPerRoleIds():array
    {
        $per_roles = $this->roles()->select(Config::get('rabc.permission_role_table').'.id')
                                        ->where(Config::get('rabc.permission_role_table').'.status',1)
                                        ->get();
        $ids = [];
        foreach ($per_roles as $per_role){
            $ids[] = $per_role->id;
        }

        return $ids;
    }

    /**
     * 删除权限对应的所有中间表关系
     * @param object  $permission
     * @return mixed
     */
    private function deletePerRoleAll()
    {
     return  DB::table(Config::get('rabc.permission_role_table'))->whereIn('id',$this->getPerRoleIds())->update(['status' => 0]);
    }

    /**
     *删除中间表单一关系
     */
    private function deletePerRole($pre_role)
    {
        if(!$pre_role){
            return false;
        }
      return DB::table(Config::get('rabc.permission_role_table'))->where('id',$pre_role->id)->update(['status' => 0]);
    }


    /**
     * @param $roles
     */
    public function detachPerRoles($roles)
    {
        foreach ($roles as $role){
            $this->deletePerRole($role);
        }
    }

}
