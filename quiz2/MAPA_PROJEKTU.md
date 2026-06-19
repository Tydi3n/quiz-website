# Mapa projektu - Quiz PHP

## 1. Struktura folderow

```text
quiz-app/
|-- README.md
|-- MAPA_PROJEKTU.md
|-- database.sql
|-- app/
|   |-- bootstrap.php
|   |-- helpers.php
|   |-- Core/
|   |   `-- Database.php
|   |-- Controllers/
|   |   |-- AdminController.php
|   |   |-- AttemptController.php
|   |   |-- AuthController.php
|   |   |-- QuestionController.php
|   |   `-- QuizController.php
|   `-- Models/
|       |-- Attempt.php
|       |-- Question.php
|       |-- Quiz.php
|       `-- User.php
|-- public/
|   |-- index.php
|   |-- assets/
|   |   `-- style.css
|   `-- uploads/
`-- views/
    |-- admin/
    |-- attempts/
    |-- auth/
    |-- errors/
    |-- layouts/
    |-- questions/
    `-- quizzes/
```

## 2. Co znajduje sie w folderach

| Folder / plik | Do czego sluzy |
| --- | --- |
| `public/index.php` | Glowny plik startowy aplikacji. Odbiera parametr `route` i uruchamia odpowiedni kontroler. |
| `public/assets/style.css` | Style strony, czyli wyglad formularzy, tabel, przyciskow i menu. |
| `public/uploads/` | Folder na obrazki dodane do quizow. |
| `database.sql` | Struktura bazy MySQL do recznego importu, np. w phpMyAdmin. |
| `app/bootstrap.php` | Uruchamia sesje, laduje klasy i przygotowuje baze danych. |
| `app/helpers.php` | Funkcje pomocnicze, np. przekierowania, widoki, CSRF, sprawdzanie logowania. |
| `app/Core/Database.php` | Polaczenie z baza MySQL, tworzenie bazy, tabel i danych startowych. |
| `app/Controllers/` | Logika obslugi stron, np. logowanie, quizy, pytania, wyniki i admin. |
| `app/Models/` | Praca na danych w bazie, np. uzytkownicy, quizy, pytania, podejscia. |
| `views/` | Pliki HTML/PHP odpowiedzialne za wyswietlanie stron. |
| Baza `quiz_php` w MySQL | Glowna baza danych aplikacji. |

## 3. Przeplyw dzialania aplikacji

```text
Uzytkownik wchodzi na strone
        |
        v
public/index.php
        |
        v
Wybor route, np. quizzes.index
        |
        v
Kontroler, np. QuizController
        |
        v
Model, np. Quiz, Question, User
        |
        v
Baza danych MySQL
        |
        v
Widok z folderu views/
        |
        v
Gotowa strona w przegladarce
```

## 4. Glowne kontrolery

| Kontroler | Odpowiedzialnosc |
| --- | --- |
| `AuthController` | Logowanie, rejestracja, wylogowanie, ustawienia konta. |
| `QuizController` | Lista quizow, dodawanie, edycja, usuwanie i widok jednego quizu. |
| `QuestionController` | Dodawanie, edycja i usuwanie pytan w quizie. |
| `AttemptController` | Rozwiazywanie quizu i pokazywanie wyniku. |
| `AdminController` | Panel admina, zarzadzanie quizami i uzytkownikami. |

## 5. Glowne modele

| Model | Odpowiedzialnosc |
| --- | --- |
| `User` | Konta uzytkownikow, role, logowanie, zmiana roli. |
| `Quiz` | Quizy, kategorie, filtrowanie, ukrywanie, obrazki. |
| `Question` | Pytania i odpowiedzi do quizow. |
| `Attempt` | Wyniki uzytkownikow i zapis odpowiedzi. |

## 6. Najwazniejsze trasy

| Route | Co robi |
| --- | --- |
| `quizzes.index` | Lista quizow. |
| `quizzes.show` | Szczegoly jednego quizu. |
| `quizzes.create` / `quizzes.store` | Formularz dodawania i zapis quizu. |
| `quizzes.edit` / `quizzes.update` | Formularz edycji i zapis zmian. |
| `quizzes.delete` | Usuniecie quizu. |
| `questions.create` / `questions.store` | Dodanie pytania. |
| `questions.edit` / `questions.update` | Edycja pytania. |
| `questions.delete` | Usuniecie pytania. |
| `attempts.take` | Rozwiazywanie quizu. |
| `attempts.submit` | Sprawdzenie quizu. |
| `attempts.result` | Pokazanie wyniku. |
| `auth.login` | Logowanie. |
| `auth.register` | Rejestracja. |
| `auth.logout` | Wylogowanie. |
| `auth.settings` | Konto i ustawienia. |
| `admin.dashboard` | Panel administratora. |

## 7. Tabele w bazie danych

| Tabela | Zawartosc |
| --- | --- |
| `users` | Uzytkownicy, hasla, role i motyw strony. |
| `categories` | Kategorie quizow. |
| `quizzes` | Quizy, opis, trudnosc, autor, obrazek, status. |
| `quiz_categories` | Polaczenie quizow z kategoriami. |
| `questions` | Pytania przypisane do quizow. |
| `answers` | Odpowiedzi do pytan wyboru. |
| `attempts` | Podejscia uzytkownikow do quizow. |
| `attempt_answers` | Odpowiedzi zaznaczone przez uzytkownika. |

## 8. Role uzytkownikow

| Rola | Uprawnienia |
| --- | --- |
| `user` | Moze rozwiazywac quizy i widziec swoje wyniki. |
| `author` | Moze tworzyc quizy oraz dodawac pytania. |
| `admin` | Moze zarzadzac quizami i kontami uzytkownikow. |

## 9. Glowne funkcje projektu

- rejestracja i logowanie
- sesja uzytkownika
- role: uzytkownik, autor, administrator
- CRUD quizow
- CRUD pytan
- typy pytan: jednokrotny wybor, wielokrotny wybor, otwarte, luka
- filtrowanie i sortowanie quizow
- rozwiazywanie quizu
- zapis wyniku
- panel administratora
- baza MySQL przez PDO
- upload obrazka quizu
- walidacja formularzy po stronie serwera
- oddzielenie logiki od widoku
