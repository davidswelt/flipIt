#!/bin/bash

echo "Set HIT ID in config/config.inc.php"

php ../get_results.php >data.csv

./preProcessData.py data.csv

php approveAndGrant.php

echo "write 1 into realrun.txt if you'd like to make payments for real."
