<?php
/*
 * This file is part of RSTS
 *
 * (c) Johnny Mast <mastjohnny@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;
use Axiom\Rivescript\Rivescript;
use AssertionError;

/**
 * TestCase trait
 *
 * The TestCase class will test a single file
 * with tests.
 *
 * PHP version 7.4 and higher.
 *
 * @category Core
 * @package  Tests
 * @author   Johnny Mast <mastjohnny@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/axiom-labs/rivescript-php
 * @since    0.1.0
 */
class TestCase
{

    /**
     * @var string
     */
    protected string $file;

    /**
     * @var string
     */
    protected string $name;

    /**
     * Instance of the rivescript interpreter.
     *
     * @var \Axiom\Rivescript\Rivescript
     */
    protected Rivescript $rs;

    /**
     * The test username.
     *
     * @var string
     */
    protected string $username = "local-user";

    /**
     * Stores the cases for this test.
     *
     * @var mixed
     */
    private array $cases;

    /**
     * TestCase constructor.
     *
     * @param string $file  The test file to load.
     * @param string $name  The name of the test.
     * @param array  $cases The testcases to run.
     */
    public function __construct(string $file, string $name, array $cases)
    {
        $this->rs = new Rivescript();
        $this->file = $file;
        $this->name = $name;
        $this->cases = $cases['tests'];
    }

    /**
     * @param string $source
     *
     * @return void
     */
    private function source(string $source): void
    {
        $this->rs->stream($source);
    }

    /**
     * Input tests an input string against the interpreter.
     *
     * @param array $step The input step to test.
     *
     * @return void
     */
    private function input(array $step): void
    {

        $reply = $this->rs->reply($step['input']);
        $expected = $step['reply'];

        if (is_string($expected)) {
            if ($reply !== $expected) {
                throw new AssertionError(
                    "Got unexpected exception from reply() for input: {$step['input']}\n" .
                    "Expected: {$expected}\n" .
                    "Got: {$reply}");
            }
        } elseif (is_array($expected) === true) {
            $correct = 0;
            foreach ($expected as $item) {
                if ($reply === $item) {
                    $correct++;
                }
            }

            if ($correct === 0) {
                $expected = implode(' or ', $expected);
                throw new AssertionError(
                    "Got unexpected exception from reply() for input: {$step['input']}\n" .
                    "Expected: {$expected}\n" .
                    "Got: {$reply}");

            }
        }
    }

    /**
     * Ser a user variable.
     *
     * @param array $vars The variables to set.
     *
     * @return void
     */
    public function set(array $vars): void
    {
        if (count($vars) > 0) {
            foreach ($vars as $key => $value) {
                $this->rs->setUservar($this->username, $key, $value);
            }
        }
    }

    /**
     * Assert the value of a user variable.
     *
     * @param array $vars The variables to test.
     *
     * @return void
     */
    private function assert(array $vars): void
    {
        foreach ($vars as $key => $value) {
            $expected = $value;
            $actual = $this->rs->getUservar($this->username, $key);
            if ($actual !== $value) {
                throw new AssertionError(
                    "Failed to assert the the value of user variable: {$key}\n" .
                    "Expected: {$expected}\n" .
                    "Got: {$actual}");
            }
        }
    }

    /**
     * @return void
     */
    public function run(): void
    {
        try {

            $errors = false;

            foreach ($this->cases as $step) {

                $key = key($step);

                switch ($key) {
                    case "assert":
                        $this->assert($step[$key]);
                        break;
                    case "source":
                        $this->source($step[$key]);
                        break;

                    case "input":
                        $this->input($step);
                        break;

                    case "set":
                        $this->set($step[$key]);
                        break;

                    default:
                        throw new AssertionError("Unsupported test step called \"{$key}\"");
                }
            }

        } catch (AssertionError $e) {
            $errors = true;
            $this->fail($e);
        }

        $sym = ($errors === true) ? "x" : "✓";
        echo "{$sym}  {$this->file}#{$this->name}\n";
    }

    /**
     * Show a failure message.
     *
     * @param \AssertionError $e
     *
     * @return void
     */
    private function fail(AssertionError $e): void
    {
        $banner = "Failed: {$this->file}#{$this->name}";
        $banner .= "\n" . str_repeat("=", strlen($banner)) . "\n";

        echo "{$banner}{$e->getMessage()} \n\n";
    }
}