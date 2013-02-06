#!/bin/sh

CJSDELIVERY_PREFIX='/usr/local'
CJSDELIVERY_CHECKOUT=`dirname $0`

# Stop on first error
set -e

echo "This script will install:"
echo "$CJSDELIVERY_PREFIX/bin/delivery"
echo "$CJSDELIVERY_PREFIX/lib/cjsdelivery"

if [ -d "$CJSDELIVERY_PREFIX/lib/cjsdelivery" ]; then
	echo "Old version found at install path"
	"$CJSDELIVERY_CHECKOUT/uninstall.sh"
fi
mkdir "$CJSDELIVERY_PREFIX/lib/cjsdelivery"

echo "Installing $CJSDELIVERY_PREFIX/lib/cjsdelivery/bin"
cp -R "$CJSDELIVERY_CHECKOUT/bin" "$CJSDELIVERY_PREFIX/lib/cjsdelivery/bin"

echo "Installing $CJSDELIVERY_PREFIX/lib/cjsdelivery/lib"
cp -R "$CJSDELIVERY_CHECKOUT/lib" "$CJSDELIVERY_PREFIX/lib/cjsdelivery/lib"

ln -si "$CJSDELIVERY_PREFIX/lib/cjsdelivery/bin/delivery" "$CJSDELIVERY_PREFIX/bin/delivery"