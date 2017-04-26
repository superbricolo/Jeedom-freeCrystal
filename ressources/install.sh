#!/bin/bash
TEMP_DIR=`mktemp -d /tmp/apr_scan.XXXXXX`
sudo touch /tmp/compilation_freeCrystal_in_progress
sudo echo 0 > /tmp/compilation_freeCrystal_in_progress
echo "*****************************************************************************************************"
echo "*                                    Installation de arp-scan                                       *"
echo "*****************************************************************************************************"
cd $TEMP_DIR
sudo git clone https://github.com/royhills/arp-scan.git
sudo echo 10 > /tmp/compilation_freeCrystal_in_progress
cd arp-scan 
sudo autoreconf --install 
sudo echo 40 > /tmp/compilation_freeCrystal_in_progress
sudo ./configure 
sudo echo 60 > /tmp/compilation_freeCrystal_in_progress
make
sudo echo 80 > /tmp/compilation_freeCrystal_in_progress
sudo make install
sudo echo 90 > /tmp/compilation_freeCrystal_in_progress
sudo rm /tmp/compilation_freeCrystal_in_progress
sudo echo 100 > /tmp/compilation_freeCrystal_in_progress
echo "*****************************************************************************************************"
echo "*                                      Installation Terminé                                         *"
echo "*****************************************************************************************************"
