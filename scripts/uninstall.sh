#!/bin/sh

INSTALL_PREFIX="/usr/local"
INSTALL_DIR="$INSTALL_PREFIX/lib/cjsdelivery"
INSTALL_BIN="$INSTALL_PREFIX/bin/delivery"

echo "Removing:"
echo "\t$INSTALL_BIN"
echo "\t$INSTALL_DIR"

if [ -L "$INSTALL_BIN" ]; then
	rm -f "$INSTALL_BIN"
fi

if [ -d "$INSTALL_DIR" ]; then
	rm -rf "$INSTALL_DIR"
fi

echo "Uninstall done."
