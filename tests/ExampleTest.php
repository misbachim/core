<?php

class ExampleTest extends SeededTestCase
{
    public static function setUpBeforeClass()
    {
        self::read('your_table_name');
        parent::setUpBeforeClass();
    }

    public function test()
    {
        $this->assertEquals(1, 1);
    }
}
