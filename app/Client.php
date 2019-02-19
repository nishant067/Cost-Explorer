<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ID
 * @property string $Name
 */
class Client extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['ID', 'Name'];

}
