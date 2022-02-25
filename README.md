# PHP Redis SSE Example

This is a simple example of an SSE subscription endpoint using PHP and Redis. The example scenario is a *very* simple chat client.

Notable features include:
+ The PHP script doesn't "spin" and instead uses Redis to listen for new messages.

## Installation

1. clone this repo
2. `composer install`
3. `sudo apt install php-redis` or [build from source](https://github.com/phpredis/phpredis/blob/develop/INSTALL.markdown)
4. restart apache or php-fpm or whatever you use
