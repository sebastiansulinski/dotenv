<?php

namespace SSDTest;

use RuntimeException;

use SSD\DotEnv\DotEnv;

class DotEnvTest extends BaseCase
{
    /**
     * @test
     */
    public function loads_single_file()
    {
        $dotEnv = new DotEnv($this->pathname('.env'));


        $dotEnv->load();


        $this->assertEquals(DotEnv::get('ENVIRONMENT'), 'live');
    }

    /**
     * @test
     */
    public function loads_multiple_files_from_directory_path()
    {
        $dotEnv = new DotEnv($this->path);


        $dotEnv->load();


        $this->assertEquals(DotEnv::get('ENVIRONMENT'), 'live');
        $this->assertEquals(DotEnv::get('DB_HOST'), 'localhost');
        $this->assertEquals(DotEnv::get('DB_NAME'), 'test');
        $this->assertEquals(DotEnv::get('DB_USER'), 'user');
        $this->assertEquals(DotEnv::get('DB_PASS'), 'password');
    }

    /**
     * @test
     */
    public function loads_multiple_files()
    {
        $dotEnv = new DotEnv(
            $this->pathname('.env'),
            $this->pathname('.env.database'),
            $this->pathname('.env.nested')
        );


        $dotEnv->load();


        $this->assertEquals(DotEnv::get('ENVIRONMENT'), 'live');
        $this->assertEquals(DotEnv::get('DB_HOST'), 'localhost');
        $this->assertEquals(DotEnv::get('DB_NAME'), 'test');
        $this->assertEquals(DotEnv::get('DB_USER'), 'user');
        $this->assertEquals(DotEnv::get('DB_PASS'), 'password');
    }

    /**
     * @test
     */
    public function returns_null_for_non_existing_variable()
    {
        $dotEnv = new DotEnv($this->pathname('.env'));


        $dotEnv->load();


        $this->assertNull(DotEnv::get('DB_HOST'));
    }

    /**
     * @test
     */
    public function returns_nested_variable()
    {
        $dotEnv = new DotEnv($this->pathname('.env.nested'));


        $dotEnv->load();


        $this->assertSame(DotEnv::get('MAIL_PASS'), DotEnv::get('MAIL_API_KEY'));
    }

    /**
     * @test
     */
    public function load_does_not_overwrite_existing_variables()
    {
        putenv("DB_HOST=127.0.0.1");

        $dotEnv = new DotEnv($this->pathname('.env.database'));

        $this->assertTrue(DotEnv::has('DB_HOST'));


        $dotEnv->load();


        $this->assertTrue(DotEnv::has('DB_HOST'));
        $this->assertEquals('127.0.0.1', DotEnv::get('DB_HOST'));
    }

    /**
     * @test
     */
    public function overwrites_variables_declared_outside_of_the_constructor_loaded_files()
    {
        putenv("DB_HOST=127.0.0.1");

        $dotEnv = new DotEnv($this->pathname('.env.database'));

        $this->assertTrue(DotEnv::has('DB_HOST'));


        $dotEnv->overload();


        $this->assertTrue(DotEnv::has('DB_HOST'));
        $this->assertEquals('localhost', DotEnv::get('DB_HOST'));
    }

    /**
     * @test
     */
    public function overwrites_variables_declared_in_previously_loaded_files()
    {
        $dotEnv = new DotEnv($this->pathname('.env'), $this->overwrite_pathname('.env'));


        $dotEnv->overload();


        $this->assertEquals(DotEnv::get('ENVIRONMENT'), 'production');
    }

    /**
     * @test
     */
    public function strips_double_quotes_from_beginning_and_end()
    {
        $dotEnv = new DotEnv($this->quotes_pathname('.env'));


        $dotEnv->overload();


        $this->assertEquals(DotEnv::get('DOUBLE_QUOTE_BOTH'), '$somestring');
        $this->assertEquals(DotEnv::get('DOUBLE_QUOTE_LEFT'), '"$somestring');
        $this->assertEquals(DotEnv::get('DOUBLE_QUOTE_RIGHT'), '$somestring"');
    }

    /**
     * @test
     */
    public function strips_single_quotes_from_beginning_and_end()
    {
        $dotEnv = new DotEnv($this->quotes_pathname('.env'));


        $dotEnv->overload();


        $this->assertEquals(DotEnv::get('SINGLE_QUOTE_BOTH'), '$asdflkj%&*');
        $this->assertEquals(DotEnv::get('SINGLE_QUOTE_LEFT'), "'\$asdflkj%&*");
        $this->assertEquals(DotEnv::get('SINGLE_QUOTE_RIGHT'), "\$asdflkj%&*'");
    }

    /**
     * @test
     *
     * @expectedException RuntimeException
     */
    public function required_method_throws_exception_without_single_valid_variable()
    {
        $dotEnv = new DotEnv($this->quotes_pathname('.env'));


        $dotEnv->load();


        $dotEnv->required('EMPTY_VARIABLE');
    }

    /**
     * @test
     *
     * @expectedException RuntimeException
     */
    public function required_method_throws_exception_without_array_of_valid_variables()
    {
        $dotEnv = new DotEnv($this->quotes_pathname('.env'));


        $dotEnv->load();


        $dotEnv->required([
            'EMPTY_VARIABLE',
            'ANOTHER_EMPTY_VARIABLE'
        ]);
    }

    /**
     * @test
     */
    public function returns_boolean_null_and_empty()
    {
        $dotEnv = new DotEnv($this->pathname('.env.bool_null'));


        $dotEnv->load();


        $this->assertTrue(DotEnv::get('VARIABLE_BOOL_TRUE'));
        $this->assertFalse(DotEnv::get('VARIABLE_BOOL_FALSE'));
        $this->assertNull(DotEnv::get('VARIABLE_NULL'));
        $this->assertEmpty(DotEnv::get('VARIABLE_NONE'));
    }

    /**
     * @test
     */
    public function returns_default_if_non_existent()
    {
        $dotEnv = new DotEnv($this->pathname('.env.bool_null'));


        $dotEnv->load();


        $this->assertSame('Test', DotEnv::get('NON_EXISTENT', 'Test'));
    }

    /**
     * @test
     */
    public function is_returns_correct_boolean()
    {
        $dotEnv = new DotEnv($this->pathname('.env'));


        $dotEnv->overload();


        $this->assertTrue(DotEnv::is('ENVIRONMENT', 'live'));
        $this->assertFalse(DotEnv::is('ENVIRONMENT', 'local'));
    }

    /**
     * @test
     */
    public function has_returns_correct_boolean()
    {
        $dotEnv = new DotEnv($this->pathname('.env.bool_null'));


        $dotEnv->load();


        $this->assertTrue(DotEnv::has('VARIABLE_BOOL_TRUE'));
        $this->assertTrue(DotEnv::has('VARIABLE_BOOL_FALSE'));
        $this->assertTrue(DotEnv::has('VARIABLE_NULL'));
        $this->assertTrue(DotEnv::has('VARIABLE_NONE'));
        $this->assertFalse(DotEnv::has('VARIABLE_NONE_EXISTENT'));
    }

    /**
     * @test
     */
    public function sets_new_variable_and_sanitizes_value()
    {
        $dotEnv = new DotEnv($this->pathname('.env.bool_null'));


        $dotEnv->load();


        $this->assertFalse(DotEnv::has('CUSTOM_VARIABLE'));


        $dotEnv->set('CUSTOM_VARIABLE', '"something\'s"');
        $dotEnv->set('CUSTOM_VARIABLE_2', '"something\'s');


        $this->assertTrue(DotEnv::has('CUSTOM_VARIABLE'));
        $this->assertTrue(DotEnv::has('CUSTOM_VARIABLE_2'));
        $this->assertTrue(DotEnv::is('CUSTOM_VARIABLE', 'something\'s'));
        $this->assertTrue(DotEnv::is('CUSTOM_VARIABLE_2', '"something\'s'));
        $this->assertEquals('something\'s', DotEnv::get('CUSTOM_VARIABLE'));
        $this->assertEquals('"something\'s', DotEnv::get('CUSTOM_VARIABLE_2'));

        $dotEnv->required('CUSTOM_VARIABLE');
    }

    /**
     * @test
     */
    public function returns_variables_from_files_as_array_and_sets_environment_variables_using_load_method_as_argument()
    {
        putenv("DB_HOST=127.0.0.1");

        $dotEnv = new DotEnv($this->pathname('.env.database'));

        $this->assertTrue(DotEnv::has('DB_HOST'));
        $this->assertEquals('127.0.0.1', DotEnv::get('DB_HOST'));
        $this->assertFalse(DotEnv::has('DB_NAME'));
        $this->assertFalse(DotEnv::has('DB_USER'));
        $this->assertFalse(DotEnv::has('DB_PASS'));


        $this->assertEquals(
            [
                'DB_HOST' => 'localhost',
                'DB_NAME' => 'test',
                'DB_USER' => 'user',
                'DB_PASS' => 'password'
            ],
            $dotEnv->toArray(DotEnv::LOAD)
        );

        $this->assertTrue(DotEnv::has('DB_HOST'));
        $this->assertEquals('127.0.0.1', DotEnv::get('DB_HOST'));
        $this->assertTrue(DotEnv::has('DB_NAME'));
        $this->assertTrue(DotEnv::has('DB_USER'));
        $this->assertTrue(DotEnv::has('DB_PASS'));
    }

    /**
     * @test
     */
    public function returns_variables_from_files_as_array_and_sets_environment_variables_using_overload_method_as_argument()
    {
        putenv("DB_HOST=127.0.0.1");

        $dotEnv = new DotEnv($this->pathname('.env.database'));

        $this->assertTrue(DotEnv::has('DB_HOST'));
        $this->assertEquals('127.0.0.1', DotEnv::get('DB_HOST'));
        $this->assertFalse(DotEnv::has('DB_NAME'));
        $this->assertFalse(DotEnv::has('DB_USER'));
        $this->assertFalse(DotEnv::has('DB_PASS'));


        $this->assertEquals(
            [
                'DB_HOST' => 'localhost',
                'DB_NAME' => 'test',
                'DB_USER' => 'user',
                'DB_PASS' => 'password'
            ],
            $dotEnv->toArray(DotEnv::OVERLOAD)
        );

        $this->assertTrue(DotEnv::has('DB_HOST'));
        $this->assertEquals('localhost', DotEnv::get('DB_HOST'));
        $this->assertTrue(DotEnv::has('DB_NAME'));
        $this->assertTrue(DotEnv::has('DB_USER'));
        $this->assertTrue(DotEnv::has('DB_PASS'));
    }

    /**
     * @test
     */
    public function returns_variables_from_files_as_array_without_setting_environment_variables_when_invalid_argument_provided()
    {
        $dotEnv = new DotEnv($this->pathname('.env.database'));

        $this->assertEquals(
            [
                'DB_HOST' => 'localhost',
                'DB_NAME' => 'test',
                'DB_USER' => 'user',
                'DB_PASS' => 'password'
            ],
            $dotEnv->toArray('invalid')
        );

        $this->assertFalse(DotEnv::has('DB_HOST'));
        $this->assertFalse(DotEnv::has('DB_NAME'));
        $this->assertFalse(DotEnv::has('DB_USER'));
        $this->assertFalse(DotEnv::has('DB_PASS'));
    }

    /**
     * @test
     */
    public function returns_variables_from_files_as_array_without_setting_environment_variables_when_no_argument_provided()
    {
        $dotEnv = new DotEnv($this->pathname('.env.database'));

        $this->assertEquals(
            [
                'DB_HOST' => 'localhost',
                'DB_NAME' => 'test',
                'DB_USER' => 'user',
                'DB_PASS' => 'password'
            ],
            $dotEnv->toArray()
        );

        $this->assertFalse(DotEnv::has('DB_HOST'));
        $this->assertFalse(DotEnv::has('DB_NAME'));
        $this->assertFalse(DotEnv::has('DB_USER'));
        $this->assertFalse(DotEnv::has('DB_PASS'));
    }
}