install:
	@scripts/install.sh

uninstall:
	@scripts/uninstall.sh

clean:
	@rm -rf build; rm -rf vendor; rm -f composer.lock; rm -f composer.phar

test:
	@pushd tests > /dev/null; phpunit -c phpunit.xml; popd > /dev/null
