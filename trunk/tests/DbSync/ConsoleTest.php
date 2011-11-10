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

require_once dirname(__FILE__) . '/_files/Console.php';

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
     * @var Stub_Console
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

        $this->_console = new Stub_Console();
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
    public function test_getProgname()
    {
        $this->assertEquals($this->_fixture['0'], $this->_console->getProgname());
    }

    /**
     * Test setProgname()
     *
     * @depends test_getProgname
     */
    public function test_setProgname()
    {
        $progname = 'man';
        $this->_console->setProgname($progname);

        $this->assertEquals($progname, $this->_console->getProgname());
    }

    /**
     * Test getArguments()
     *
     */
    public function test_getArguments()
    {
        $this->assertEquals(array_slice($this->_fixture, 1, 3), $this->_console->getArguments());
    }

    /**
     * Test setArguments()
     *
     * @depends test_getArguments
     */
    public function test_setArguments()
    {
        $arguments = array('car', 'toy', 'man');
        $this->_console->setArguments($arguments);

        $this->assertEquals($arguments, $this->_console->getArguments());
    }

    /**
     * Test getArgument()
     *
     */
    public function test_getArgument()
    {
        $this->assertEquals($this->_fixture['2'], $this->_console->getArgument(1));
    }

    /**
     * Test getArgument()
     *
     */
    public function test_getArgument_false()
    {
        $this->assertFalse($this->_console->getArgument(4));
    }

    /**
     * Test hasArgument()
     *
     */
    public function test_hasArgument()
    {
        $this->assertTrue($this->_console->hasArgument('do'));
    }

    /**
     * Test getOptions()
     *
     */
    public function test_getOptions()
    {
        $this->assertCount(3, $this->_console->getOptions());
    }

    /**
     * Test getOption()
     *
     */
    public function test_getOption()
    {
        $this->assertEquals(array_slice($this->_fixture, 8, 3), $this->_console->getOption('args'));
    }

    /**
     * Test getOption()
     *
     */
    public function test_getOption_false()
    {
        $this->assertFalse($this->_console->getOption('arg1'));
    }

    /**
     * Test hasOption()
     *
     */
    public function test_hasOption()
    {
        $this->assertTrue($this->_console->hasOption('help'));
    }

    /**
     * Test getStdParam()
     *
     */
    public function test_getStdParam()
    {
        $text = 'some text';
        $stdin = vfsStream::url('exampleDir') . '/std';
        vfsStream::setup('exampleDir');

        file_put_contents($stdin, $text);

        $fp = fopen($stdin, 'r');
        $this->_console->setStdin($fp);

        $this->assertEquals($text, $this->_console->getStdParam());
    }

    /**
     * Test getStdParam()
     *
     */
    public function test_getStdParam_default()
    {
        $default = 'some text';
        $stdin = vfsStream::url('exampleDir') . '/std';
        vfsStream::setup('exampleDir');

        file_put_contents($stdin, '');

        $fp = fopen($stdin, 'r');
        $this->_console->setStdin($fp);

        $this->assertEquals($default, $this->_console->getStdParam($default));
    }


    /**
     * Test colorize()
     *
     */
    public function test_colorize()
    {
        $text = 'some text';
        $colors = array(
            'red'    => "\033[1;31m" . $text . "\033[m",
            'green'  => "\033[1;32m" . $text . "\033[m",
            'blue'   => "\033[1;34m" . $text . "\033[m",
            'white'  => "\033[1;37m" . $text . "\033[m",
            'yellow' => "\033[1;33m" . $text . "\033[m",
        );

        foreach ($colors as $color => $coloredText) {
            $this->assertEquals($coloredText, $this->_console->colorize($text, $color));
        }
    }
}

