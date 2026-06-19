# Quiz PHP

Aplikacja zaliczeniowa w PHP 8 z baza MySQL. Projekt zawiera logowanie, role, CRUD quizow, pytania kilku typow, rozwiazywanie quizow, zapis wynikow, panel administratora, upload obrazka i ustawienia konta.

## Uruchomienie

1. Wlacz Apache i MySQL w XAMPP.
2. Wejdz w folder projektu.
3. Uruchom serwer PHP:

```bash
cd public
php -S localhost:8000
```

```text
http://localhost:8000
```



Struktura bazy jest tez w pliku `database.sql`, gdyby trzeba bylo zaimportowac ja recznie w phpMyAdmin.

## Konta testowe

| Rola | Email | Haslo |
| --- | --- | --- |
| Administrator | admin@example.com | admin123 |
| Autor | author@example.com | author123 |
| Uzytkownik | user@example.com | user123 |
