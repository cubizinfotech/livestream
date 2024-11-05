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
		docker restart 81f318081fa8 >>> (e8c99fd6f6ce is container name)
		docker update --restart=always 5edfd400d024
		docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' rtmp_server
