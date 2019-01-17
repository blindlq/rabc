<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/26
 * Time: 11:50
 */

namespace Junhai\Rabc;

use Junhai\Rabc\Contacts\RoleInterface;
use Junhai\Rabc\Traits\RoleTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class RabcRole extends Model implements RoleInterface
{
    use RoleTrait;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('rabc.roles_table');
    }


    /**
     * Get the relationships for the entity.
     *
     * @return array
     */
    public function getQueueableRelations()
    {
       parent::getQueueableRelations();
    }
}