#!/bin/sh
cd $(dirname $(dirname $(realpath $0)))
composer up s9e/text-formatter
vendor/bin/phpunit --no-coverage || exit
rm -f releases/*-dev*
php scripts/build.php "$@"
php scripts/generateTagsPage.php
git diff
git commit -a                                          && \
git push                                               && \
cp www/tags.html ../s9e.github.io/XenForoMediaBBCodes/ && \
cd ../s9e.github.io/XenForoMediaBBCodes/               && \
git commit -a -m"Updated XenForoMediaBBCodes"          && \
git push