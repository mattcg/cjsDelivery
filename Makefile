MIN_PHP=5.4.0

INSTALL_PREFIX=/usr/local
INSTALL_DIR=${INSTALL_PREFIX}/lib/cjsdelivery
INSTALL_BIN=${INSTALL_PREFIX}/bin/delivery

vendor: composer.json
	@if command -v composer >/dev/null 2>&1; then \
		echo "Found composer. Updating dependencies."; \
		composer update --prefer-dist; \
	else \
		echo "Could not find composer. Downloading."; \
		curl "http://getcomposer.org/composer.phar" --progress-bar --output composer.phar; \
		echo "Download complete. Updating dependencies."; \
		php composer.phar update --prefer-dist; \
	fi;

build/logs/phpunit.xml: vendor src/MattCG/cjsDelivery/*.php
	@cd tests; phpunit -c phpunit.xml

test: build/logs/phpunit.xml

install: vendor uninstall
	@php -r "exit((int)version_compare(PHP_VERSION, '${MIN_PHP}', '<'));"; if [ $$? -eq 1 ]; then \
		echo "Your PHP version is out of date. Please upgrade to at least version ${MIN_PHP}."; \
		exit 1; \
	fi;

	@echo "This script will install:"
	@echo "  - ${INSTALL_BIN}"
	@echo "  - ${INSTALL_DIR}"

	@mkdir "${INSTALL_DIR}"

	@cp -R bin "${INSTALL_DIR}/bin"
	@cp -R src "${INSTALL_DIR}/src"
	@cp -R vendor "${INSTALL_DIR}/vendor"
	@cp cjsDelivery.php "${INSTALL_DIR}/cjsDelivery.php"

	@ln -si "${INSTALL_DIR}/bin/delivery" "${INSTALL_BIN}"
	@echo "Done"

uninstall:
	@if [ -L "${INSTALL_BIN}" ]; then \
		rm -f "${INSTALL_BIN}"; \
	fi;

	@if [ -d "${INSTALL_DIR}" ]; then \
		rm -rf "${INSTALL_DIR}"; \
	fi;

clean:
	rm -rf build vendor composer.lock composer.phar

.PHONY: install uninstall clean test
