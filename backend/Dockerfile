FROM tiangolo/nginx-rtmp

COPY nginx.conf /etc/nginx/nginx.conf

COPY ./html/index.html /www/

RUN apt-get update && apt-get install -y curl

EXPOSE ${RTMP_PORT}
