VENDOR_BIN_PATH = ./vendor/bin

mkdir:
	mkdir -p build
.PHONY: mkdir

test:
	$(VENDOR_BIN_PATH)/phpunit
.PHONY: test

mutation:
	$(VENDOR_BIN_PATH)/infection
.PHONY: mutation

static:
	$(VENDOR_BIN_PATH)/phpstan analyse
	$(VENDOR_BIN_PATH)/psalm
.PHONY: static

check: mkdir test mutation static
.PHONY: check