#!/bin/sh
cd $(dirname $(dirname $(realpath $0)))
git submodule update --remote
phpunit || exit
rm -f releases/*-dev*
scripts/build.php "$@"
git diff
git commit -a                                               && \
git push                                                    && \
cp www/configure.html ../s9e.github.io/XenForoMediaBBCodes/ && \
cd ../s9e.github.io/XenForoMediaBBCodes/                    && \
git commit -a -m"Updated XenForoMediaBBCodes"               && \
git push