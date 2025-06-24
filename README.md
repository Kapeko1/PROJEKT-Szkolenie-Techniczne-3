## Instalacja:

Uruchom kontenery:
`docker-compose up -d`

Zainstaluj zależności composera:
`docker-compose exec app composer install`

Wygeneruj klucz aplikacji:
`docker-compose exec app php artisan key:generate`

Uruchom migracje:
`docker-compose exec app php artisan migrate`


## Adresy:
Aplikacja będzie dostępna pod adresem `http://localhost:8080`.

API jest dostępne pod adresem bazowym: `http://localhost:8080/api`

## Endpointy

Wszystkie odpowiedzi są w JSON, trzeba pamiętac o ustawieniu nagłówków żądania na Accept: application/json oraz Content-Type: application/json (dla post i put)


### Kategorie (`/api/categories`)
`GET /api/categories`
Pobiera listę wszystkich kategorii.

`POST /api/categories`
Tworzy nową kategorię.
Przykładowe body:
```json
{
    "name": "Nazwa Kategorii",
    "description": "Opis kategorii (opcjonalnie)",
    "is_active": true
}
```

`GET /api/categories/{id}`
Pobiera szczegóły konkretnej kategorii.

`PUT /api/categories/{id}`
Aktualizuje istniejącą kategorię.Przykładowe body
```json
{
    "name": "Nowa Nazwa Kategorii",
    "is_active": false
}
```
`DELETE /api/categories/{id}`
Usuwa kategorię


### Produkty (`/api/products`)
`GET /api/products`

`POST /api/products`
Przykładowe body (trzeba pamiętać zeby kategoria istniała:
```json
{
    "name": "Nazwa produktu",
    "description": "Opis produktu",
    "sku": "SKU-123",
    "price": 10.00,
    "quantity": 5,
    "category_id": 1,
    "is_active": true
}
```

`GET /api/products/{id}` Pobiera szczegóły konkretnego produktu

`PUT /api/products/{id}`
Przykładowe body:
```json
{
    "price": 89.99,
    "quantity": 90
}
```

`DELETE /api/products/{id}` Usuwa produkt

### Zamówienia (`/api/orders`)
`GET /api/orders` Pobiera wszystkie zamówienia

`POST /api/orders` Przykładowe body: 
```json
        {
            "product_id": 1, 
            "customer_name": "Test TTTT",
            "customer_email": "Test@ex.pl",
            "quantity": 2,
            "status": "pending" ,
            "order_date": "2025-06-05 09:40:00" 
        }
```

`GET /api/orders/{id}` Pobiera szczegóły konkretnego zamówienia

`PUT /api/orders/{id}` Aktualizacja zamówienia. Przykładowe body:
```json
        {
            "status": "completed"
        }
```

`DELETE /api/orders/{id}` Usuwa zamówienie



# TODO i ważne:

Brakuje sporo rzeczy do production-ready np. walidacja żądań, ukrycie błędów db przed użytkownikiem (przy sql error nadal wyrzuca cały komunikat błędu do użytkownika, utworzenie DTO dla statusu, dokładniejsza obsługa wyjątków ), ale bałem się że zabraknie mi czasu.


## Ważne 
Niektóre rzeczy (jak np. pliki dockera, fragmenty kodu z serwisów czy niektóre związane z cache) zabrałem ze swoich innych projektów i mogą nie być "szyte na miarę" tylko dostosowane pod ten projekt. Największym problemem była presja czasu. 
Wrzucam też mój .env (żeby pokazać zmiany w np. session driver)




