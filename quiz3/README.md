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

W projekcie sa dodane podstawowe funkcje wymagane dla tematu quiz:

- logowanie zwykle przez email i haslo,
- proste logowanie spolecznosciowe demo: Google/GitHub (bez prawdziwego OAuth, jako wersja szkolna do pokazania funkcji),
- pytania jednokrotnego wyboru, wielokrotnego wyboru, otwarte i z luka,
- podpowiedzi do pytan,
- ranking globalny i ranking dla konkretnego quizu,
- odznaki za aktywnosc: pierwszy quiz, 5 quizow i bezbledny wynik,
- eksport wynikow do CSV,
- podstawowe ustawienia personalizacji: motyw jasny/ciemny i jezyk PL/EN,
- panel administratora z zarzadzaniem quizami i uzytkownikami.
