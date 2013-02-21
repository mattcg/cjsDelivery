#!/bin/sh

CJSDELIVERY_PREFIX="/usr/local"
CJSDELIVERY_INSTALL="$CJSDELIVERY_PREFIX/lib/cjsdelivery"
CJSDELIVERY_CHECKOUT=`dirname $0`

# Stop on first error
set -e

echo "This script will install:"
echo "$CJSDELIVERY_PREFIX/bin/delivery"
echo "$CJSDELIVERY_INSTALL"

if [ -d "$CJSDELIVERY_INSTALL" ]; then
	echo "Old version found at install path."
	"$CJSDELIVERY_CHECKOUT/uninstall.sh"
fi
mkdir "$CJSDELIVERY_INSTALL"

echo "Copying:"

echo "$CJSDELIVERY_INSTALL/bin"
cp -R "$CJSDELIVERY_CHECKOUT/bin" "$CJSDELIVERY_INSTALL/bin"

echo "$CJSDELIVERY_INSTALL/lib"
cp -R "$CJSDELIVERY_CHECKOUT/lib" "$CJSDELIVERY_INSTALL/lib"

echo "Linking:"

echo "$CJSDELIVERY_PREFIX/bin/delivery => $CJSDELIVERY_INSTALL/bin/delivery"
ln -si "$CJSDELIVERY_INSTALL/bin/delivery" "$CJSDELIVERY_PREFIX/bin/delivery"

echo "Install done."
