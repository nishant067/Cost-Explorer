<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ID
 * @property string $Title
 * @property int $Client_ID
 */
class Project extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['ID', 'Title', 'Client_ID'];

}
