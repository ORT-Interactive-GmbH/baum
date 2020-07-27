<?php

class CategoryTestCase extends BaumTestCase {

  public static function setUpBeforeClass(): void {
    with(new CategoryMigrator)->up();
  }

  public function setUp(): void {
    with(new CategorySeeder)->run();
  }

  protected function categories($name, $className = Baum\Tests\Models\Category::class) {
    return forward_static_call_array(array($className, 'where'), array('name', '=', $name))->first();
  }

}
