#!/usr/bin/env php
<?php

/**
 * MIT License
 *
 * Copyright (C) 2016 - Sebastien Malot <sebastien@malot.fr>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

include __DIR__.'/../vendor/autoload.php';

use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Smalot\Smtp\Sendmail\Commands\SendCommand;
use Smalot\Smtp\Sendmail\Commands\UpdateCommand;
use Symfony\Component\Console\Application;

$handler = new SyslogHandler('sendmail');
$logger = new Logger('sendmail', [$handler]);
$command = new SendCommand($logger);

$application = new Application('sendmail-smtp', '@git-version@');
$application->add($command);
$application->add(new UpdateCommand());
$application->setDefaultCommand('send');
$application->run();
