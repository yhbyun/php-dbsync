<?php
/**
 * DbSync
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/php-dbsync/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to maks.slesarenko@gmail.com so we can send you a copy immediately.
 *
 * @category DbSync
 * @package  Tests
 * @license  http://code.google.com/p/php-dbsync/wiki/License   New BSD License
 * @version  $Id: Console.php 36 2011-10-23 15:15:19Z maks.slesarenko@gmail.com $
 */

/**
 * DbSync_ConsoleTest
 *
 * @group    console
 * @category DbSync
 * @package  Tests
 * @version  $Id$
 */
class DbSync_ConsoleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DbSync_Console
     */
    protected $_console;

    /**
     * @var array
     */
    protected $_fixture = array(
        'prog.php',
        'do',
        'something',
        'stupid',
        '--config',
        'config.xml',
        '--help',
        '--args',
        'arg1',
        'arg2',
        'arg3'
    );

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['argv'] = $this->_fixture;

        $this->_console = new DbSync_Console();
        $this->_console->parse();
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated Console::tearDown()
        parent::tearDown();
    }

    /**
     * Test getProgname()
     *
     */
    public function testGetProgname()
    {
        $this->assertEquals($this->_fixture['0'], $this->_console->getProgname());
    }

    /**
     * Test getArguments()
     *
     */
    public function testGetArguments()
    {
        $this->assertEquals(array_slice($this->_fixture, 1, 3), $this->_console->getArguments());
    }

    /**
     * Test getArgument()
     *
     */
    public function testGetArgument()
    {
        $this->assertEquals($this->_fixture['2'], $this->_console->getArgument(1));
    }

    /**
     * Test getArgument()
     *
     */
    public function testGetArgumentFalse()
    {
        $this->assertFalse($this->_console->getArgument(4));
    }

    /**
     * Test hasArgument()
     *
     */
    public function testHasArgument()
    {
        $this->assertTrue($this->_console->hasArgument('do'));
    }

    /**
     * Test getOptions()
     *
     */
    public function testGetOptions()
    {
        $this->assertCount(3, $this->_console->getOptions());
    }

    /**
     * Test getOption()
     *
     */
    public function testGetOption()
    {
        $this->assertEquals(array_slice($this->_fixture, 8, 3), $this->_console->getOption('args'));
    }

    /**
     * Test getOption()
     *
     */
    public function testGetOptionFalse()
    {
        $this->assertFalse($this->_console->getOption('arg1'));
    }

    /**
     * Test hasOption()
     *
     */
    public function testHasOption()
    {
        $this->assertTrue($this->_console->hasOption('help'));
    }
}

