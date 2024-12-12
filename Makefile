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
	@read -p "Do you want to make a backup before update? [y/n] > " ans && ans=$${ans:-N} ; \
	if [ $${ans} = y ] || [ $${ans} = Y ]; then \
	    docker-compose exec $(CONTAINER_FPM) php ./artisan system:createBackup ; \
	else \
    	echo "Backup skipped..." ; \
	fi
	@echo "Updating..."
	docker-compose down
	git pull
	docker-compose up -d
	docker-compose exec $(CONTAINER_FPM) php ./artisan migrate --force
	@echo "Update completed & application restated"

stop:
	docker-compose stop

start:
	docker-compose start

down:
	docker-compose down

backup:
	docker-compose exec $(CONTAINER_FPM) php ./artisan system:createBackup

recoveryFromBackup:
	docker-compose exec $(CONTAINER_FPM) php ./artisan system:recoveryFromBackup

inviteUser:
	docker-compose exec $(CONTAINER_FPM) php ./artisan identity:addInviteLink

addUser:
	docker-compose exec $(CONTAINER_FPM) php ./artisan identity:addUser

changeUserEmail:
	docker-compose exec $(CONTAINER_FPM) php ./artisan identity:changeEmail

changeUserPassword:
	docker-compose exec $(CONTAINER_FPM) php ./artisan identity:changePassword

