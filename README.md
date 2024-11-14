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
		sudo rm -r /var/www/html/live_stream.zip

	RemoveFolder:
		sudo rm -r /var/www/html/audio_copyright

	UnZipFile:
		sudo unzip /path/to/your/file.zip -d /path/to/destination
		SamePath:
			sudo unzip /path/to/your/file.zip
		
Command:
	netstat
	hostname -I

DockerCommand:
		docker build -t shared-rtmp-image . >>> create image and used multiple RTMP server
		docker login
		docker-compose build
		docker-compose up
			NOTE: One line command
				docker-compose up -d --build
		docker ps
		docker container ls
		docker-compose down >>> use for delete container
		docker exec -it 81f318081fa8 bash
		docker stop e8c99fd6f6ce >>> remove/stop
		exit
		docker restart 8ee8e418ae96 >>> (e8c99fd6f6ce is container name)
		docker update --restart=always 8ee8e418ae96
		docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' rtmp_server

		Get Container ID:
			docker ps -q -f "name=rtmpServer"

OBS - multiple output:
	https://www.youtube.com/watch?v=mZnhn_pxhGg&ab_channel=TechnoShyam
