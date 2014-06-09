#!/bin/bash
cd $(dirname $(realpath $0))
php build.php
rm -f /tmp/Renderer_*.php