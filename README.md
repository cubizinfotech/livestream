FilePathIn:
    cd /var/www/html/livestream/frontend
	cd /var/www/html/livestream/backend
  
FilePathOut:
		cd ../../../..

SudoCommand:
	UpdateCommand:
		apt-get update

	CreateFileCommand:
		apt install nano

	OpenFileCommand:
		nano /etc/nginx/nginx.conf

	FilePermission:
		sudo chmod 777 /var/www/html/index.php
		
	FolderPermission:
		sudo chmod -R 777 /var/www/html
		sudo chown -R www-data:www-data /var/www/html

	RemoveFile:
		sudo rm -r /var/www/html/livestream.zip

	RemoveFolder:
		sudo rm -r /var/www/html/audio_copyright

	UnZipFile:
		sudo unzip /path/to/your/file.zip -d /path/to/destination
		SamePath:
			sudo unzip /path/to/your/file.zip
		
Command:
	netstat
	hostname -I
	sudo netstat -tuln | grep LISTEN
	sudo netstat -tuln | grep 4138 >>> testing
	sudo ufw allow 4138 >>> add port
	sudo ufw delete allow 4138 >>> delete port
	sudo ufw reload

DockerCommand:
		docker info
		docker build -t shared-rtmp-image . >>> create image and used multiple RTMP server
		sudo usermod -aG docker ubuntu >>> add username with docker
		sudo usermod -aG docker www-data >>> add password with docker
		docker login
		docker-compose build
		docker-compose up
			NOTE: One line command
				docker-compose up -d --build
		docker ps
		docker container ls
		docker-compose down >>> use for delete container
		docker rm -f C1-Test >>> remove container "C1-Test" container name
		docker exec -it 81f318081fa8 bash
		docker stop ed488a0c9eba >>> remove/stop
		docker restart 8ee8e418ae96 >>> (ed488a0c9eba is container name)
		docker update --restart=always ed488a0c9eba
		docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' rtmp_server

		Get Container ID:
			docker ps -q -f "name=rtmpServer"

OBS - multiple output:
	https://www.youtube.com/watch?v=mZnhn_pxhGg&ab_channel=TechnoShyam

When Run Docker command through file (php file):
	CMD: sudo visudo
	Add Line: www-data ALL=(ALL) NOPASSWD: /usr/bin/docker, /usr/local/bin/docker-compose
	Then: ctrl + x
	Then: Y
	Then: Press enter button
