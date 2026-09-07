<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Strony marki (plan v1.0 §3) — Atelier, Verkaufsstellen, Wissenswertes,
 * B2B, Kontakt. Renderowane w layout eva (spójny wygląd całej witryny),
 * treść wg starej strony evastone.eu + wytycznych „quiet luxury".
 */
class BrandController extends Controller
{
    public function show(string $locale, string $page): View
    {
        $method = 'content'.ucfirst($page);
        $data = $this->{$method}($locale);

        return view("brand.{$page}", array_merge($data, ['locale' => $locale]));
    }

    /* ---------------------------------------------------------------- Atelier */

    private function contentAtelier(string $l): array
    {
        $c = [
            'de' => [
                'kick' => 'Atelier', 'title' => 'Die Werkstatt hinter der Struktur.',
                'lead' => 'EvaStone entsteht in einer Goldschmiedewerkstatt, in der traditionelle Methoden der Schmuckherstellung mit moderner 3D-Gestaltung zusammenkommen. Das Design ist von der Natur inspiriert, die Oberflächenstruktur darauf abgestimmt.',
                'quote' => 'Schönheit ist die einzige Form, die keine Erklärung braucht — aber eine Handschrift.',
                'craftKick' => 'Handwerk', 'craftTitle' => 'Zwei Werkstätten, eine Handschrift.',
                'craftBody' => 'Gearbeitet wird an eigenen Werkzeugen und in eigener Fertigung: Fassen, Löten, Oxidieren, Polieren. Die Struktur entsteht am Stück selbst — sie wird nicht aufgedruckt und nicht aufgeklebt.',
                'timelineKick' => 'Die Marke in Daten', 'timelineTitle' => 'Von Bernstein zum rohen Diamantwürfel.',
                'timeline' => [
                    ['1995', 'Der Anfang', 'Silber, verbunden mit Bernstein. Die Werkstatt arbeitet von Beginn an mit eigenen Werkzeugen und eigener Fertigung.'],
                    ['2008', 'Gründung der Marke EvaStone', 'Silber und rohe Diamantwürfel treten in den europäischen Markt ein. Maßgeblich ist das Muster als Ganzes.'],
                    ['heute', 'Werkstatt und Facheinzelhandel', 'Traditionelles Goldschmiedehandwerk mit moderner 3D-Gestaltung. Verkauf ausschließlich über unabhängige Boutiquen in DE, AT, UK und CH.'],
                ],
                'diffKick' => 'Der Unterschied unter dem Finger', 'diffTitle' => 'Unregelmäßig ist hier kein Fehler.',
                'diffBody' => 'Es ist der Beweis der Handarbeit. Links die glatt polierte Schiene — überall gleich, maschinell reproduzierbar. Rechts die Oberflächenstruktur EvaStone: unter Streiflicht sichtbar, im Foto der Kundin sichtbar, in der Vitrine sichtbar.',
                'diffCapA' => 'Glatt poliert', 'diffCapB' => 'Oberflächenstruktur EvaStone',
                'ctaTitle' => 'Sie möchten ein Stück tragen?', 'ctaBody' => 'Wir verkaufen nicht direkt — Ihre Boutique tut es.',
                'ctaStore' => 'Verkaufsstelle finden', 'ctaB2b' => 'Konditionen für Boutiquen',
            ],
            'en' => [
                'kick' => 'Atelier', 'title' => 'The workshop behind the structure.',
                'lead' => 'EvaStone is made in a goldsmith workshop where traditional jewellery-making meets modern 3D design. The design is inspired by nature; the surface structure is matched to it.',
                'quote' => 'Beauty is the only form that needs no explanation — but it does need a handwriting.',
                'craftKick' => 'Craft', 'craftTitle' => 'Two workshops, one handwriting.',
                'craftBody' => 'We work with our own tools and our own production: setting, soldering, oxidising, polishing. The structure is created on the piece itself — not printed on, not glued on.',
                'timelineKick' => 'The brand in dates', 'timelineTitle' => 'From amber to the rough diamond cube.',
                'timeline' => [
                    ['1995', 'The beginning', 'Silver joined with amber. From the start the workshop works with its own tools and its own production.'],
                    ['2008', 'The EvaStone brand is founded', 'Silver and rough diamond cubes enter the European market. What matters is the pattern as a whole.'],
                    ['today', 'Workshop and specialist retail', 'Traditional goldsmithing with modern 3D design. Sold only through independent boutiques in DE, AT, UK and CH.'],
                ],
                'diffKick' => 'The difference under the finger', 'diffTitle' => 'Irregular is not a flaw here.',
                'diffBody' => 'It is the proof of handwork. On the left the smooth polished band — the same everywhere, machine-reproducible. On the right the EvaStone surface structure: visible under raking light, visible in your customer’s photo, visible in the window.',
                'diffCapA' => 'Smooth polished', 'diffCapB' => 'EvaStone surface structure',
                'ctaTitle' => 'Would you like to wear a piece?', 'ctaBody' => 'We do not sell directly — your boutique does.',
                'ctaStore' => 'Find a stockist', 'ctaB2b' => 'Terms for boutiques',
            ],
            'pl' => [
                'kick' => 'Atelier', 'title' => 'Warsztat, z którego bierze się struktura.',
                'lead' => 'EvaStone powstaje w warsztacie złotniczym, w którym tradycyjne metody tworzenia biżuterii łączą się z nowoczesnym projektowaniem 3D. Design jest inspirowany naturą, a struktura powierzchni idzie za wzorem.',
                'quote' => 'Piękno to jedyna forma, która nie potrzebuje wyjaśnienia — ale potrzebuje charakteru pisma.',
                'craftKick' => 'Rzemiosło', 'craftTitle' => 'Dwie pracownie, jeden charakter pisma.',
                'craftBody' => 'Pracujemy na własnych narzędziach i we własnej produkcji: osadzanie, lutowanie, oksydowanie, polerowanie. Struktura powstaje na samym wyrobie — nie jest nadrukowana ani naklejona.',
                'timelineKick' => 'Marka w datach', 'timelineTitle' => 'Od bursztynu do kostki surowego diamentu.',
                'timeline' => [
                    ['1995', 'Początek', 'Srebro łączone z bursztynem. Od początku pracownia działa na własnych narzędziach i we własnej produkcji.'],
                    ['2008', 'Powstaje marka EvaStone', 'Srebro i kostki surowego diamentu wchodzą na rynek europejski. Liczy się wzór jako całość.'],
                    ['dziś', 'Pracownia i handel specjalistyczny', 'Tradycyjne złotnictwo z nowoczesnym projektowaniem 3D. Sprzedaż wyłącznie przez niezależne butiki w DE, AT, UK i CH.'],
                ],
                'diffKick' => 'Różnica pod palcem', 'diffTitle' => 'Nieregularność nie jest tu błędem.',
                'diffBody' => 'To dowód pracy ręcznej. Po lewej gładko polerowana szyna — wszędzie taka sama, odtwarzalna maszynowo. Po prawej struktura powierzchni EvaStone: widoczna pod światłem, widoczna na zdjęciu klientki, widoczna w witrynie.',
                'diffCapA' => 'Gładko polerowane', 'diffCapB' => 'Struktura powierzchni EvaStone',
                'ctaTitle' => 'Chcesz nosić egzemplarz?', 'ctaBody' => 'Nie sprzedajemy bezpośrednio — robi to Twój butik.',
                'ctaStore' => 'Znajdź punkt sprzedaży', 'ctaB2b' => 'Warunki dla butików',
            ],
        ][$l];

        return $c + $this->links($l);
    }

    /* -------------------------------------------------------- Verkaufsstellen */

    private function contentVerkaufsstellen(string $l): array
    {
        $a = config('evastone.address');
        $c = [
            'de' => [
                'kick' => 'Verkaufsstellen', 'title' => 'Erhältlich in ausgewählten Boutiquen.',
                'lead' => 'EvaStone wird nicht direkt verkauft. Sie finden die Modelle bei unabhängigen Juwelieren und Goldschmieden in Deutschland, Österreich, Großbritannien und der Schweiz — fragen Sie dort nach einer Modellnummer.',
                'mapTitle' => 'Werkstatt & Kontakt', 'mapNote' => 'Die Liste der Verkaufsstellen wird laufend ergänzt. Schreiben Sie uns, wir nennen Ihnen die nächstgelegene Boutique.',
                'boutiqueTitle' => 'Sie führen eine Boutique?', 'boutiqueBody' => 'Werden Sie Verkaufsstelle — Konditionen für unabhängige Häuser.',
                'ctaContact' => 'Nach Verfügbarkeit fragen', 'ctaB2b' => 'Konditionen für Boutiquen',
            ],
            'en' => [
                'kick' => 'Stockists', 'title' => 'Available at selected boutiques.',
                'lead' => 'EvaStone is not sold directly. You will find the models at independent jewellers and goldsmiths in Germany, Austria, the UK and Switzerland — ask there for a model number.',
                'mapTitle' => 'Workshop & contact', 'mapNote' => 'The list of stockists is continually expanded. Write to us and we will name the nearest boutique.',
                'boutiqueTitle' => 'Do you run a boutique?', 'boutiqueBody' => 'Become a stockist — terms for independent houses.',
                'ctaContact' => 'Ask about availability', 'ctaB2b' => 'Terms for boutiques',
            ],
            'pl' => [
                'kick' => 'Punkty sprzedaży', 'title' => 'Dostępne w wybranych butikach.',
                'lead' => 'EvaStone nie jest sprzedawane bezpośrednio. Modele znajdziesz u niezależnych jubilerów i złotników w Niemczech, Austrii, Wielkiej Brytanii i Szwajcarii — zapytaj tam o numer modelu.',
                'mapTitle' => 'Pracownia i kontakt', 'mapNote' => 'Lista punktów sprzedaży jest na bieżąco uzupełniana. Napisz do nas, wskażemy najbliższy butik.',
                'boutiqueTitle' => 'Prowadzisz butik?', 'boutiqueBody' => 'Zostań punktem sprzedaży — warunki dla niezależnych sklepów.',
                'ctaContact' => 'Zapytaj o dostępność', 'ctaB2b' => 'Warunki dla butików',
            ],
        ][$l];

        return $c + ['addr' => $a, 'mapsQuery' => urlencode($a['maps_query'])] + $this->links($l);
    }

    /* ---------------------------------------------------------- Wissenswertes */

    private function contentWissenswertes(string $l): array
    {
        $c = [
            'de' => [
                'kick' => 'Wissenswertes', 'title' => 'Was ein Stück von EvaStone ausmacht.',
                'lead' => 'Kein Katalog, keine Preise — sondern das, was hinter dem Schmuck steht: Material, Oberfläche, Pflege.',
                'cards' => [
                    ['925 Silber', 'Warum Silber.', 'Sterlingsilber, in eigener Fertigung verarbeitet. Es lässt sich fassen, löten, oxidieren und vergolden — und trägt die Oberflächenstruktur, die jedes Stück unverwechselbar macht.'],
                    ['Acht Steine, vier Schliffe', 'Eine geschlossene Liste.', 'Rohdiamantwürfel, schwarzer Diamantwürfel, Topas, weißer und oranger Saphir, rosa Turmalin, Smaragd, Tansanit. Jeder Stein wird mit Namen und Schliff angegeben — rund, quadratisch, rechteckig oder Navette.'],
                    ['Oberflächenstruktur', 'Der Unterschied, den man fühlt.', 'Die Struktur entsteht am Stück selbst, nicht als Aufdruck. Unter Streiflicht, im Foto und in der Vitrine sichtbar — der Beweis der Handarbeit.'],
                    ['Pflege', 'Damit die Struktur bleibt.', 'Weiches Tuch, keine Scheuermittel, getrennt aufbewahren. Oxidierte und vergoldete Flächen nur trocken reinigen, damit die Handschrift des Stücks erhalten bleibt.'],
                ],
                'ctaTitle' => 'Die Modelle ansehen', 'ctaBody' => 'Eine Auswahl aus der eigenen Fertigung.',
                'ctaGallery' => 'Zur Galerie', 'ctaStore' => 'Verkaufsstelle finden',
            ],
            'en' => [
                'kick' => 'Knowledge', 'title' => 'What makes a piece of EvaStone.',
                'lead' => 'No catalogue, no prices — but what stands behind the jewellery: material, surface, care.',
                'cards' => [
                    ['925 silver', 'Why silver.', 'Sterling silver worked in our own production. It can be set, soldered, oxidised and gold-plated — and it carries the surface structure that makes every piece unmistakable.'],
                    ['Eight stones, four cuts', 'A closed list.', 'Rough diamond cube, black diamond cube, topaz, white and orange sapphire, pink tourmaline, emerald, tanzanite. Each stone is named with its cut — round, square, rectangular or navette.'],
                    ['Surface structure', 'The difference you can feel.', 'The structure is created on the piece itself, not as a print. Visible under raking light, in a photo and in the window — the proof of handwork.'],
                    ['Care', 'So the structure lasts.', 'Soft cloth, no abrasives, store separately. Clean oxidised and gold-plated surfaces dry only, so the handwriting of the piece is preserved.'],
                ],
                'ctaTitle' => 'See the models', 'ctaBody' => 'A selection from our own workshop.',
                'ctaGallery' => 'To the gallery', 'ctaStore' => 'Find a stockist',
            ],
            'pl' => [
                'kick' => 'Wiedza', 'title' => 'Co składa się na egzemplarz EvaStone.',
                'lead' => 'Bez katalogu i cen — za to to, co stoi za biżuterią: materiał, powierzchnia, pielęgnacja.',
                'cards' => [
                    ['Srebro 925', 'Dlaczego srebro.', 'Srebro próby 925 obrabiane we własnej produkcji. Można je osadzać, lutować, oksydować i złocić — i nosi strukturę powierzchni, która czyni każdy egzemplarz rozpoznawalnym.'],
                    ['Osiem kamieni, cztery szlify', 'Lista zamknięta.', 'Kostka surowego i czarnego diamentu, topaz, biały i pomarańczowy szafir, różowy turmalin, szmaragd, tanzanit. Każdy kamień podajemy z nazwą i szlifem — okrągły, kwadratowy, prostokątny lub nawetka.'],
                    ['Struktura powierzchni', 'Różnica, którą czujesz.', 'Struktura powstaje na samym wyrobie, nie jako nadruk. Widoczna pod światłem, na zdjęciu i w witrynie — dowód pracy ręcznej.'],
                    ['Pielęgnacja', 'Aby struktura została.', 'Miękka ściereczka, bez środków ściernych, przechowywać osobno. Powierzchnie oksydowane i złocone czyścić tylko na sucho, by zachować charakter egzemplarza.'],
                ],
                'ctaTitle' => 'Zobacz modele', 'ctaBody' => 'Wybór z naszej pracowni.',
                'ctaGallery' => 'Do galerii', 'ctaStore' => 'Znajdź punkt sprzedaży',
            ],
        ][$l];

        return $c + $this->links($l);
    }

    /* -------------------------------------------------------------------- B2B */

    private function contentB2b(string $l): array
    {
        $portal = config('evastone.b2b_portal_url');
        $c = [
            'de' => [
                'kick' => 'B2B — Für den Fachhandel',
                'title' => 'Nachbestellung in zwei Klicks. Vitrine startklar.',
                'lead' => 'EvaStone beliefert unabhängige Boutiquen und Goldschmieden in DE, AT, UK und CH — keine Endkundinnen. Ein Kanal für jede Bestellung, ein Ansprechpartner mit Namen, ein Prozess, der Ihre Zeit nicht kostet.',
                // dlaczego (różnicowanie)
                'whyKick' => 'Warum EvaStone',
                'whyTitle' => 'Was die Kundin drei Straßen weiter nicht findet.',
                'whyBody' => 'Glatt poliertes Silber ist überall gleich verfügbar — wer es führt, konkurriert mit jedem, der es auch führt. Die unregelmäßige Oberflächenstruktur von EvaStone ist der Unterschied, der im Vorbeigehen entscheidet: unter Streiflicht sichtbar, im Foto Ihrer Kundin sichtbar, in der Vitrine sichtbar.',
                // proces
                'howKick' => 'So läuft die Bestellung',
                'howTitle' => 'Ein Kanal, ein Status, kein Rätselraten.',
                'steps' => [
                    ['Bestellung über das Portal', 'Ein Kanal, eine Wahrheit. Auch telefonische Bestellungen tragen wir für Sie ins Portal ein.'],
                    ['Bestätigung in 5 Minuten', 'Automatische Eingangsbestätigung auf Deutsch oder Englisch.'],
                    ['Liefertermin in 48 Stunden', 'Ewa oder Rafał bestätigen den Termin persönlich — kein Automat.'],
                    ['Status jederzeit sichtbar', 'Produktion, Kommissionierung, Versand mit Sendungsnummer — im Portal, ohne Nachfrage.'],
                    ['Materialien ab der ersten Lieferung', 'Startpaket und Contentpaket: Fotos und Texte in DE/EN für Ihre Vitrine und Ihr Social.'],
                    ['Nachbestellung in zwei Klicks', 'Ab einem Stück, volle Größenrange, direkt aus Ihrer Bestellhistorie.'],
                ],
                // warunki
                'termsKick' => 'Konditionen',
                'termsTitle' => 'Einstieg in Sets, Nachschub im Einzelstück.',
                'terms' => [
                    'Erste Bestellung ab 4–5 Sets',
                    'Nachbestellung ab 1 Stück, volle Größenrange',
                    'Zwei Preislisten: EUR (Europa), USD für die übrige Welt',
                    'Start- und Contentpaket ab der ersten Lieferung',
                    'Whitelabel-Shop nach zwei bis drei Bestellungen',
                    'Kein Rabatt — Premium konkurriert über Wert, nicht über Preis',
                ],
                'termsNote' => 'Verbindliche Konditionen bestätigen wir schriftlich mit dem Angebot.',
                // kwalifikacja
                'fitKick' => 'Klarheit vor dem ersten Gespräch',
                'fitYes' => 'Passt', 'fitNo' => 'Passt nicht',
                'yes' => [
                    'Unabhängige Boutiquen und Goldschmieden mit eigener Kundschaft',
                    'Vitrinen, die sich von der Filiale in derselben Straße unterscheiden sollen',
                    'Häuser, die in Sets einsteigen und danach einzeln nachbestellen',
                    'Inhaberinnen und Inhaber, die selbst entscheiden, was in die Vitrine kommt',
                ],
                'no' => [
                    'Filialketten und Zentraleinkauf',
                    'Wer ein Sortiment aus tausenden Positionen mit starren Mindestpartien sucht',
                    'Wer Ware ohne eigene Handschrift braucht',
                    'Wer ausschließlich online ohne eigene Fläche verkauft',
                ],
                // portal + formularz
                'portalTitle' => 'Bereits Partner?',
                'portalBody' => 'Katalog, Verfügbarkeit und Bestellung im B2B-Portal.',
                'portalBtn' => 'Zum B2B-Portal',
                'soon' => 'Das Portal öffnet für Sie, sobald Ihr Konto eingerichtet ist. Registrieren Sie sich unten — wir melden uns innerhalb von zwei Werktagen.',
                'formKick' => 'Partner werden',
                'formTitle' => 'Erzählen Sie uns von Ihrem Geschäft.',
                'formBody' => 'Fünf Felder genügen, um Ihnen etwas Sinnvolles zu antworten. Eine Person meldet sich innerhalb von zwei Werktagen — keine Sequenz, keine Automation.',
                'fShop' => 'Geschäft / Boutique', 'fPerson' => 'Ansprechpartner', 'fEmail' => 'E-Mail',
                'fWeb' => 'Website', 'fCity' => 'Stadt', 'fCountry' => 'Land (DE, AT, UK, CH …)',
                'fSubmit' => 'Anfrage senden', 'fConsent' => 'Ihre Angaben nutzen wir ausschließlich zur Beantwortung dieser Anfrage.',
                'optional' => 'optional',
            ],
            'en' => [
                'kick' => 'B2B — For the trade',
                'title' => 'Re-order in two clicks. Window ready to go.',
                'lead' => 'EvaStone supplies independent boutiques and goldsmiths in DE, AT, UK and CH — not end customers. One channel for every order, one named contact, a process that does not cost you time.',
                'whyKick' => 'Why EvaStone',
                'whyTitle' => 'What the customer won’t find three streets away.',
                'whyBody' => 'Smooth polished silver is available everywhere — whoever stocks it competes with everyone else who stocks it. EvaStone’s irregular surface structure is the difference that decides in passing: visible under raking light, in your customer’s photo, in the window.',
                'howKick' => 'How ordering works',
                'howTitle' => 'One channel, one status, no guesswork.',
                'steps' => [
                    ['Order through the portal', 'One channel, one source of truth. We enter phone orders into the portal for you.'],
                    ['Confirmation in 5 minutes', 'Automatic acknowledgement in German or English.'],
                    ['Delivery date within 48 hours', 'Ewa or Rafał confirm the date personally — no bot.'],
                    ['Status visible at any time', 'Production, picking, dispatch with tracking number — in the portal, without asking.'],
                    ['Materials from the first delivery', 'Starter and content package: photos and DE/EN copy for your window and social.'],
                    ['Re-order in two clicks', 'From a single piece, full size range, straight from your order history.'],
                ],
                'termsKick' => 'Terms',
                'termsTitle' => 'Enter in sets, restock by the single piece.',
                'terms' => [
                    'First order from 4–5 sets',
                    'Re-order from 1 piece, full size range',
                    'Two price lists: EUR (Europe), USD for the rest of the world',
                    'Starter and content package from the first delivery',
                    'White-label shop after two to three orders',
                    'No discounting — premium competes on value, not price',
                ],
                'termsNote' => 'Binding terms are confirmed in writing with the offer.',
                'fitKick' => 'Clarity before the first conversation',
                'fitYes' => 'A fit', 'fitNo' => 'Not a fit',
                'yes' => [
                    'Independent boutiques and goldsmiths with their own clientele',
                    'Windows that should differ from the chain store down the street',
                    'Houses that start with sets and then re-order individually',
                    'Owners who decide for themselves what goes in the window',
                ],
                'no' => [
                    'Chains and central purchasing',
                    'Anyone after a range of thousands of lines with rigid minimum batches',
                    'Anyone who needs goods without a handwriting of their own',
                    'Anyone selling online only, without their own floor',
                ],
                'portalTitle' => 'Already a partner?',
                'portalBody' => 'Catalogue, availability and ordering in the B2B portal.',
                'portalBtn' => 'Enter the B2B portal',
                'soon' => 'The portal opens for you once your account is set up. Register below — we reply within two business days.',
                'formKick' => 'Become a partner',
                'formTitle' => 'Tell us about your shop.',
                'formBody' => 'Five fields are enough for us to give you a useful answer. A person replies within two business days — no sequence, no automation.',
                'fShop' => 'Shop / boutique', 'fPerson' => 'Contact person', 'fEmail' => 'Email',
                'fWeb' => 'Website', 'fCity' => 'City', 'fCountry' => 'Country (DE, AT, UK, CH …)',
                'fSubmit' => 'Send enquiry', 'fConsent' => 'We use your details solely to answer this enquiry.',
                'optional' => 'optional',
            ],
            'pl' => [
                'kick' => 'B2B — Dla handlu',
                'title' => 'Dozamówienie w dwa kliknięcia. Witryna gotowa.',
                'lead' => 'EvaStone zaopatruje niezależne butiki i złotników w DE, AT, UK i CH — nie klientów końcowych. Jeden kanał na każde zamówienie, jedna osoba do kontaktu, proces, który nie zabiera czasu.',
                'whyKick' => 'Dlaczego EvaStone',
                'whyTitle' => 'To, czego klientka nie znajdzie trzy ulice dalej.',
                'whyBody' => 'Gładko polerowane srebro jest wszędzie takie samo — kto je ma, konkuruje z każdym, kto też je ma. Nieregularna struktura powierzchni EvaStone to różnica, która rozstrzyga w przejściu: widoczna pod światłem, na zdjęciu klientki, w witrynie.',
                'howKick' => 'Jak wygląda zamówienie',
                'howTitle' => 'Jeden kanał, jeden status, bez zgadywania.',
                'steps' => [
                    ['Zamówienie przez portal', 'Jeden kanał, jedno źródło prawdy. Zamówienia telefoniczne wpisujemy do portalu za Ciebie.'],
                    ['Potwierdzenie w 5 minut', 'Automatyczne potwierdzenie po niemiecku lub angielsku.'],
                    ['Termin dostawy w 48 godzin', 'Ewa lub Rafał potwierdzają termin osobiście — bez automatu.'],
                    ['Status widoczny w każdej chwili', 'Produkcja, kompletacja, wysyłka z numerem przesyłki — w portalu, bez pytania.'],
                    ['Materiały od pierwszej dostawy', 'Pakiet startowy i contentowy: zdjęcia i opisy DE/EN do witryny i social.'],
                    ['Dozamówienie w dwa kliknięcia', 'Od jednej sztuki, pełny zakres rozmiarów, prosto z historii zamówień.'],
                ],
                'termsKick' => 'Warunki',
                'termsTitle' => 'Wejście w setach, dozamówienie w pojedynczej sztuce.',
                'terms' => [
                    'Pierwsze zamówienie od 4–5 setów',
                    'Dozamówienie od 1 sztuki, pełny zakres rozmiarów',
                    'Dwa cenniki: EUR (Europa), USD dla reszty świata',
                    'Pakiet startowy i contentowy od pierwszej dostawy',
                    'Sklep white-label po dwóch–trzech zamówieniach',
                    'Bez rabatów — premium konkuruje wartością, nie ceną',
                ],
                'termsNote' => 'Wiążące warunki potwierdzamy pisemnie wraz z ofertą.',
                'fitKick' => 'Jasność przed pierwszą rozmową',
                'fitYes' => 'Pasuje', 'fitNo' => 'Nie pasuje',
                'yes' => [
                    'Niezależne butiki i złotnicy z własną klientelą',
                    'Witryny, które mają różnić się od sieciówki na tej samej ulicy',
                    'Sklepy, które wchodzą w sety i potem dozamawiają pojedynczo',
                    'Właściciele decydujący sami, co trafia do witryny',
                ],
                'no' => [
                    'Sieci i zakupy centralne',
                    'Kto szuka asortymentu z tysięcy pozycji ze sztywnym minimum',
                    'Kto potrzebuje towaru bez własnego charakteru',
                    'Kto sprzedaje wyłącznie online, bez własnej powierzchni',
                ],
                'portalTitle' => 'Jesteś już partnerem?',
                'portalBody' => 'Katalog, dostępność i zamówienia w portalu B2B.',
                'portalBtn' => 'Wejdź do portalu B2B',
                'soon' => 'Portal otworzy się dla Ciebie po założeniu konta. Zarejestruj się poniżej — odpowiadamy w ciągu dwóch dni roboczych.',
                'formKick' => 'Zostań partnerem',
                'formTitle' => 'Opowiedz nam o swoim sklepie.',
                'formBody' => 'Pięć pól wystarczy, byśmy odpowiedzieli sensownie. Odezwie się człowiek w ciągu dwóch dni roboczych — bez sekwencji, bez automatu.',
                'fShop' => 'Sklep / butik', 'fPerson' => 'Osoba kontaktowa', 'fEmail' => 'E-mail',
                'fWeb' => 'Strona www', 'fCity' => 'Miasto', 'fCountry' => 'Kraj (DE, AT, UK, CH …)',
                'fSubmit' => 'Wyślij zapytanie', 'fConsent' => 'Dane wykorzystamy wyłącznie do odpowiedzi na to zapytanie.',
                'optional' => 'opcjonalnie',
            ],
        ][$l];

        return $c + ['portal' => $portal] + $this->links($l);
    }

    /* ---------------------------------------------------------------- Kontakt */

    private function contentKontakt(string $l): array
    {
        $a = config('evastone.address');
        $c = [
            'de' => [
                'kick' => 'Kontakt', 'title' => 'Sprechen Sie mit uns.',
                'lead' => 'Für Boutiquen, Presse und Messetermine. Wir melden uns innerhalb von zwei Werktagen — keine Automaten, eine Person mit Namen.',
                'boutiqueTitle' => 'Sie führen eine Boutique?', 'boutiqueBody' => 'Der schnellste Weg führt über das Formular für Händler.', 'boutiqueBtn' => 'Konditionen ansehen',
                'wearTitle' => 'Sie möchten ein Stück tragen?', 'wearBody' => 'Wir verkaufen nicht direkt. Finden Sie die nächste Verkaufsstelle.', 'wearBtn' => 'Verkaufsstelle finden',
                'directTitle' => 'Direkt', 'mailLabel' => 'E-Mail', 'addrLabel' => 'Anschrift', 'phoneLabel' => 'Telefon',
                'pressTitle' => 'Presse', 'pressBody' => 'Für Bildmaterial, Interviews und Messeakkreditierung schreiben Sie bitte direkt an die Redaktionsadresse.',
            ],
            'en' => [
                'kick' => 'Contact', 'title' => 'Talk to us.',
                'lead' => 'For boutiques, press and trade-fair dates. We reply within two business days — no bots, a person with a name.',
                'boutiqueTitle' => 'Do you run a boutique?', 'boutiqueBody' => 'The fastest way is the retailer form.', 'boutiqueBtn' => 'View terms',
                'wearTitle' => 'Would you like to wear a piece?', 'wearBody' => 'We do not sell directly. Find your nearest stockist.', 'wearBtn' => 'Find a stockist',
                'directTitle' => 'Direct', 'mailLabel' => 'Email', 'addrLabel' => 'Address', 'phoneLabel' => 'Phone',
                'pressTitle' => 'Press', 'pressBody' => 'For images, interviews and trade-fair accreditation, please write directly to the editorial address.',
            ],
            'pl' => [
                'kick' => 'Kontakt', 'title' => 'Porozmawiaj z nami.',
                'lead' => 'Dla butików, prasy i terminów targowych. Odpowiadamy w ciągu dwóch dni roboczych — bez automatów, osoba z imieniem.',
                'boutiqueTitle' => 'Prowadzisz butik?', 'boutiqueBody' => 'Najszybciej przez formularz dla sklepów.', 'boutiqueBtn' => 'Zobacz warunki',
                'wearTitle' => 'Chcesz nosić egzemplarz?', 'wearBody' => 'Nie sprzedajemy bezpośrednio. Znajdź najbliższy punkt sprzedaży.', 'wearBtn' => 'Znajdź punkt sprzedaży',
                'directTitle' => 'Bezpośrednio', 'mailLabel' => 'E-mail', 'addrLabel' => 'Adres', 'phoneLabel' => 'Telefon',
                'pressTitle' => 'Prasa', 'pressBody' => 'W sprawie materiałów zdjęciowych, wywiadów i akredytacji targowej pisz bezpośrednio na adres redakcyjny.',
            ],
        ][$l];

        return $c + ['addr' => $a] + $this->links($l);
    }

    /* --------------------------------------------------------------- wspólne */

    /** Zlokalizowane odnośniki między stronami marki. */
    private function links(string $l): array
    {
        return [
            'lStore' => ['de' => '/de/haendlerkarte/', 'en' => '/en/stockists/', 'pl' => '/pl/mapa-dystrybutorow/'][$l],
            'lB2b' => "/{$l}/wholesale/",
            'lContact' => ['de' => '/de/kontakt/', 'en' => '/en/contact/', 'pl' => '/pl/kontakt/'][$l],
            'lGallery' => '/'.$l.'/'.config("evastone.catalog_sections.{$l}").'/',
            'lAtelier' => "/{$l}/atelier/",
        ];
    }
}
