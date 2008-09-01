#!/bin/bash -x

if [[ -n ~/.bbep/BeatExtractor.kmdr.pid ]]; then $(dcop `cat ~/.bbep/BeatExtractor.kmdr.pid` KommanderIf setText "$1" "$2"); fi
