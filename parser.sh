#!/bin/bash

declare -a languages=("de" "en" "es" "fr" "ja" "pt_BR" "ru" "tr" "zh")

## now loop through the above array
for lang in "${languages[@]}"
do
  echo "Process $lang"
  mkdir -p "input/php_manual_$lang"
  tar -xf "input/php_manual_$lang.tar.gz" --directory "input/php_manual_$lang"
  php parser.php --include-examples "input/php_manual_$lang/php-chunked-xhtml" "output/$lang"
  rm "input/php_manual_$lang.tar.gz"
  rm -rf "input/php_manual_$lang"
done
