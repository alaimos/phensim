#!/bin/sh

#
# Ubuntu Equip 
#  Equip basec/core libs
# Licence: MIT

export DEBIAN_FRONTEND=noninteractive
sudo apt-get update -y  

#sudo apt-get dist-upgrade -y  

sudo apt-get install build-essential rsync telnet screen man wget -y  
sudo apt-get install strace tcpdump -y  
sudo apt-get install libssl-dev zlib1g-dev libcurl3-dev libxslt-dev -y  
sudo apt-get install software-properties-common python-software-properties -y  

sudo apt-get install git -y  
