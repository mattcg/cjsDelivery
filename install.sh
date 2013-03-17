#!/bin/sh

INSTALL_PREFIX="/usr/local"
INSTALL_DIR="$INSTALL_PREFIX/lib/cjsdelivery"
INSTALL_BIN="$INSTALL_PREFIX/bin/delivery"

# Stop on first error
set -e

# Check whether PHP is up to date
if php -r "exit((int)version_compare(PHP_VERSION, '5.4.0', '>='));"; then
	echo "Your PHP version is out of date. Please upgrade to at least version 5.4.0."
	exit 1
fi

# Get the real path to the checkout directory
pushd `dirname $0` > /dev/null
CHECKOUT_DIR=`pwd`
popd > /dev/null

# Install dependencies using composer
if command -v composer >/dev/null 2>&1; then
	echo "Found composer. Updating dependencies."
	composer update --prefer-dist --working-dir "$CHECKOUT_DIR" --quiet
else
	echo "Could not find composer. Downloading."
	curl "http://getcomposer.org/composer.phar" --silent --output "$CHECKOUT_DIR/composer.phar"
	echo "Download complete. Updating dependencies."
	php "$CHECKOUT_DIR/composer.phar" -- update --prefer-dist --working-dir "$CHECKOUT_DIR" --quiet
fi

echo "This script will install:"
echo "$INSTALL_BIN"
echo "$INSTALL_DIR"

# Uninstall any old version
if [ -d "$INSTALL_DIR" ]; then
	echo "Old version found at install path."
	"$CHECKOUT_DIR/uninstall.sh"
fi
mkdir "$INSTALL_DIR"

# Install the necessary parts of the checkout to /usr/local/lib
echo "Installing: $INSTALL_DIR"
cp -R "$CHECKOUT_DIR/bin" "$INSTALL_DIR/bin"
cp -R "$CHECKOUT_DIR/src" "$INSTALL_DIR/src"
cp -R "$CHECKOUT_DIR/vendor" "$INSTALL_DIR/vendor"
cp "$CHECKOUT_DIR/cjsDelivery.php" "$INSTALL_DIR/cjsDelivery.php"
cp "$CHECKOUT_DIR/uninstall.sh" "$INSTALL_DIR/uninstall.sh"

# Link to the delivery binary from /usr/local/bin
echo "Linking: $INSTALL_BIN => $INSTALL_DIR/bin/delivery"
ln -si "$INSTALL_DIR/bin/delivery" "$INSTALL_BIN"

echo "Install done."
