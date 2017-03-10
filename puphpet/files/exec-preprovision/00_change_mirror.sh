#!/bin/sh

sed -i 's/us.archive/it.archive/g' /etc/apt/sources.list

apt-get update
