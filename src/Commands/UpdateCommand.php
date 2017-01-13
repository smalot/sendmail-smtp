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

use GuzzleHttp\Client;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCommand
 * @package Smalot\Smtp\Sendmail\Commands
 */
class UpdateCommand extends Command
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @inheritDoc
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->httpClient = new Client();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
          ->setName('self-update')
          ->addOption('check-only', null, InputOption::VALUE_NONE, '')
          ->setDescription('Updates Carbon14 to the latest version');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currentVersion = $this->getApplication()->getVersion();

        $updater = new Updater(null, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setPackageName('smalot/sendmail-smtp');
        $updater->getStrategy()->setPharName('sendmail.phar');
        $updater->getStrategy()->setCurrentLocalVersion($currentVersion);
        $updater->getStrategy()->setStability('stable');

        try {
            if ($input->getOption('check-only')) {
                $result = $updater->hasUpdate();

                if ($result) {
                    $output->writeln(
                      sprintf(
                        '<comment>The current stable build available remotely is %s.</comment>',
                        $updater->getNewVersion()
                      )
                    );
                } elseif (false === $updater->getNewVersion()) {
                    $output->writeln('<error>There are no stable builds available.</error>');
                } else {
                    $output->writeln(
                      sprintf(
                        '<info>You are already using composer version %s.</info>',
                        $updater->getNewVersion()
                      )
                    );
                }

                return;
            } else {
                $result = $updater->update();

                if ($result) {
                    $new = $updater->getNewVersion();
                    $old = $updater->getOldVersion();

                    $output->writeln(
                      sprintf(
                        '<info>Successfully updated from %s to %s.</info>',
                        $old,
                        $new
                      )
                    );

                    exit(0);
                } else {
                    $output->writeln(
                      sprintf('<info>You are already using composer version %s.</info>', $updater->getNewVersion())
                    );
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Oops, something wrong happened!</error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');
            exit(1);
        }
    }
}
