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

namespace Smalot\Smtp\Sendmail;

use phpmailerException;

/**
 * Class PHPMailer
 * @package Smalot\Smtp\Sendmail
 */
class PHPMailer extends \PHPMailer
{
    /**
     * Split the whole message into 2 parts:
     * - headers
     * - body
     *
     * @param string $message
     * @return bool
     */
    public function parseMessage($message)
    {
        if (preg_match('/^(.*?)(\n\n|\r\r|\r\n\r\n)(.*)$/ms', $message, $match)) {
            $this->MIMEHeader = $match[1];
            $this->MIMEBody = $match[3];

            // Parse headers and set values.
            $this->parseHeaders();

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function preSend()
    {
        try {
            // Sign with DKIM if enabled
            if (!empty($this->DKIM_domain)
              && !empty($this->DKIM_selector)
              && (!empty($this->DKIM_private_string)
                || (!empty($this->DKIM_private) && file_exists($this->DKIM_private))
              )
            ) {
                $header_dkim = $this->DKIM_Add(
                  $this->MIMEHeader,
                  $this->encodeHeader($this->secureHeader($this->Subject)),
                  $this->MIMEBody
                );
                $this->MIMEHeader = rtrim($this->MIMEHeader, "\r\n ").self::CRLF.
                  str_replace("\r\n", "\n", $header_dkim).self::CRLF;
            }

            return true;
        } catch (phpmailerException $exc) {
            $this->setError($exc->getMessage());
            if ($this->exceptions) {
                throw $exc;
            }

            return false;
        }
    }

    /**
     * Initialize PHPMailer object with headers.
     */
    protected function parseHeaders()
    {
        if (preg_match_all('/(.*?):\s*(.*?\n(\s.*?\n)*)/', $this->MIMEHeader, $matches)) {
            foreach ($matches[0] as $position => $text) {
                $header = strtolower($matches[1][$position]);
                $value = $matches[2][$position].$matches[3][$position];

                switch ($header) {
                    case 'from':
                        $addresses = $this->parseAddresses($value);
                        $this->setFrom($addresses[0]['address'], $addresses[0]['name']);
                        break;

                    case 'to':
                        foreach ($this->parseAddresses($value) as $address) {
                            $this->addAddress($address['address'], $address['name']);
                        }
                        break;
                }
            }
        }
    }
}
