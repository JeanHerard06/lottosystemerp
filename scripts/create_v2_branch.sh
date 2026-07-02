#!/usr/bin/env bash
set -e

git checkout develop/v2.0 2>/dev/null || git checkout -b develop/v2.0
git checkout -b feature/lottery-engine-v2
