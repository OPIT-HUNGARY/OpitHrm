#!/bin/bash

scripts_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/"

${scripts_dir}../../../../../app/console doctrine:database:drop --quiet --force -e test
${scripts_dir}../../../../../app/console doctrine:database:create -e test
${scripts_dir}../../../../../app/console doctrine:schema:create --no-interaction -e test
${scripts_dir}../../../../../app/console doctrine:fixtures:load --fixtures=/${scripts_dir}../DataFixtures/ORM/ --fixtures=/${scripts_dir}../../UserBundle/DataFixtures/ORM/ --no-interaction -e test