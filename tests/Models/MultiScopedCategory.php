<?php

namespace Baum\Tests\Models;

class MultiScopedCategory extends Category
{

    protected $scoped = array('company_id', 'language');

}

