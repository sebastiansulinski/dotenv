<?php namespace SSDTest;

use PHPUnit_Framework_Error;

use SSD\DotEnv\DotEnv;

class DotEnvTest extends BaseCase
{
    /**
     * @test
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function constructor_throws_error_without_the_argument()
    {
        $dotenv = new DotEnv();
    }

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
            $this->pathname('overwrite' . DIRECTORY_SEPARATOR . '.env')
        ]);
        $dotenv->overload();

        $this->assertEquals(DotEnv::get('ENVIRONMENT'), 'production');
    }
}