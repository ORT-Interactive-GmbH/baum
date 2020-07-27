<?php

namespace Baum\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Baum\Node;

class Category extends Node
{

    protected $table = 'categories';

    public $timestamps = false;

}
