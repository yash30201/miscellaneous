#!/bin/bash

if [[ $# -eq 0 ]] ; then
    echo 'Please also enter the number of clients to spin'
    exit 0
fi

NUMBER_OF_CLIENTS=$1

for i in $(seq 1 $NUMBER_OF_CLIENTS);
    do (nohup php publisher.php &);
done

php publisher.php
