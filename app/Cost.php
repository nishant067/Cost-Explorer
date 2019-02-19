<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ID
 * @property float $Amount
 * @property int $Cost_Type_ID
 * @property int $Project_ID
 */
class Cost extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['ID', 'Amount', 'Cost_Type_ID', 'Project_ID'];

}
