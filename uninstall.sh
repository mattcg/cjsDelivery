#!/bin/sh

CJSDELIVERY_PREFIX="/usr/local"
CJSDELIVERY_INSTALL="$CJSDELIVERY_PREFIX/lib/cjsdelivery"
CJSDELIVERY_BIN="$CJSDELIVERY_PREFIX/bin/delivery"

echo "Removing:"

echo "$CJSDELIVERY_INSTALL"
if [ -d "$CJSDELIVERY_INSTALL" ]; then
	rm -rf "$CJSDELIVERY_INSTALL"
fi

echo "$CJSDELIVERY_BIN"
if [ -L "$CJSDELIVERY_BIN" ]; then
	rm -f "$CJSDELIVERY_BIN"
fi

echo "Uninstall done."
