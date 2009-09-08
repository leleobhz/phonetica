#!/bin/bash -x

if [[ -e "$(pwd)/BeatExtractor.kmdr.pid" ]]; then
    $(dcop `cat ~/.bbep/BeatExtractor.kmdr.pid` KommanderIf setText "$1" "$2")
fi
