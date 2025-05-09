#!/usr/bin/env bash
VERSION=$(jq -r '.version' < plugins/theme-canary.json)
NAME=myaac-theme-canary-v$VERSION.zip
rm -f $NAME
zip -r $NAME plugins/ -x */\.*
