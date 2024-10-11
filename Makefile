CONTAINER_FPM = php_fpm
CONTAINER_COMPOSER = php_composer
CONTAINER_QUEUE = queue
CONTAINER_SCHEDULER = scheduler
CONTAINER_NGINX = nginx
CONTAINER_PERMISSION = file_fix_permissions


install:
	docker-compose pull
	docker-compose up -d
	@echo "PasswordBroker installed and started"

delete:
	docker-compose down

update:
	@echo "Updating..."
	docker-compose stop $(CONTAINER_NGINX)
	docker-compose stop $(CONTAINER_SCHEDULER)
	docker-compose stop $(CONTAINER_QUEUE)
	docker-compose stop $(CONTAINER_FPM)
	git pull origin $(shell git rev-parse --abbrev-ref HEAD)
	docker-compose restart $(CONTAINER_PERMISSION)
	docker-compose restart $(CONTAINER_COMPOSER)
	docker-compose start $(CONTAINER_FPM)
	docker-compose start $(CONTAINER_QUEUE)
	docker-compose start $(CONTAINER_SCHEDULER)
	docker-compose start $(CONTAINER_NGINX)
	@echo "Update completed & application restated"

stop:
	docker-compose stop

start:
	docker-compose start
