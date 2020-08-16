docker-setup:
	docker-compose build # let's build our services
	docker-compose up -d # get services running

backend-install:
	@docker exec crm-app composer i

backend-setup:
	make backend-install
	@docker exec crm-app php artisan key:generate
	@docker exec crm-app php artisan storage:link
	@docker exec crm-app php artisan migrate

make backend-seed:
	@docker exec crm-app php artisan db:seed

clean-js-dep:
	@docker exec crm-app bash -c "\
		rm -rf node_modules;\
		rm package-lock.json;\
		npm cache clean --force"

install-js-dep:
	make clean-js-dep
	@docker exec crm-app npm i
	@docker exec crm-app npm run dev

dev:
	make docker-setup
	sleep 30
	make backend-setup
	make backend-seed
	make install-js-dep