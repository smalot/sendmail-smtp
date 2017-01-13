# Sendmail SMTP

PHP wrapper to replace default `/usr/sbin/sendmail` binary to support `smtp`.


# Setup

## Download command line tool

First, download the last release of the binary:

````sh
wget https://github.com/smalot/sendmail-smtp/releases/download/v0.1.0/sendmail.phar
````

And enable execution flag

````sh
chmod +x sendmail.phar
````

Check if it works

````sh
./sendmail.phar --help
````

## Update `php.ini`

Edit the `cli`, `apache` or both `php.ini` files.

````ini
# Set the full path to the phar command line tool.
sendmail_path = "/path/to/phar/sendmail.phar"
````

The [`sendmail_path`](http://php.net/manual/en/ini.list.php) is a **[PHP_INI_SYSTEM](http://php.net/manual/en/configuration.changes.modes.php)** `changeable` type which means you can only change it in the `php.ini` file or in the `httpd.conf` file.



# Full config file

File to store config : `/etc/sendmail-smtp.yml`

````yaml
# SMTP hosts.
# Either a single hostname or multiple semicolon-delimited hostnames.
# You can also specify a different port
# for each host by using this format: [hostname:port]
# (e.g. "smtp1.example.com:25;smtp2.example.com").
# You can also specify encryption type, for example:
# (e.g. "tls://smtp1.example.com:587;ssl://smtp2.example.com:465").
# Hosts will be tried in order.
host: 127.0.0.1

# The default SMTP server port.
port: 25

# Whether to use SMTP authentication.
# Uses the Username and Password properties.
auth: false

# SMTP username.
username: ~

# SMTP password.
password: ~

# SMTP auth type.
# Options are CRAM-MD5, LOGIN, PLAIN, NTLM, XOAUTH2, attempted in that order if not specified.
auth_type: ~

# What kind of encryption to use on the SMTP connection.
# Options: '', 'ssl' or 'tls'
secure: ~

# Whether to enable TLS encryption automatically if a server supports it,
# even if `secure` is not set to 'tls'.
# Be aware that in PHP >= 5.6 this requires that the server's certificates are valid.
auto_tls: ~

# SMTP realm.
# Used for NTLM auth
realm: ~

# SMTP workstation.
# Used for NTLM auth
workstation: ~

# The SMTP server timeout in seconds.
# Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2
timeout: 300

# Options array passed to stream_context_create when connecting via SMTP.
# @see http://nl1.php.net/manual/en/function.stream-context-create.php
options: ~

# SMTP class debug output mode.
# Debug output level.
# Options:
# * `0` No output
# * `1` Commands
# * `2` Data and commands
# * `3` As 2 plus connection status
# * `4` Low-level data output
debug: 0
````


# Sample config files

## Gmail

````yaml
host: smtp.gmail.com
port: 587
username: mail@example.tld
password: xxxxxxxxxx
auth: true
````

## 1and1

````yaml
host: smtp.1und1.de
port: 587
username: mail@example.tld
password: xxxxxxxxxx
auth: true
````

## SMTP Server Docker

https://github.com/smalot/smtp-server-docker

````yaml
host: 127.0.0.1
port: 8025
auth: false
````
