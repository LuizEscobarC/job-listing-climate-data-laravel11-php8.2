#!/bin/bash

echo "====================================================="
echo "Laravel 11 Test Runner"
echo "====================================================="

if [ -z "$1" ]; then
    echo "Running all tests..."
    docker exec app-estech-api ./vendor/bin/phpunit
else
    echo "Running tests matching '$1'..."
    docker exec app-estech-api ./vendor/bin/phpunit --filter="$1"
fi

EXIT_CODE=$?

echo ""
echo "====================================================="
if [ $EXIT_CODE -eq 0 ]; then
    echo "All tests passed successfully! ✅"
else
    echo "Tests failed with exit code $EXIT_CODE ❌"
    echo "Checking for common issues..."
fi

echo "====================================================="
exit $EXIT_CODE
