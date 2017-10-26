#!/bin/bash
set -e
set -x

vendor/bin/phpcs --standard=phpcs-ruleset.xml -p
vendor/bin/phpunit
