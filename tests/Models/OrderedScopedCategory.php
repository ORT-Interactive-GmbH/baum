<?php

namespace Baum\Tests\Models;

class OrderedScopedCategory extends Category
{

    protected $scoped = array('company_id');

    protected $orderColumn = 'name';

}
