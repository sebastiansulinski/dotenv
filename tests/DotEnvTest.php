<?php namespace SSDTest;

use RuntimeException;

use SSD\DotEnv\DotEnv;

class DotEnvTest extends BaseCase
{
    /**
     * @test
     */
    public function loads_single_file()
    {
        $dotenv = new DotEnv($this->pathname('.env'));
        $dotenv->load();

        $this->assertEquals(DotEnv::get('ENVIRONMENT'), 'live');
    }

    /**
     * @test
     */
    public function loads_multiple_files_from_directory_path()
    {
        $dotenv = new DotEnv($this->path);
        $dotenv->load();

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
        $dotenv = new DotEnv([
            $this->pathname('.env'),
            $this->pathname('.env.database'),
            $this->pathname('.env.nested')
        ]);
        $dotenv->load();

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
        $dotenv = new DotEnv($this->pathname('.env'));
        $dotenv->load();

        $this->assertNull(DotEnv::get('DB_HOST'));
    }

    /**
     * @test
     */
    public function returns_nested_variable()
    {
        $dotenv = new DotEnv($this->pathname('.env.nested'));
        $dotenv->load();

        $this->assertSame(DotEnv::get('MAIL_PASS'), DotEnv::get('MAIL_API_KEY'));
    }

    /**
     * @test
     */
    public function overwrites_previously_declared_variables()
    {
        $dotenv = new DotEnv([
            $this->pathname('.env'),
            $this->overwrite_pathname('.env')
        ]);
        $dotenv->overload();

        $this->assertEquals(DotEnv::get('ENVIRONMENT'), 'production');
    }

    /**
     * @test
     */
    public function strips_double_quotes_from_beginning_and_end()
    {
        $dotenv = new DotEnv([
            $this->quotes_pathname('.env')
        ]);
        $dotenv->overload();

        $this->assertEquals(DotEnv::get('DOUBLE_QUOTE_BOTH'), '$somestring');
        $this->assertEquals(DotEnv::get('DOUBLE_QUOTE_LEFT'), '"$somestring');
        $this->assertEquals(DotEnv::get('DOUBLE_QUOTE_RIGHT'), '$somestring"');
    }

    /**
     * @test
     */
    public function strips_single_quotes_from_beginning_and_end()
    {
        $dotenv = new DotEnv([
            $this->quotes_pathname('.env')
        ]);
        $dotenv->overload();

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
        $dotenv = new DotEnv([
            $this->quotes_pathname('.env')
        ]);
        $dotenv->load();
        $dotenv->required('EMPTY_VARIABLE');
    }

    /**
     * @test
     *
     * @expectedException RuntimeException
     */
    public function required_method_throws_exception_without_array_of_valid_variables()
    {
        $dotenv = new DotEnv([
            $this->quotes_pathname('.env')
        ]);
        $dotenv->load();
        $dotenv->required([
            'EMPTY_VARIABLE',
            'ANOTHER_EMPTY_VARIABLE'
        ]);
    }

    /**
     * @test
     */
    public function returns_boolean_null_and_empty()
    {
        $dotenv = new DotEnv([
            $this->pathname('.env.bool_null')
        ]);
        $dotenv->load();

        $this->assertTrue(DotEnv::get('VARIABLE_BOOL_TRUE'));
        $this->assertFalse(DotEnv::get('VARIABLE_BOOL_FALSE'));
        $this->assertNull(DotEnv::get('VARIABLE_NULL'));
        $this->assertEmpty(DotEnv::get('VARIABLE_NONE'));
    }

    /**
     * @test
     */
    public function returns_defaul_if_non_existent()
    {
        $dotenv = new DotEnv([
            $this->pathname('.env.bool_null')
        ]);
        $dotenv->load();

        $this->assertSame('Test', DotEnv::get('NON_EXISTENT', 'Test'));
    }

    /**
     * @test
     */
    public function is_returns_correct_boolean()
    {
        $dotenv = new DotEnv([
            $this->pathname('.env')
        ]);
        $dotenv->overload();

        $this->assertTrue(DotEnv::is('ENVIRONMENT', 'live'));
        $this->assertFalse(DotEnv::is('ENVIRONMENT', 'local'));
    }

    /**
     * @test
     */
    public function has_returns_correct_boolean()
    {
        $dotenv = new DotEnv([
            $this->pathname('.env.bool_null')
        ]);
        $dotenv->load();

        $this->assertTrue(DotEnv::has('VARIABLE_BOOL_TRUE'));
        $this->assertTrue(DotEnv::has('VARIABLE_BOOL_FALSE'));
        $this->assertTrue(DotEnv::has('VARIABLE_NULL'));
        $this->assertFalse(DotEnv::has('VARIABLE_NONE'));
    }
}