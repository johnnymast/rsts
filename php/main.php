<?php
/*
 * This file is part of RSTS
 *
 * (c) Johnny Mast <mastjohnny@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
define('TESTS_DIR', realpath(__DIR__ . '.\tests'));

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/app/FileIO.php');

use App\TestCase;
use Symfony\Component\Yaml\Yaml;
use function App\getTests;

$tests = getTests(TESTS_DIR);
sort($tests);

/**
 * For all files in the tests directory
 * run its tests.
 */
foreach ($tests as $filename) {
    $files = Yaml::parseFile($filename);
    foreach ($files as $name => $cases) {
        $test = new TestCase(basename($filename), $name, $cases);
        $test->run();
    }
}