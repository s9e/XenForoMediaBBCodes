#!/bin/sh
git submodule update --remote
$(dirname $(realpath $0))/build.php
git diff
git commit -a && git push