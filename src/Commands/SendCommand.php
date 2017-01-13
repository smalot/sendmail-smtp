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

namespace Smalot\Smtp\Sendmail\Commands;

use Psr\Log\LoggerInterface;
use Smalot\Smtp\Sendmail\PHPMailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Sendmail
 * @package Smalot\Smtp\Sendmail\Commands
 */
class SendCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SendCommand constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct();

        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
          ->setName('send')
          ->addOption('config-file', 'f', InputOption::VALUE_REQUIRED, 'Config file', '/etc/sendmail-smtp.yml')
          ->setDescription('Send mail via SMTP.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $filename = $input->getOption('config-file');

            if (file_exists($filename)) {
                $config = Yaml::parse(file_get_contents($filename));
            } else {
                $config = [];
            }

            $phpMailer = $this->getPHPMailer($config);
            $phpMailer->parseMessage($this->getInputContent());

            if (!$phpMailer->send()) {
                return 2;
            }
        } catch (\Exception $e) {
            return 1;
        }

        return 0;
    }

    /**
     * @param array $config
     * @return PHPMailer
     */
    protected function getPHPMailer($config)
    {
        $phpMailer = new PHPMailer();
        $phpMailer->isSMTP();

        // Set default values.
        $phpMailer->Host = 'localhost';
        $phpMailer->Port = 25;

        // Apply config.
        foreach ($config as $key => $value) {
            if ($value === null) {
                continue;
            }

            switch ($key) {
                case 'debug':
                    $phpMailer->SMTPDebug = boolval($value);
                    break;

                case 'debug_output':
                    $phpMailer->Debugoutput = $value;
                    break;

                case 'host':
                    $phpMailer->Host = $value;
                    break;

                case 'port':
                    $phpMailer->Port = $value;
                    break;

                case 'username':
                    $phpMailer->Username = $value;
                    break;

                case 'password':
                    $phpMailer->Password = $value;
                    break;

                case 'timeout':
                    $phpMailer->Timeout = $value;
                    break;

                case 'secure':
                    $phpMailer->SMTPSecure = $value;
                    break;

                case 'auto_tls':
                    $phpMailer->SMTPAutoTLS = $value;
                    break;

                case 'auth_type':
                    $phpMailer->AuthType = $value;
                    break;

                case 'realm':
                    $phpMailer->Realm = $value;
                    break;

                case 'workstation':
                    $phpMailer->Workstation = $value;
                    break;

                case 'options':
                    $phpMailer->SMTPOptions = $value;
                    break;
            }
        }

        return $phpMailer;
    }

    /**
     * @return string
     */
    protected function getInputContent()
    {
        $message = '';

        if (0 === ftell(STDIN)) {
            while (!feof(STDIN)) {
                $message .= fread(STDIN, 1024);
            }
        }

        return $message;
    }
}
