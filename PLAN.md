# System zarządzania treningami i planem treningowym

## 1. Opis projektu
Aplikacja webowa umożliwia użytkownikowi:
- Rejestrację i logowanie
- Tworzenie własnych planów treningowych
- Dodawanie dni treningowych do planu
- Przypisywanie ćwiczeń do dnia treningowego
- Zapisywanie progresu (data, ćwiczenie, serie × powtórzenia)
- Przegląd historii postępów

System działa w oparciu o PHP (bez frameworków) i MySQL, z prostym frontendem HTML/CSS.  
MVP koncentruje się na logowaniu/rejestracji, planach, dniach treningowych, ćwiczeniach i progresie.

---

## 2. Funkcjonalności

### ✅ MVP
1. Rejestracja użytkownika
2. Logowanie / wylogowanie
3. Dashboard użytkownika
4. Tworzenie planów treningowych (nazwa, cel opcjonalny)
5. Dodawanie dni treningowych do planu
6. Dodawanie ćwiczeń do dnia treningowego (nazwa, grupa mięśni, serie, powtórzenia)
7. Przegląd planu treningowego
8. Zapisywanie progresu (data, ćwiczenie, serie × powtórzenia)
9. Wyświetlanie historii progresu
10. Podstawowe zabezpieczenia: SQLi, hasła, sesje

### ➕ Funkcje dodatkowe
- Import/eksport planów i progresu (CSV/JSON)
- Proste raporty i wizualizacje postępów
- Edycja/usuwanie ćwiczeń i dni treningowych
- Filtrowanie ćwiczeń wg grup mięśniowych
- Responsywny frontend (Bootstrap opcjonalnie)

---

## 3. Plan działania i etapy

### Etap 1 – Przygotowanie środowiska i bazy danych
**Zadania:**
1. Instalacja lokalnego serwera LAMP/XAMPP
   - **Cel:** działające środowisko PHP + MySQL + phpMyAdmin
   - **DoD:** testowe zapytanie SELECT działa
2. Zaprojektowanie schematu bazy danych (MVP)
   - **Cel:** tabele: `users`, `training_plans`, `workouts`, `exercises`, `workout_exercises`, `progress`
   - **DoD:** diagram logiczny i tabele utworzone
3. Utworzenie `config.php` + połączenie PDO
   - **Cel:** centralna konfiguracja
   - **DoD:** połączenie działa poprawnie

---

### Etap 2 – Autoryzacja
**Zadania:**
1. Rejestracja użytkownika
   - **DoD:** walidacja email, password_hash zapisany w DB
2. Logowanie / wylogowanie
   - **DoD:** sesja działa, dashboard dostępny tylko po zalogowaniu

---

### Etap 3 – Dashboard i przegląd planów
1. Dashboard (`dashboard.php`)
   - **DoD:** pokazuje tylko plany zalogowanego użytkownika
2. Dodawanie planu (`plan_add.php`)
   - **DoD:** plan zapisany w DB, widoczny w dashboard

---

### Etap 4 – Zarządzanie dniami treningowymi
1. Dodawanie dnia treningowego (`workout_add.php`)
   - **DoD:** dzień przypisany do planu w DB
2. Wyświetlanie dni w planie (`workout_view.php`)
   - **DoD:** widok dni i przypisanych ćwiczeń

---

### Etap 5 – Zarządzanie ćwiczeniami
1. Dodawanie ćwiczeń do dnia (`exercise_add.php`)
   - **DoD:** ćwiczenia zapisane i widoczne w widoku dnia
2. Podgląd ćwiczeń w dniu treningowym
   - **DoD:** ćwiczenia przypisane tylko do zalogowanego użytkownika

---

### Etap 6 – Progres ćwiczeń
1. Dodawanie progresu (`progress_add.php`)
   - **DoD:** wpis z datą, ćwiczeniem, serią i powtórzeniami w DB
2. Historia progresu (`history.php`)
   - **DoD:** lista wpisów dla użytkownika i ćwiczenia

---

### Etap 7 – Bezpieczeństwo i walidacja
- Prepared statements dla wszystkich zapytań
- Hasła hash (password_hash / password_verify)
- Walidacja formularzy (email, puste pola, typ danych)
- Sprawdzanie sesji i właściciela rekordu (IDOR)
- CSRF token w formularzach (opcjonalnie)

---

## 4. Projekt bazy danych

### users
| Kolumna | Typ | Uwagi |
|---------------|---------------|----------------|
| id | INT PK AUTO | |
| email | VARCHAR(100) | UNIQUE |
| password_hash | VARCHAR(255) | |
| created_at | DATETIME | |

### training_plans
| Kolumna | Typ | Uwagi |
|---------|-------------|----------------|
| id | INT PK AUTO | |
| user_id | INT | FK → users.id |
| name | VARCHAR(100)| |

### workouts
| Kolumna | Typ | Uwagi |
|---------|-------------|----------------|
| id | INT PK AUTO | |
| plan_id | INT | FK → training_plans.id |
| name | VARCHAR(100)| |

### exercises
| Kolumna | Typ | Uwagi |
|--------------|-------------|-------|
| id | INT PK AUTO | |
| name | VARCHAR(100)| |
| muscle_group | VARCHAR(50) | |

### workout_exercises
| Kolumna | Typ | Uwagi |
|--------------|-------------|-------|
| id | INT PK AUTO | |
| workout_id | INT | FK → workouts.id |
| exercise_id | INT | FK → exercises.id |
| sets | INT | |
| reps | INT | |

### progress
| Kolumna | Typ | Uwagi |
|---------------|-------------|-------|
| id | INT PK AUTO | |
| user_id | INT | FK → users.id |
| exercise_id | INT | FK → exercises.id |
| weight | DECIMAL | |
| reps | INT | |
| workout_date | DATE | |

**Indeksy:** users.email UNIQUE, wszystkie FK indeksowane.

---

## 5. Struktura katalogów
/config.php /db.php /header.php /footer.php /functions/ # walidacje, auth index.php /register.php /login.php /logout.php /dashboard.php /plan_add.php /workout_add.php /exercise_add.php /progress_add.php /history.php /assets/css/style.css /assets/js/main.js
Skopiuj kod

---

## 6. Routing / strony

| Endpoint | Funkcja |
|----------------------|--------------------------------------------|
| /register.php | Rejestracja |
| /login.php | Logowanie |
| /logout.php | Wylogowanie |
| /dashboard.php | Lista planów |
| /plan_add.php | Dodanie planu |
| /workout_add.php | Dodanie dnia do planu |
| /exercise_add.php | Dodanie ćwiczeń do dnia |
| /progress_add.php | Dodanie progresu dla ćwiczenia |
| /history.php | Przegląd historii progresu |

---

## 7. Ryzyka / pułapki

- Brak walidacji → SQL Injection → stosować prepared statements
- Brak sprawdzania właściciela planu → IDOR → weryfikacja user_id
- Hasła w czystym tekście → używać password_hash()
- Formularze bez sesji → dostęp niezalogowanych → sprawdzać sesję
- Brak limitów w inputach → overflow → ograniczać długość pól
- Usuwanie planu z ćwiczeniami → cascade delete lub transakcje

**Rekomendacje:** walidacja serwerowa, prepared statements, session checks, ograniczenie długości pól

---

## 8. Kolejność implementacji (MVP)

1. Środowisko + baza danych + config
2. Rejestracja i logowanie
3. Dashboard i lista planów
4. Tworzenie planu
5. Dodawanie dni treningowych
6. Dodawanie ćwiczeń do dnia
7. Dodawanie i przegląd progresu
8. Walidacja i bezpieczeństwo
9. Testowanie całości

---

## 9. Priorytet
- Funkcjonalność i prostota
- Logowanie/rejestracja, plan, trening, progres – absolutne MVP
- Raporty, import/eksport, role admin – funkcje dodatkowe po MVP

---