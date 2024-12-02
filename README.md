SSO Keycloak Concept
=====================

Aplikasi dan datbase di jalankan dengan xampp

## Pre Install
Dibutuhkan docker engine untuk menjalankan aplikasi docker
jika ingin menjalakan selain menggunakan docker silahkan cek 
https://www.keycloak.org/guides
dijalankan dimanapun pastikan port yang digunakan.

## Perintah yang digunakan

```bash
# memulai docker pastikan di directory yang sama denagn docker_compose.yml
bash run.sh start 

# menghentikan container
bash run.sh stop

# membersihkan container
bash run.sh clean
```

### Urls
Open the admin console and the apps on the following URLs
```bash
# Keycloak admin console
http://localhost:8080/


contoh jika menggukan xampp
# Web-app-1
http://localhost/(direktori file pada htdocs)

# Web-app-2
http://localhost/(direktori file pada htdocs)

# Web-app-3
http://localhost/(direktori file pada htdocs)

# Web-app-4
http://localhost/(direktori file pada htdocs)
```

### Keycloak Admin Login
```bash
user: admin
password: admin
```

### DB akses
```
http://localhost/phpmyadmin/