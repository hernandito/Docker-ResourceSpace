#!/bin/bash

# Check if config exists. If not, copy in the sample config
if [ -f /config/proxy-config.conf ]; then
  echo "Using existing config file."
else
  echo "Creating config from template."
  mv /etc/apache2/000-default.conf /config/proxy-config.conf
 fi

# Check if ResourceSpace exists. If not, copy from /home
if [ -f /web/resourcespace/index.php ]; then
  echo "Using existing ResourceSpace install."
else
  echo "Copying ResourceSpace into Web folder."
  mv /home/resourcespace/ /web/
 fi


