MIN_PHP=5.4.0

INSTALL_PREFIX=/usr/local
INSTALL_DIR=${INSTALL_PREFIX}/lib/cjsdelivery
INSTALL_BIN=${INSTALL_PREFIX}/bin/delivery

SYS_COMPOSER := $(shell command -v composer 2>/dev/null)
ifdef SYS_COMPOSER
	COMPOSER_CMD := composer
else
	COMPOSER_CMD := php composer.phar
	COMPOSER_DEP := composer.phar
endif

composer.phar:
	@echo "Could not find composer. Downloading."
	@curl "http://getcomposer.org/composer.phar" --progress-bar --output composer.phar

vendor: composer.json $(COMPOSER_DEP)
	$(COMPOSER_CMD) update --no-dev --prefer-dist

vendor/bin:
	$(COMPOSER_CMD) update --dev --prefer-dist

test: vendor/bin src/MattCG/cjsDelivery/*.php tests/src/MattCG/cjsDelivery/*.php
	cd tests; ../vendor/bin/phpunit -c phpunit.xml

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
