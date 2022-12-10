#!/bin/sh

for FNAME in $*
do
   python3 validate_json.py $FNAME
done