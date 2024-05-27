#!/bin/bash

compile()
{
    local version="$1"
    
    echo "$version" > home/build
    sh -c '2>/dev/null git symbolic-ref --short HEAD' > home/buildinfo
    bin/build && box compile
}

compile "$1"
