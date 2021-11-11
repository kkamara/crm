docker-setup:
	docker-compose build # let's build our services
	docker-compose up -d # get services running

backend-migrate:
	@docker exec octobercms-app php artisan october:migrate

backend-setup:
	@docker exec octobercms-app composer i
	make backend-migrate
	@docker exec octobercms-app php artisan project:set "${OCTOBERCMS_LICENSE}"

clean-js:
	@docker exec octobercms-app bash -c "\
		rm -rf node_modules;\
		rm package-lock.json;\
		npm cache clean --force"

install-js:
	@docker exec octobercms-app php artisan october:util compile assets

rm:
	@docker stop octobercms-app \
		octobercms-db \
		octobercms-nginx
	@docker rm octobercms-app \
		octobercms-db \
		octobercms-nginx

dev:
	make docker-setup
	sleep 120
	make backend-setup
	make install-js
