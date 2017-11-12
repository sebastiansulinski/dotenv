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
    public function loads_multiple_files_as_array()
    {
        $dotEnv = new DotEnv([
            $this->pathname('.env'),
            $this->pathname('.env.database'),
            $this->pathname('.env.nested')
        ]);
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
    public function overwrites_previously_declared_variables()
    {
        $dotEnv = new DotEnv([
            $this->pathname('.env'),
            $this->overwrite_pathname('.env')
        ]);
        $dotEnv->overload();

        $this->assertEquals(DotEnv::get('ENVIRONMENT'), 'production');
    }

    /**
     * @test
     */
    public function strips_double_quotes_from_beginning_and_end()
    {
        $dotEnv = new DotEnv([
            $this->quotes_pathname('.env')
        ]);
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
        $dotEnv = new DotEnv([
            $this->quotes_pathname('.env')
        ]);
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
        $dotEnv = new DotEnv([
            $this->quotes_pathname('.env')
        ]);
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
        $dotEnv = new DotEnv([
            $this->quotes_pathname('.env')
        ]);
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
        $dotEnv = new DotEnv([
            $this->pathname('.env.bool_null')
        ]);
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
        $dotEnv = new DotEnv([
            $this->pathname('.env.bool_null')
        ]);
        $dotEnv->load();

        $this->assertSame('Test', DotEnv::get('NON_EXISTENT', 'Test'));
    }

    /**
     * @test
     */
    public function is_returns_correct_boolean()
    {
        $dotEnv = new DotEnv([
            $this->pathname('.env')
        ]);
        $dotEnv->overload();

        $this->assertTrue(DotEnv::is('ENVIRONMENT', 'live'));
        $this->assertFalse(DotEnv::is('ENVIRONMENT', 'local'));
    }

    /**
     * @test
     */
    public function has_returns_correct_boolean()
    {
        $dotEnv = new DotEnv([
            $this->pathname('.env.bool_null')
        ]);
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
    public function sets_new_variable()
    {
        $dotEnv = new DotEnv([
            $this->pathname('.env.bool_null')
        ]);
        $dotEnv->load();


        $this->assertFalse(DotEnv::has('CUSTOM_VARIABLE'));

        $dotEnv->set('CUSTOM_VARIABLE', 123);

        $this->assertTrue(DotEnv::has('CUSTOM_VARIABLE'));
        $this->assertTrue(DotEnv::is('CUSTOM_VARIABLE', 123));
        $this->assertEquals(123, DotEnv::get('CUSTOM_VARIABLE'));

        $dotEnv->required('CUSTOM_VARIABLE');
    }
}