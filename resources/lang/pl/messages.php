<?php

return [
    // Ogólne
    'subtitle' => 'Znajdź swój wymarzony samochód już dziś!',
    'from_per_day' => 'Od :price €/dzień',
    'book' => 'Zarezerwuj',
    'contact_whatsapp' => 'Skontaktuj się z nami przez WhatsApp',
    'error' => 'Przepraszamy, szukana strona nie została znaleziona.',
    'back_to_home' => '← Powrót do strony głównej',
    'pause' => 'Pauza',
    'play' => 'Odtwarzaj',
    'no_images_available' => 'Brak dostępnych zdjęć',
    'close' => 'Zamknij',
    'update' => 'Aktualizuj',
    'delete_image' => 'Usuń zdjęcie',
    'confirm_set_main_image' => 'Czy na pewno chcesz ustawić to zdjęcie jako główne?',
    'image_will_be_main' => 'To zdjęcie zostanie ustawione jako główne po zapisaniu zmian.',
    'confirm_delete_car' => 'Czy na pewno chcesz usunąć ten samochód? Tej czynności nie można cofnąć.',
    'welcome' => 'Witamy na stronie głównej',

    // Admin na stronie
    'login' => 'Zaloguj',
    'logout' => 'Wyloguj',
    'admin_panel' => 'Panel Administracyjny',
    'add_rental_car' => 'Dodaj wypożyczony samochód',
    'order_panel' => 'Panel zamówień',

    // Navbar
    'home' => 'Główna',
    'cars_rent' => 'Wynajem samochodów',
    'condition' => 'Warunek',
    'contact' => 'Kontakt',

    // Samochód na stronie
    'car_index' => 'Lista samochodów',
    'car_create' => 'Dodaj samochód',
    'car_edit' => 'Edytuj samochód',
    'type' => 'Typ',
    'select_type' => 'Wybierz typ',
    'model' => 'Model',
    'seats' => 'Liczba miejsc',
    'fuel_type' => 'Rodzaj paliwa',
    'select_fuel' => 'Wybierz rodzaj paliwa',
    'engine_capacity' => 'Pojemność silnika',
    'transmission' => 'Skrzynia biegów',
    'select_transmission' => 'Wybierz skrzynię biegów',
    'manual' => 'Manualna',
    'automatic' => 'Automatyczna',
    'main_image' => 'Główne zdjęcie',
    'gallery_images' => 'Galeria zdjęć',
    'current_image' => 'Aktualny obraz',
    'current_gallery' => 'Aktualna galeria (nowe obrazy będą dodawane)',
    'daily_price' => 'Cena za dzień',
    'registration_from' => 'Rok produkcji',

    // Rezerwacja na stronie samochodu
    'rental_prices' => 'Ceny wynajmu',
    'description' => 'Opis',
    'specifications' => 'Specyfikacja',
    'rental_date' => 'Data wynajmu',
    'rental_time' => 'Godzina wynajmu',
    'return_time' => 'Godzina zwrotu',
    'extra_delivery_fee' => 'Dodatkowa opłata za dostawę',
    'airport_delivery_included' => 'Dostawa na lotnisko w cenie',
    'days' => 'Dni',
    'book_now' => 'Zarezerwuj teraz',
    'additional_info' => 'Dodatkowe informacje',

    // Formularz klienta
    'name' => 'Imię i nazwisko',
    'email' => 'Email',
    'phone' => 'Telefon',
    'address' => 'Adres',

    // Zamówienia
    'orders' => [
        'title' => 'Zamówienia',
        'customer_orders' => 'Zamówienia Klientów',
        'id' => 'ID',
        'customer' => 'Klient',
        'car' => 'Samochód',
        'date' => 'Data',
        'time' => 'Czas',
        'delivery' => 'Dostawa',
        'status' => 'Status',
        'actions' => 'Akcje',
        'details' => 'Szczegóły',
        'order_details' => 'Szczegóły Zamówienia',
        'back_to_orders' => 'Powrót do zamówień',
        'customer_info' => 'Informacje o Kliencie',
        'car_info' => 'Informacje o Samochodzie',
        'order_status' => 'Status Zamówienia',
        'update_status' => 'Aktualizuj Status',
    ],

    // Statusy
    'statuses' => [
        'pending' => 'Oczekujące',
        'confirmed' => 'Potwierdzone',
        'completed' => 'Zakończone',
        'cancelled' => 'Anulowane',
    ],

    // Warunki wynajmu
    'rental_conditions' => 'Warunki Wynajmu',
    'welcome_text' => 'Witamy w RentCar! Poniżej znajdziesz zasady i warunki wypożyczenia naszych samochodów.',

    'requirements_title' => '1. Wymagania dotyczące najemcy',
    'requirements_list' => [
        'Najemca musi mieć ukończone 21 lat.',
        'Posiadać ważne prawo jazdy od co najmniej 2 lat.',
        'Przedstawić ważny dokument tożsamości.',
    ],

    'payment_title' => '2. Warunki płatności',
    'payment_list' => [
        'Płatność jest wymagana z góry za cały okres najmu.',
        'Akceptujemy płatności kartą kredytową oraz przelewem online.',
    ],

    'return_title' => '3. Zwrot pojazdu',
    'return_list' => [
        'Samochód należy zwrócić w umówionym terminie i miejscu.',
        'Pojazd powinien być zwrócony z pełnym bakiem paliwa.',
        'Opóźnienia w zwrocie mogą skutkować dodatkowymi opłatami.',
    ],

    'liability_title' => '4. Odpowiedzialność i ubezpieczenie',
    'liability_list' => [
        'Najemca odpowiada za szkody powstałe z winy użytkownika.',
        'Samochody są ubezpieczone, ale udział własny może obowiązywać.',
    ],

    'final_title' => '5. Postanowienia końcowe',
    'final_text' => 'W przypadku naruszenia warunków najmu, RentCar zastrzega sobie prawo do rozwiązania umowy. Szczegóły regulaminu są dostępne na naszej stronie lub u konsultanta.',

    // session success and errors
    'login_success' => 'Zalogowano pomyślnie!',
    'logout_success' => 'Wylogowano pomyślnie!',
    'car_created' => 'Samochód został utworzony!',
    'car_updated' => 'Samochód został zaktulizowany!',
    'car_deleted' => 'Samochód został usunięty!',
    'auth_error' => 'Błąd uwierzytelniania',
    'order_already' => 'Złożyłeś już dzisiaj zamówienie. Spróbuj ponownie jutro.',
    'order_unavailable' => 'Ten samochód jest obecnie niedostępny do wypożyczenia.',
    'order_created' => 'Zamówienie zostało złożone! Wkrótce się z Tobą skontaktujemy.',
];
