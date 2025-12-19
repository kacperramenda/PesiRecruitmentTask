# PesiRecruitmentTask

Wtyczka wyświetlająca dynamiczny pasek postępu do darmowej dostawy na karcie produktu.

## Opis działania
Wtyczka sprawdza czy aktualnie przeglądany produkt kwalifikuje się do darmowej dostawy (próg 200zł), a nastepnie wyświetla odpowiedni komunikat nad przyciskiem "Dodaj do koszyka"

## Założenia
**Zgodnie z poleceniem plugin oblicza różnicę między aktualną ceną, a progiem darmowej dostawy

**Do pobrania ceny użyłem wc_get_price_to_display() aby uzyskać faktyczną kwotę produktu

**Kwoty są wyświetlane przy użyciu wc_price(), co zapewnia odpowiednie formatowanie

**Kod został zabezpieczony w klasie i przestrzeni nazw `Pesi\FreeShippingCounter`, aby uniknąć konfliktów

**Dane wyjściowe zabezpieczone są `esc_attr` oraz `wp_kses_post`

**CSS jest ładowany tylko na stronie produktu

Wtyczka jest elastyczna. Domyślny próg darmowej dostawy (200.00) można zmienić za pomocą filtra w pliku `functions.php` motywu:

```php
add_filter( 'fsc_threshold', function() {
    return 300.00;
});
