# Quiz PHP

Aplikacja zaliczeniowa w PHP 8 z baza MySQL. Projekt zawiera logowanie, role, CRUD quizow, pytania kilku typow, rozwiazywanie quizow, zapis wynikow, panel administratora, upload obrazka i ustawienia konta.

Mapa folderow, tabel i tras jest w pliku `MAPA_PROJEKTU.md`.

## Uruchomienie

1. Wlacz Apache i MySQL w XAMPP.
2. Wejdz w folder projektu.
3. Uruchom serwer PHP:

```bash
cd public
php -S localhost:8000
```

Potem wejdz w przegladarce na:

```text
http://localhost:8000
```

Aplikacja sama utworzy baze `quiz_php`, tabele i dane startowe przy pierwszym wejsciu na strone.

## Dane MySQL

Domyslne dane polaczenia:

```text
host: 127.0.0.1
port: 3306
baza: quiz_php
uzytkownik: root
haslo: puste
```

Struktura bazy jest tez w pliku `database.sql`, gdyby trzeba bylo zaimportowac ja recznie w phpMyAdmin.

## Konta testowe

| Rola | Email | Haslo |
| --- | --- | --- |
| Administrator | admin@example.com | admin123 |
| Autor | author@example.com | author123 |
| Uzytkownik | user@example.com | user123 |

## Zakres wymagan

- kod obiektowy: modele i kontrolery w katalogu `app`
- formularze z walidacja po stronie serwera
- zachowanie wartosci formularzy po bledzie walidacji
- tabela rekordow z akcjami edycji i usuwania
- oddzielenie logiki od widokow
- pelny CRUD quizow jako glownego zasobu
- baza danych MySQL przez PDO
- tabele powiazane relacjami, w tym relacja many-to-many quizow i kategorii
- upload obrazka quizu i usuwanie pliku razem z rekordem
- znaczniki czasu `created_at` oraz `updated_at`
- rejestracja, logowanie, sesje, role i ustawienia konta
- panel administratora do kontroli quizow i kont
