# Panduan Perintah CLI Docker yang Sering Digunakan

Docker adalah platform untuk mengembangkan, mengirim, dan menjalankan aplikasi di dalam container. Berikut adalah daftar perintah Docker yang penting dan sering digunakan:

## Dasar-dasar Docker

-   `docker --version`
    Melihat versi Docker yang terpasang.
-   `docker info`
    Melihat informasi detail tentang instalasi Docker.
-   `docker help`
    Melihat bantuan perintah Docker.

## Image

-   `docker images`
    Melihat daftar image yang tersedia di lokal.
-   `docker pull <nama_image>`
    Mengunduh image dari Docker Hub atau registry lain.
-   `docker build -t <nama_image>:<tag> .`
    Membangun image dari Dockerfile di direktori saat ini.
-   `docker rmi <nama_image>`
    Menghapus image dari lokal.

## Container

-   `docker ps`
    Melihat daftar container yang sedang berjalan.
-   `docker ps -a`
    Melihat semua container (termasuk yang sudah berhenti).
-   `docker run -d -p <host_port>:<container_port> --name <nama_container> <nama_image>`
    Menjalankan container baru secara background.
-   `docker run -it <nama_image> /bin/bash`
    Menjalankan container secara interaktif dengan shell.
-   `docker stop <nama_container>`
    Menghentikan container yang sedang berjalan.
-   `docker start <nama_container>`
    Menjalankan kembali container yang sudah pernah dibuat.
-   `docker restart <nama_container>`
    Merestart container.
-   `docker rm <nama_container>`
    Menghapus container.

## Volume

-   `docker volume ls`
    Melihat daftar volume.
-   `docker volume create <nama_volume>`
    Membuat volume baru.
-   `docker volume rm <nama_volume>`
    Menghapus volume.

## Network

-   `docker network ls`
    Melihat daftar network.
-   `docker network create <nama_network>`
    Membuat network baru.
-   `docker network rm <nama_network>`
    Menghapus network.

## Eksekusi & Log

-   `docker exec -it <nama_container> <perintah>`
    Menjalankan perintah di dalam container yang sedang berjalan.
-   `docker logs <nama_container>`
    Melihat log output dari container.

## Docker Compose

-   `docker-compose up -d`
    Menjalankan semua service yang didefinisikan di file docker-compose.yml secara background.
-   `docker-compose down`
    Menghentikan dan menghapus semua container, network, dan volume yang dibuat oleh compose.
-   `docker-compose logs`
    Melihat log dari semua service.

---

Panduan ini hanya mencakup perintah dasar dan penting. Untuk dokumentasi lebih lengkap, kunjungi [dokumentasi resmi Docker](https://docs.docker.com/).
