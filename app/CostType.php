<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ID
 * @property string $Name
 * @property int $Parent_Cost_Type_ID
 */
class CostType extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['ID', 'Name', 'Parent_Cost_Type_ID'];

}
