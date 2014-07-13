#!/bin/bash
cd $(dirname $(realpath $0))
php build.php $1
rm -f /tmp/Renderer_*.php