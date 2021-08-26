## GUIDE INSTALL LOCAL (kalo mau)

git clone

buka cmd / bash

run composer install

buat file .env, copy isi .env.example ke .env, jan lupa save

run php artisan key:generate

buat db namanya 'pc24'

run php artisan migrate --seed buat migrate database sekaligus seed dummy data

run php artisan passport:install buat auth (dapetin client id dan client secret)

url: localhost/pc24/public (xampp)

## LIST ENDPOINT

Auth
  - Login
    - url(post): /api/login
    - data:  
    ```
    {  
      "username": "admin"  
      "password": "secret"  
      "client_id": "2"  
      "client_secret": "liat di table oauth_clients di database (kolom secret yg idnya 2)"
    }
    ```

  - Get User
    - url(get): /api/user
    - header: 
      - Authorization: Bearer {access_token yg di dpt dr login}
      - Accept: application/json

  - Logout
    - url(get): /api/logout
    - header: 
      - Authorization: Bearer {access_token yg di dpt dr login}
      - Accept: application/json


News
  - Get News
    - url(get): /api/news/{offset?}/{limit?}
    - ex: /api/news/1/3

  - Get News sort by date
    - url(get): /api/news/sort-date/{order}/{offset?}/{limit?}
    - order:
      - asc
      - desc
    - ex: /api/news/sort-date/asc/1/3

  - Get News sort by date && filter tag (1 tag)
    - url(get): /api/news/sort-date-with-tag/{order}/{tag}/{offset?}/{limit?}
    - order:
      - asc
      - desc
    - tag: tag in database
    - ex: /api/news/sort-date-with-tag/asc/sometag/1/3

  - (?) means optional

  - Create News
    - url(post): /api/news/store
    - data: 
    ```
    {
      "title" : "test",
      "content" : "<p>test</>",
      "image":{
          "filename": "test", 
          "filesize":523424, 
          "base64" : base64
      },
      "tags": [
        {"id":2,"name": "alias"},
        {"id":4, "name" : "odit"}, 
        {"id": null, "name": "new tagggggg"}
      ]
    }
    ```
  
  - Update News
    - url(post): /api/news/{id}/update
    - data: 
    ```
    {
      "title" : "test",
      "content" : "<p>test</>",
      "image":{
          "filename": "test", 
          "filesize":523424, 
          "base64" : base64
      },
      "tags": [
        {"id":2,"name": "alias"},
        {"id":4, "name" : "odit"}, 
        {"id": null, "name": "new tagggggg"}
      ]
    }
    ```
  - Delete News
    - url(get): /api/news/{id}/delete
    - header: 
        - Authorization: Bearer {access_token yg di dpt dr login}
        - Accept: application/json