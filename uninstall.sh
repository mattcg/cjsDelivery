#!/bin/sh

CJSDELIVERY_PREFIX='/usr/local'

echo "Uninstalling:"
echo "$CJSDELIVERY_PREFIX/bin/delivery"
echo "$CJSDELIVERY_PREFIX/lib/cjsdelivery"

if [ -d "$CJSDELIVERY_PREFIX/lib/cjsdelivery" ]; then
	rm -rf "$CJSDELIVERY_PREFIX/lib/cjsdelivery"
fi

if [ -L "$CJSDELIVERY_PREFIX/bin/delivery" ]; then
	rm -f "$CJSDELIVERY_PREFIX/bin/delivery"
fi