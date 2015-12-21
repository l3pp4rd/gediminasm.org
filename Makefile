.PHONY: run release

run:
	hugo server --theme=hyde-x --buildDrafts --watch

release:
	tar --exclude-from=.tarignore --exclude-vcs -czf ansible/app.tar.gz .


deps:
	@command -v curl >/dev/null 2>&1 || (echo "curl is not available in \$PATH"; exit 1)
	@command -v tar >/dev/null 2>&1 || (echo "tar is not available in \$PATH"; exit 1)
	@command -v hugo >/dev/null 2>&1 || (echo "hugo is not available in \$PATH"; exit 1)
	$(call theme,zyro/hyde-x,c3fa78c)

# download theme at specific commit from github
define theme =
$(eval _LOCATION := themes/$(shell cut -d '/' -f 2- <<< $(1)))
mkdir -p $(_LOCATION)
curl -L https://github.com/$(1)/tarball/$(2) | tar -C $(_LOCATION) -zx --strip-components 1
endef

