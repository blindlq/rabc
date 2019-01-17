<?php
/**
 * Created by PhpStorm.
 * User: junhai
 * Date: 2018/7/25
 * Time: 17:49
 */

namespace Junhai\Rabc;

use Junhai\Rabc\Contacts\PermissionInterface;
use Junhai\Rabc\Traits\PermissionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class RabcPermission extends Model implements PermissionInterface
{
    use PermissionTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('rabc.permissions_table');
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