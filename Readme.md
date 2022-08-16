# Timetracker

## Description
- The task is to make a simple time tracker. The user should be able to:
- Type the name of the task he is working on and click “start”.
- See the timer that is counting how long the task is already taking.
- Click Stop to stop working on that task (the timer stops).
- Type another name for a different task and click “start” again. The page should start
counting from the beginning.
- On the same page (or other, up to you) user should be able to see the summary of the
time tracker where it displays how much time I spent on which task, and how much time
I was working today.

## Requeriments
- Place all the code in Github or Bitbucket
- Store it in a Docker container.
- Feel free to use your favourite PHP framework, we are looking for a professional that
can do a smart utilization of developing tools. Always keep in mind the SOLID principles.
- The data should be stored in any relational database you wish.
- The tasks can be recognized by name, so if I type “homepage development” twice
during one day, spend 2h in the morning and 0.5h in the afternoon, then at the end of
the day I should see 2.5h near “homepage development”.
- Don’t forget the README.md

## Installation

1. 😀 Clone this rep.

2. Create the file `./.docker/.env.nginx.local` using `./.docker/.env.nginx` as template. The value of the variable `NGINX_BACKEND_DOMAIN` is the `server_name` used in NGINX. Also create the `.env` file from `env.dist` and the `.docker/.env` from `.docker/.env.dist`. As a test app you can use the predefined variables and passwords.

3. Go inside folder `./docker` and run `docker-sync-stack start` to start containers.

4. You should work inside the `php` container. This project is configured to work with [Remote Container](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers) extension for Visual Studio Code, so you could run `Reopen in container` command after open the project.

5. Inside the `php` container, run `composer install` to install dependencies from `/var/www/symfony` folder.

6. Use the following value for the DATABASE_URL environment variable:

```
DATABASE_URL=mysql://app_user:helloworld@db:3306/app_db?serverVersion=8.0.23
```
7. Inside the `php` container, run `php bin/console doctrine:migrations:migrate` to create the database schema.

You could change the name, user and password of the database in the `env` file at the root of the project.

## Console

You can use the web application via broswer or via console. The commands for the console are:
- `php bin/console app:task start taskname` for starting a new task.
- `php bin/console app:task end taskname` for ending a task.
- `php bin/console app:task list` for listing info about all the tasks.


## Credits
Created using this BoilerPlate: [Docker + PHP 8.1 + MySQL + Nginx + Symfony 6.1 Boilerplate](https://github.com/ger86/symfony-docker)