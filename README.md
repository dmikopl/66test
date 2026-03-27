API do zarządzania produktami. Symfony 7.1, PHP 8.3, PostgreSQL 15. Docker Compose uruchamia całość jedną komendą. Nginx na porcie 8080. Endpointy REST pod /api/products. Historia cen automatyczna przy zmianie ceny. Swagger UI dostępny pod /api/doc. Testy PHPUnit - 13 testów, wszystkie przechodzą. Konfiguracja przez .env, przykład w .env.example.

## Uruchomienie

```bash
# Budowanie i uruchomienie
docker-compose up -d --build

# Sprawdzenie statusu
docker-compose ps

# Logi
docker-compose logs -f

# Zatrzymanie
docker-compose down

# Testy
docker-compose exec php vendor/bin/phpunit
```

## Kursy Walut

Przygotowałem obsługę historii kursów walut (EUR, USD) względem PLN:

- Encja `CurrencyRateHistory` przechowuje kursy z datą
- `CurrencyRateService` obsługuje konwersje cen między walutami
- Placeholder `fetchFromNbpApi()` przygotowany pod integrację z NBP
- Metody: `getRateForDate()`, `getLatestRate()`, `convertPrice()`
