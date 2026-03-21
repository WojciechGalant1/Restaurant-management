1. Architektura aplikacji webowych

1.1 „Approaches to Reducing REST API Response Time in High-Traffic Banking Systems” – R. A. Deshpande

Cel: Celem artykułu jest identyfikacja i porównanie metod redukcji opóźnień krytycznych endpointów API przy wolumenach rzędu setek milionów zapytań miesięcznie, tak aby osiągnąć akceptowalne metryki p50 i p95 w ułamkach sekundy. Autor podkreśla, że niewielkie zmiany rzędu 0,1 s istotnie wpływają na konwersję i zaufanie klientów bankowych.  

Porównywane elementy: Praca obejmuje trzy główne warstwy optymalizacji: architektoniczną, protokołową i aplikacyjną.  
•	Architektura: monolit vs. mikroserwisy z API Gateway (podział domen, contention a liczba hopów sieciowych); wzorce odporności Circuit Breaker i Bulkhead.
•	Protokół i payload: HTTP/1.1 vs. Keep-Alive, HTTP/2, ALPN; kompresja gzip vs. Brotli; redukcja rozmiaru payloadu JSON.
•	Buforowanie: trzy poziomy cache – CDN brzegowy, Redis in-memory, HTTP caching (Cache-Control, ETag, Last-Modified).
•	Baza danych: PgBouncer, indeksowanie, repliki RDS, różne strategie shardingu (range / hash / hybrydowy).
•	Asynchroniczność i równoległość: wątki wirtualne (JEP 444), batchowanie w Kafka, CQRS + Event Sourcing.

Metryki: Rozkład czasów odpowiedzi (p50, p95, p99) przy wysokim obciążeniu; rozmiar payloadu i natężenie ruchu sieciowego (MB/s); przepustowość producenta (operacje/s); udział odczytów trafiających na węzeł master; zużycie CPU.

Wyniki: 
Warstwa architektury
•	Przy właściwym doborze protokołów i service discovery mikroserwisy skracają medianę czasu odpowiedzi o ok. 25% względem monolitu dzięki równoległej skalowalności i izolacji awarii.
•	Circuit Breaker obniża p99 z setek do dziesiątek milisekund, eliminując kaskadowe ponowienia prób (retry).
•	Bulkhead utrzymuje wykorzystanie CPU puli powyżej 85% bez istotnego wzrostu opóźnień; współdzielona pula prowadzi do wykładniczego wzrostu timeoutów.
Warstwa protokołu i payloadu
•	Włączenie kompresji gzip typowo zmniejsza payload o 60–80%; przejście z gzip na Brotli daje dodatkowe ok. 20%.
•	W infrastrukturze Verdigris wymuszenie gzip zredukowało ruch o 30–70% i obniżyło medianę czasu ładowania raportów o kilkadziesiąt milisekund.
•	HTTP/2 z multiplexingiem i HPACK przyspiesza ładowanie wielu małych obiektów nawet 6,1× względem HTTP/1.1; korzyść odczuwa 80% serwisów, szczególnie w sieciach mobilnych.
•	ALPN eliminuje dodatkowy RTT na negocjację protokołu, co jest istotne przy transakcjach międzykontynentalnych.
Buforowanie
•	Edge CDN obniżył średni czas odpowiedzi do 23 ms i zmniejszył ruch do origin o ok. 65%.
•	Pojedynczy Redis obsługuje 1,2 mln operacji/s przy opóźnieniu poniżej 1 ms.
•	20–30% żądań stanowią warunkowe GET z odpowiedzią 304, co eliminuje przesyłanie ciała odpowiedzi.
Baza danych i przechowywanie
•	Odpowiednio dobrane indeksy skracają czas wykonania zapytań nawet o 70%.
•	Repliki odczytowe RDS mogą obniżyć medianę opóźnia odczytów o 30–50% i zredukować udział operacji trafiających na węzeł master do 30%.
•	Hybrydowy sharding (range + hash) utrzymuje czas odpowiedzi ok. 90 ms, sharding oparty wyłącznie na zakresach – 135 ms, a jednolity hashing – powyżej 180 ms przy tym samym obciążeniu.
Asynchroniczność i równoległe przetwarzanie
•	Wątki wirtualne pozwalają utrzymać miliony połączeń przy zaniedbywalnm narzucie na przełączanie kontekstu, redukując p95 do jednocyfrowych milisekund.
•	Batchowanie w Kafka (batch 200 komunikatów) zwiększa przepustowość producenta do 50 MB/s; bez batchowania spada ona o rząd wielkości.
•	CQRS + Event Sourcing w systemie płatności natychmiastowych utrzymuje wysoką przepustowość i stabilnie niską latencję dzięki braku blokad i izolacji modeli odczytu/zapisu.

Wnioski: Najwyraźniej udokumentowane zyski wydajnościowe przynoszą: przejście z monolitu na mikroserwisy z API Gateway, Circuit Breaker i Bulkhead (−25% mediany, silna poprawa p99); HTTP/2 + ALPN + Keep-Alive + kompresja (kilkadziesiąt ms mniej, nawet 6,1× szybsze ładowanie wielu obiektów); trójpoziomowe buforowanie (CDN ∼23 ms, spadek ruchu o 60–65%); optymalizacja bazy danych (redukcja czasu zapytań o 30–70%, utrzymanie ok. 90 ms pod dużym obciążeniem); wątki wirtualne + batchowanie + CQRS (dalsza redukcja p95 do jednocyfrowych ms przy skrajnych wolumenach).

1.2 „Exploring The Model-View-Controller (MVC) Architecture: A Broad Analysis of Market and Technological Applications” – S. Necula

Cel: Systematyczny przegląd literatury i bibliometryczna analiza badań nad architekturą Model‑View‑Controller (MVC) w latach 1996-2023. Koncentruje się na tym, gdzie i jak MVC jest stosowane (technologie, rynki), jak rozwijały się tematy badawcze i jakie nowe trendy (MVVM, AI/ML) się pojawiają.

Zakres analizy: Pytania badawcze dotyczą zmienności implementacji MVC w różnych językach i platformach, wpływu MVC na architekturę i wzorce projektowe oraz rynków najczęściej stosujących ten wzorzec.
•	Zakres technologiczny: Java,.NET, JavaScript, Python, Ruby; web, mobile (zwłaszcza iOS), aplikacje korporacyjne, e‑commerce, finanse, narzędzia edukacyjne.  
•	Zakres rynkowy: sektor technologiczny, edukacja, e‑commerce, usługi finansowe.

Metodologia i miary: Systematyczne wyszukiwanie w bazach Scopus, Web of Science, SpringerLink i ACM; kryteria włączenia: artykuły recenzowane, wprost dotyczące MVC. Analiza bibliometryczna (Bibliometrix/Biblioshiny) obejmuje: liczbę dokumentów, cytowań i czasopism; tempo wzrostu liczby publikacji; sieci współautorstwa i współpracy krajów; mapy współwystępowania słów kluczowych oraz analizę ewolucji tematów i trendów.

Wyniki: 
Rozkład technologii i tematów
•	Java wykazuje najwyższą centralność w sieci słów kluczowych, co świadczy o jej dominacji w badaniach i implementacjach MVC.
•	MVC jest szeroko stosowane we frameworkach: Spring MVC, ASP.NET MVC, Struts2 + Spring + Hibernate, a także Angular, React i Vue po stronie frontendu.
•	Sektory e-commerce i finansów szczególnie doceniają MVC za integralność danych, bezpieczeństwo i elastyczność warstwy interfejsu.
Ewolucja tematów i trendy
•	Szczyt zainteresowania MVC przypadl na lata 2009–2013, związany z rozwojem frameworków webowych i mobilnych.
•	Widać wyraźne przesunięcie ku MVVM i stosowi MERN w obszarze aplikacji SPA i interfejsów czasu rzeczywistego.
Rośnie integracja modelu MVC z ML.NET, TensorFlow i Scikit-Learn w środowiskach ASP.NET, Spring Boot i Ruby on Rails, co służy usprawnieniu przetwarzania danych i personalizacji.

Wnioski: Artykuł nie porównuje frameworków metrykami wydajności, lecz zestawia pola zastosowań, języki, rynki i tematy badawcze wokół MVC. Wzorzec pozostaje szeroko stosowanym i adaptowalnym fundamentem, lecz w obszarze interfejsów czasu rzeczywistego i aplikacji jednostronicowych (SPA) rośnie rola MVVM oraz stosów opartych na Node.js/JavaScript. Równocześnie coraz powszechniejsze staje się włączanie narzędzi AI/ML do warstwy modelu. Modyfikacje MVC (rozszerzenia dla pervasive computing, wizualizacji 3D) lepiej odpowiadają specyficznym potrzebom domenowym niż „czyste” MVC.
 
1.3 „Performance Evaluation of a Modern Web Architecture”, – J. Lundar, T.-M. Grønli

Cel: Analiza architektury nowoczesnych aplikacji webowych w kontekście komunikacji dwukierunkowej i czasu rzeczywistego. Autorzy proponują połączenie WebSocket + Node.js + JavaScript full-stack jako wydajne i skalowalne rozwiązanie; budują własny prototyp i porównują go z wcześniejszymi podejściami (Comet/long polling, Jetty).

Porównywane elementy: 
•	Serwery HTTP: Jetty (model wątkowy) vs. Node.js (model zdarzeniowy).
•	Kanały dwukierunkowe: CometD long polling vs. CometD WebSocket vs. własny prototyp WebSocket + Node.js (bez Bayeux).
•	Architektury push: REST + techniki push (CometD, Bayeux, long polling) vs. czysty WebSocket.
Metryki: 
•	Średnni czas odpowiedzi serwera przy rosnącej liczbie równoległych żądań (ms).
•	Szczytowe zużycie CPU procesu (%).
•	Ilość przesłanych danych (B) dla 15 wiadomości (wysyłanie, odbiór, heartbeat) – mierzona tcpdump.

Wyniki: 
Serwery – Jetty vs. Node.js
•	Test: dwa serwery zwracające tą samą stronę HTML; obciążenie generowane przez Apache JMeter; monitorowanie CPU przez Syrupy.
•	Node.js osiąga znacznie niższy średni czas odpowiedzi niż Jetty dla 1000–12 500 równoległych żądań (np. poniżej 10 ms dla 5000 żądań vs. ≥62 ms dla Jetty przy 1000).
•	Maksymalne zużycie CPU: Node.js ok. 47,9% vs. Jetty 118,3% – Node.js zużywa co najmniej dwukrotnie mniej zasobów.
Kanał dwukierunkowy – CometD vs. WebSocket + Node.js
•	Test: klient wysyła co 10 s łącznie 15 wiadomości; mierzone całkowite dane na łączu.
•	CometD long polling: 27 492 B.
•	CometD WebSocket: 11 985 B.
•	Prototyp WebSocket + Node.js (bez Bayeux, zoptymalizowany JSON): 4 858 B.
•	Long polling zużywa ponad 5,6× więcej danych niż własny prototyp; dużą część ruchu stanowią żądania „trzymające” połączenie (POST/heartbeat bez danych użytkownika).
Architektura prototypu
•	Full-stack JavaScript (Node.js + klient): wspólny kod, łatwiejszy podział logiki między serwer a klienta.
•	Model zdarzeniowy Node.js upraszcza obsługę wielu równoległych połączeń WebSocket i eliminuje złożoność wielowątkowości.
•	Na kliencie: rozbudowany wzór ModelViewPresenter z dedykowanymi kontrolerami dla dwukierunkowego przepływu oraz Web Worker do parsowania JSON w tle.

Wnioski: Połączenie WebSocket + Node.js + JavaScript po obu stronach daje niższe czasy odpowiedzi, mniejsze zużycie CPU oraz znacznie mniejszy ruch sieciowy niż wcześniejsze rozwiązania oparte na long pollingu i klasycznych serwerach wątkowych. Proponowana architektura zwiększa elastyczność, ułatwia projektowanie aplikacji czasu rzeczywistego i lepiej wpisuje się w nowoczesne standardy sieciowe. 

1.4 „Scalability and Performance Optimization in Web Application Development” – A. S. Shethiya

Cel: Przegląd literatury i praktyk inżynierskich dotyczących skalowalności i optymalizacji wydajności aplikacji webowych. Autor omówia wzorce architektoniczne, mechanizmy buforowania, optymalizację baz danych, sieci dostarczania treści (CDN) oraz rolę przetwarzania w chmurze, konteneryzacji i mikroserwisów. Praca nie zawiera własnych eksperymentów ani pomiarów – jest syntezą poglądów z literatury.

Porównywane elementy: 
•	Architektura monolityczna vs. mikroserwisy: monolit staje się wąskim gardłem przy skalowaniu aplikacji; mikroserwisy dzielą ją na niezależne komponenty komunikujące się przez API.
•	Skalowanie pionowe vs. poziome: skalowanie pionowe polega na zwiększaniu zasobów jednego serwera (CPU, RAM), poziome – na rozkładaniu obciążenia na wiele serwerów.
•	Techniki optymalizacji: buforowanie (caching), CDN, optymalizacja baz danych, load balancing, optymalizacja API, optymalizacja frontendu, architektura serverless oraz przetwarzanie asynchroniczne.




Metryki (koncepcyjne, bez własnych pomiarów): 
•	Czas ładowania i opóźnienie (latency): optymalizacja wydajności skupia się na poprawie prędkości i efektywności aplikacji poprzez skracanie czasów odpowiedzi i usprawnianie pobierania danych.
•	Współczynnik konwersji: według przywoływanej literatury nawet jednosekundowe opóźnienie ładowania strony może znacznie obniżyć konwersję.
•	Dostępność i odporność na awarie: load balancing zwiększa redundancję i zapobiega przestojom.
•	Narzędzia do pomiaru: Google Lighthouse, New Relic, APM.

Wyniki: 
Architektura i skalowanie
•	Mikroserwisy z kontenerami zapewniają skalowalny i efektywny sposób budowania aplikacji webowych, oferując ukierunkowane skalowanie, elastyczność i krótsze cykle wytwarzania oprogramowania.
•	Kubernetes automatyzuje wdrażanie, skalowanie i zarządzanie aplikacjami kontenerowymi, zapewniając wysoką dostępność i optymalizację zasobów.
•	Architektura serverless pozwala na automatyczne skalowanie bez konieczności ręcznego zarządzania infrastrukturą.
Optymalizacja wydajności
•	Buforowanie jest jedną z najbardziej efektywnych technik skracania czasów odpowiedzi i poprawy prędkości ładowania.
•	CDN zmniejsza opóźnienia, dostarczając statyczne treści z serwerów zlokalizowanych bliżej użytkownika.
•	Optymalizacja bazy danych (indeksy, optymalizacja zapytań, bazy NoSQL) poprawia szybkość zapytań i skalowalność.
•	GraphQL, przetwarzanie asynchroniczne, minifikacja, kompresja, lazy loading i HTTP/2/3 pozytywnie wpływają na wydajność frontendu i API.
Ograniczenia i wyzwania
•	Skalowanie zwiększa koszty operacyjne – konieczne jest wyważenie efektywności kosztowej i zysków wydajnościowych.
•	Mikroserwisy komplikują bezpieczeństwo, monitoring i debugowanie, wymagając API gateway’ów, distributed tracingu i segmentacji sieci.

Podsumowanie: Artykuł jest syntezą praktyk inżynierskich, promuje mikroserwisy, kontenery, load balancing, buforowanie, CDN oraz optymalizację baz danych i frontendu jako klucz do skalowalnych i wydajnych aplikacji webowych. Nie zawiera własnych pomiarów ani porównań liczbowych; zestawia koncepcyjnie podejścia i wskazuje strategie uznawane za skuteczniejsze (np. mikroserwisy vs. monolit), jednocześnie podkreślając koszty, złożoność i wymagania bezpieczeństwa jako główne wyzwania.
 
1.5 „Scalable and Efficient Backend Development with Node.js: Architecture, Performance, and use Cases” – V. Krishna

Cel: Ocena efektywności Node.js jako platformy backendowej od strony architektury, wydajności, ekosystemu i zastosowań praktycznych. Artykuł łączy przegląd literatury z własnymi eksperymentami porównawczymi, w których Node.js zestawiono z Django i Spring Boot.
Hipotezy: 
•	H1: Node.js działa lepiej niż Django i Spring Boot przy dużej współbieżności i obciążeniu I/O.
•	H2: Node.js zużywa mniej pamięci.
•	H3: Wyzwania Node.js w aplikacjach korporacyjnych są mniejsze niż korzyści wynikające ze skalowalności i ekosystemu.
Porównywane technologie: 
•	Node.js + Express.js
•	Django + Gunicorn
•	Spring Boot (Java)
Metodologia: Trzy REST API (JSON, MySQL na AWS RDS), uruchomione na identycznych instancjach EC2 (t3.medium). Testy przeprowadzono narzędziem JMeter przy 100, 500 i 1000 równoczesnych użytkownikach.
Metryki: 
•	Średnne opóźnienie (latency, ms).
•	Liczba żądań na sekundę (throughput).
•	Zużycie pamięci (MB).
•	Zużycie CPU (%).
Wyniki: 
•	Node.js osiąga najniższe opóźnienia, najwyższy throughput i najmniejsze zużycie pamięci we wszystkich poziomach obciążenia.
•	Zużycie CPU w Node.js sięga ok. 85% przy 1000 użytkownikach, co sygnalizuje potencjalne wąskie gardło przy zadaniach CPU-intensywnych.
•	Firmy stosujące Node.js w produkcji (m.in. PayPal) raportują 35-procentowy spadek średniego czasu odpowiedzi i dwukrotny wzrost liczby żądań na sekundę po przejściu z Javy.
Główne ograniczenia Node.js (na podstawie literatury i doświadczeń inżynierów): 
•	Trudność w utrzymaniu złożonej asynchroniczności (callback hell).
•	Słabość przy zadaniach CPU-intensywnych (np. przetwarzanie obrazów).
•	Ryzyka związane z zależnościami npm.
Sposoby łagodzenia ograniczeń: 
•	Zastosowanie async/await, Promise i TypeScript redukuje problem callback hell.
•	Worker threads i clustering umożliwiają efektywne wykorzystanie wielu rdzeni w zadaniach CPU-bound.
Wnioski: W typowych, I/O-intensywnych backendach REST Node.js wyraźnie przewyższa Django i Spring Boot pod względem opóźnienia, przepustowości i zużycia pamięci przy tym samym sprzęcie i obciążeniu. Zyski te są potwierdzane w wdrożeniach produkcyjnych. Wada Node.js pojawia się przy zadaniach CPU-intensywnych i złożonej asynchroniczności, jednak dostępne narzędzia i wzorce projektowe znacząco ograniczają te problemy. 










2. Charakterystyka badanych technologii

2.1 Laravel i ekosystem PHP

2.1.1 „A comparative analysis of performance optimization techniques for benchmarking PHP frameworks: Laravel and Codeigniter.” – M. K. Ahmed, A. H. Bello, S. S. Jauro, M. Dawaki

Cel: Porównanie technik optymalizacji wydajności w dwoch popularnych frameworkach PHP - Laravel i CodeIgniter poprzez benchmarking pod kątem czasu odpowiedzi, przepustowości i przetwarzania danych, w celu wskazania, który framework lepiej nadaje się do różnych typów aplikacji (lekkie vs. złożone).

Porównywane elementy: 
•	Laravel: buforowanie tras i widoków (route/view caching), optymalizacja zapytań bazodanowych, eager loading, refaktoryzacja kodu.
•	CodeIgniter: buforowanie stron i zapytań (page/query caching), indeksowanie bazy danych, dobre praktyki kodowania.

Metodologia: Frameworki dobrano według popularności, jakości dokumentacji oraz cech wydajnościowych i skalowalności. Testy przeprowadzono narzędziami: Apache JMeter (testy obciążeniowe i stresowe), Xdebug (profilowanie i identyfikacja wąskich gardeł), New Relic (monitorowanie w czasie rzeczywistym). Scenariusze testowe: 1 użytkownik / 1 iteracja, 10 użytkowników / 10 iteracji, 2 użytkowników / 1000 iteracji, identyczne dla obu frameworków.

Wyniki: 
Czas odpowiedzi (przed i po optymalizacji)
Scenariusz	Laravel (przed optym.)	Laravel (po optym.)	CodeIgniter (przed optym.)	CodeIgniter (po optym.)
1 użytk. / 1 iter.	119 ms	94 ms	29 ms	17 ms
10 użytk. / 10 iter.	122 ms	80 ms	25 ms	18 ms
2 użytk. / 1000 iter.	86 ms	–	14 ms	–
Tabela 1. Porównanie średnich czasów odpowiedzi [ms] przed i po optymalizacji

Przepustowość
Scenariusz	Laravel (rps)	CodeIgniter (rps)
1 użytk. / 1 iter.	21,22	56,34
10 użytk. / 10 iter.	21,70	76,54
2 użytk. / 1000 iter.	8,22	10,38
Tabela 2. Porównanie przepustowości [rps] w trzech scenariuszach obciążenia 
Stabilność: Oba frameworki uzyskały 0,000% błędów, co świadczy o wysokiej niezawodności obu rozwiązań.

Wnioski: CodeIgniter osiąga niższe czasy odpowiedzi i wyższą przepustowość we wszystkich scenariuszach jest zatem lepszym wyborem dla lekkich, prostych aplikacji nastawionych na szybkość. Laravel jest wolniejszy, lecz techniki optymalizacji dają znaczne zyski (szczególnie przy wielu iteracjach), a bogaty ekosystem i rozbudowane narzędzia czynią go lepszym wyborem dla złożonych, skalowalnych aplikacji.

2.1.2 „Comparative Analysis of PHP Frameworks for Development of Academic Information System Using Load and Stress Testing” – A. Niarman, Iswandi, A. K. Candri

Cel: Zbadanie, czy do budowy systemu informacji akademickiej lepiej użyć czystego PHP, CodeIgniter czy Laravel, oceniając wydajność przy różnych poziomach obciążenia (load testing i stress testing).

Porównywane elementy: Trzy podejścia zaimplementowane na identycznym module „student” z taką samą strukturą tabeli i funkcjami (wyszukiwanie, generowanie, edycja, kasowanie, upload zdjęcia, eksport CSV):
•	Pure PHP z ORM ActiveRecord.
•	CodeIgniter z wbudowanym ORM Active Record.
•	Laravel z Eloquent ORM.

Metodologia: Czas wykonania funkcji mierzono funkcją microtime(); zużycie pamięci – memory_get_usage(). Testy obciążeniowe i stresowe przeprowadzono narzędziem JMeter przy 50–500 użytkowników (load testing) oraz 600–2000 użytkowników (stress testing).

Metryki: 
•	Czas wykonania funkcji (ms).
•	Zużycie pamięci (MB).
•	Średnni czas odpowiedzi (s) i odchylenie standardowe z testów JMeter.
•	Throughput (KB/s).

Wyniki: 
Czas wykonania funkcji
•	CodeIgniter jest najszybszy w większości operacji (3–17 ms).
•	Pure PHP jest prawie zawsze drugi, jednak radzi sobie bardzo słabo przy generowaniu 1000 rekordów (589,85 ms vs. 17,26 ms w CodeIgniter).
•	Laravel jest najwolniejszy, szczególnie przy ładowaniu i generowaniu danych (ok. 120–130 ms).
•	Wyjątek: upload zdjęcia – Pure PHP osiąga najlepszy wynik (0,34 ms).

Zużycie pamięci
•	Pure PHP zużywa najmniej pamięci w prawie wszystkich funkcjach (np. wyszukiwanie: ok. 2 MB vs. 3,5 MB CodeIgniter vs. ok. 9,5 MB Laravel).
•	CodeIgniter jest nieznacznie droższy pamięciowo od Pure PHP, różnice zazwyczaj poniżej 1 MB.
•	Laravel zużywa wyraźnie najwięcej pamięci: 6,7–11,9 MB w zależności od operacji
Testy JMeter
Użytkownicy	Typ testu	Śr. czas odp. [s]	Throughput [KB/s]	Najstabilniejszy
50–500	Load	CI: ~33–36, PHP: ~76–95, Laravel: ~100–130	CI najwyższy	CodeIgniter
600–2000	Stress	CI: 36, PHP: ~95, Laravel: ~140	CI najwyższy	CodeIgniter
Tabela 3. Zbiorcze wyniki testów JMeter – porównanie trzech podejść

Wnioski: CodeIgniter wygrywa w tym studium przypadku: osiąga najniższy czas odpowiedzi, najwyższą przepustowość i najlepszą stabilność. Pure PHP jest najlżejsze i najoszczędniejsze pamięciowo, lecz wykazuje słabość przy masowym generowaniu danych; autorzy rekomendują je dla małych i średnich aplikacji. Laravel jest najbardziej zasobochłonny, ale oferuje najbogatszy ekosystem i jest bardziej odpowiedni dla większych projektów korporacyjnych, gdzie zasoby sprzętowe są mniej krytyczne niż bogactwo funkcji.
 
2.1.3 „Comparative Study: Performance of MVC Frameworks on RDBMS”  – M. H. Rahman, M. Naderuzzaman, M. A. Kashem, B. M. Salahuddin, Z. Mahmud

Cel: Porównanie wydajności trzech frameworków PHP MVC (CodeIgniter, Laravel, Phalcon) przy pracy z relacyjnymi bazami danych MySQL i PostgreSQL na dużym zbiorze danych (1 mln rekordów) w podstawowych operacjach CRUD. Autorzy dążą do wskazania optymalnej kombinacji framework + RDBMS dla aplikacji webowych.

Porównywane elementy: 
•	Frameworki: CodeIgniter, Laravel, Phalcon.
•	Bazy danych: MySQL 8.0, PostgreSQL 10.20.
•	Operacje: insert, select, update, delete (CRUD) na tabeli testowej z 1 000 000 rekordów.

Metodologia: 100 iteracji × 10 000 operacji na losowo generowanych danych; dla każdej „porc ji” mierzono czas wykonania, a następnie liczono średnią.

Metryki: 
•	Średnni czas wykonania (mean) dla każdej operacji, frameworka i bazy danych.
•	Wartości minimalne, maksymalne, odchylenie standardowe i wariancja czasów.

Wyniki: 
Operacja	CI / MySQL	CI / PgSQL	Laravel / MySQL	Laravel / PgSQL	Phalcon / PgSQL*
Insert	śr.	śr.	śr.	śr.	najniższy
Select	1,60 s	–	3,23 s	–	0,98 s (MySQL)
Update	–	–	–	–	46,64 s (PgSQL) 207,82 s (MySQL)
Delete	–	–	–	–	najniższy
Tabela 4. Porównanie średnich czasów wykonania operacji CRUD [s] dla wybranych kombinacji framework + RDBMS

•	Operacje insert i delete: Phalcon uzyskuje najniższe średnie czasy zarówno w MySQL, jak i w PostgreSQL.
•	Operacja select: w MySQL Phalcon jest wyraźnie szybszy (0,98 s vs. 1,60 s dla CodeIgniter i 3,23 s dla Laravel).
•	Operacja update: Phalcon w MySQL jest tu paradoksalnie najwolniejszy (207,82 s), natomiast w PostgreSQL osiąga najlepszy wynik (46,64 s) – co wskazuje na istotną rolę doboru systemu bazodanowego.

Wnioski: Najlepszą kombinacją ogólnie jest Phalcon + PostgreSQL – framework ten osiąga najniższe średnie czasy dla trzech na cztery operacji CRUD. Dobrór systemu bazodanowego ma istotny wpływ na wyniki: ta sama operacja może dawać skrajnie różne czasy w zależności od pary framework + RDBMS. Autorzy wskazują Phalcon + PostgreSQL jako preferowąną konfigurację przy projektowaniu wydajnych aplikacji webowych korzystających z RDBMS.
 
2.1.4 „Empirical Study of Most Popular PHP Framework” – H. Abutaleb, A. Tamimi, T. Alrawashdeh

Cel: Porównanie najpopularniejszych frameworków PHP opartych na wzorcu MVC pod kątem wydajności w typowych warunkach webowych, w celu wsparcia programistów w wyborze właściwego narzędzia.

Porównywane elementy: Siedem frameworków PHP: Laravel, CodeIgniter, Symfony, Symfony2, Phalcon, CakePHP, Yii. Artykuł agreguje i zestawia wyniki kilku wcześniejszych benchmarków przeprowadzonych w różnych środowiskach sprzętowo-programowych.

Metodologia: Testy obciążeniowe HTTP (m.in. Apache Benchmark ab) z dużą liczbą żądań (scenariusze: „hello world”, ładowanie danych z tabeli) na różnych konfiguracjach serwerów (Linux/Ubuntu, Windows; różne konfiguracje CPU/RAM). Wyniki zestawiono na wykresach porównawczych.

Metryki: 
•	Liczba zapytań na sekundę (requests per second, RPS).
•	Czas odpowiedzi (ms).
•	Zużycie pamięci (KB).
•	Liczba ładowanych plików przez framework.



Wyniki: 
Framework	RPS	Czas odp. (ms)	Pamięć (KB)	Liczba plików
Laravel	~3000 ✓ najwyższy	–	518 ✓ najniższa	26
Phalcon	–	3,1 ✓ najniższy	530	10 ✓ najmniej
CodeIgniter	–	–	725	22
Symfony	–	–	1711	15
Symfony2	–	–	1586	301
CakePHP	–	–	2824 najwyższa	37
Tabela 5. Porównanie wybranych metryk dla siedmiu frameworków PHP (✓ = najlepsza wartość w kategorii)

•	RPS: Laravel osiąga najwyższą liczbę obsłużonych żądań na sekundę (∼3000), wyprzedzając pozostałe frameworki.
•	Czas odpowiedzi: Phalcon ma najniższy czas obsługi pojedynczego żądania (3,1 ms).
•	Liczba ładowanych plików: Phalcon ładuje najmniej plików (10), Symfony2 zdecydowanie najwięcej (301).
•	Pamięć: Laravel i Phalcon są najoszczędniejsze (odpowiednio 518 i 530 KB); CakePHP zużywa najwięcej (2824 KB).

Wnioski: Laravel przoduje w przepustowości (RPS) i zużyciu pamięci, Phalcon w czasie odpowiedzi i minimalizacji liczby ładowanych plików. Autorzy podkreślają, że wyboru frameworka nie należy dokonywać wyłącznie na podstawie metryk wydajności: równorzędne znaczenie mają jakość dokumentacji, dojrzałość ekosystemu i wielkość społeczności – a w tym obszarze Laravel ma znaczącą przewagę.

2.1.5 „Study on MVC Framework for Web Development in PHP” ¬– S. Khan i A. T. Khanam

Cel: Przegląd wzorca Model-View-Controller (MVC) w PHP oraz porównanie popularnych frameworków MVC, ze szczególnym naciskiem na CodeIgniter i Laravel, pod kątem architektury, wydajności, bezpieczeństwa i wpływu na doświadczenie użytkownika. Praca ma charakter przeglądu literatury z analizą opisową – bez formalnych eksperymentów liczbowych.

Porównywane elementy: Głównie CodeIgniter vs. Laravel (wspomniany również Symfony) w następujących aspektach:
•	Architektura i narzut systemowy.
•	Wydajność bazodanowa: Active Record (CodeIgniter) vs. Eloquent ORM (Laravel).
•	Mechanizmy buforowania (cache).
•	Routing i middleware.
•	Złożoność i bogactwo funkcji vs. lekkość frameworka.
•	Bezpieczeństwo (ochrona przed SQL injection, XSS) i jakość UX.
Metodologia: Przegląd literatury – autorzy odwołują się do wcześniejszych badań porównawczych nad frameworkami PHP/MVC, badaniami nad bezpieczeństwem oraz wpływem MVC na doświadczenie użytkownika. Analizę uzupełniają przykładowe fragmenty kodu (CodeIgniter i Laravel), ilustrujące różnice w stylu kodowania – nie są to jednak sformalizowane benchmarki.

Metryki: Wyłącznie jakościowe (bez pomiarów liczbowych):
•	Wydajność i zużycie zasobów: czasy ładowania, zużycie pamięci, uproszczona obsługa.
•	Złożoność i produktywność programisty: bogactwo narzędzi Laravela vs. prostota CodeIgniter.
•	Bezpieczeństwo i jakość UX: omawiane opisowo na podstawie literatury.

Wyniki: 
•	CodeIgniter wykazuje lekką, uproszczoną architekturę o niskim zużyciu pamięci i krótkich czasach ładowania – preferowany przy maksymalnym nacisku na prędkość i minimalne zużycie zasobów.
•	Laravel oferuje szeroki zespół narzędzi i komponentów (Eloquent ORM, rozbudowany cache, routing, middleware), co czyni go lepszym wyborem dla dużych, złożonych aplikacji.
•	Brak jednoznacznego zwycięzcy wydajnościowego: autorzy zaznaczają, że żaden framework nie jest kategorycznie szybszy ani wolniejszy – rzeczywista wydajność zależy od optymalizacji bazy danych, konfiguracji serwera i buforowania, wymagając testów dopasowanych do konkretnego projektu.
•	MVC jako wzorzec: literatura wskazuje na poprawę utrzymywalności, skalowalności, jakości UX i bezpieczeństwa (m.in. redukcja podatności na SQL injection i XSS dzięki separacji warstw).

Wnioski: Artykuł jest przeglądem koncepcyjnym pokazującym, że wybór frameworka MVC w PHP to kompromis: CodeIgniter oferuje lekkość i prostotę, Laravel – bogactwo funkcji i wsparcie dla dużych aplikacji. Brak ilościowych benchmarków; porównanie opiera się na cechach architektonicznych, dostępnych narzędziach i analizie literatury. Ostateczny wybór powinien zależeć od skali i złożoności projektu, priorytetu szybkości vs. bogatej funkcjonalności oraz dostępnych zasobów zespołu.
 





















2.2 NestJS i ekosystem Node.js

2.2.1 „Bridging Monolith and Microservices: A Modular Monolith Architecture for Scalable RESTful Applications Using NestJS” – A. Arroziqi, M. Susanty

Cel: Ocena efektywności architektury modularnego monolitu (Modular Monolith) jako alternatywy dla tradycyjnego monolitu przy budowie małych i średnich aplikacji webowych z użyciem NestJS. Artykuł wypełnia lukę badawczą dotyczącą modularnych monolitów w ekosystemie JavaScript/Node.js.

Porównywane elementy: Dwie architektury wdrożone w aplikacji Tahsin App (mała platforma e-learningowa):
•	Tradycyjny monolit: pojedyncza jednostka wdrożeniowa z minimalną separacją modułową.
•	Modularny monolit: wewnętrzne granice modułowe oparte na separacji domenowo-usługowej; zaimplementowany w NestJS z zasadami architektury heksagonalnej (Ports and Adapters), z czterema warstwami: domeny, aplikacji, infrastruktury i interfejsu.
Metodologia: Studium przypadku podzielone na cztery etapy: przygotowanie systemu, implementacja architektury, testowanie jakości i analiza statystyczna. Narzędzia: SonarQube (statyczna analiza kodu), Apache JMeter (testy obciążeniowe), Python/SciPy (niezależne testy t dla istotności statystycznej), Postman (walidacja API). Jakość oprogramowania oceniana według modelu ISO/IEC 25010.
Metryki: 
•	Utrzymywalność: Współczynnik Długu Technicznego (TDR), ocena SonarQube (A–E), liczba wykrytych problemów.
•	Złożoność kodu: złożoność cyklomatyczna i kognitywna.
•	Wydajność: średni czas odpowiedzi (ms), przepustowość (req/s), współczynnik błędów (%).
•	Skalowalność: efektywność i stabilność pod zwiększonym obciążeniem, wykorzystanie CPU (%).
Wyniki: 
Metryka	Tradycyjny monolit	Modularny monolit (NestJS)
Dług techniczny (TDR)	11,5%	4,5% (−32%)
Ocena SonarQube	C	A
Liczba problemów	223	272 (większość drobnych)
Złożoność cyklomatyczna	341	1152 (↑)
Złożoność kognitywna	165	172 (stabilna)
Przepustowość (500 VU, POST)	podstawa	+100% (799,75 req/s)
Współczynnik błędów (1000 VU)	niższy	24,29% (wąskie gardło)
Zużycie CPU (słabszy sprzęt)	duże wahania	<50%, stabilne
Tabela 1. Porównanie tradycyjnego monolitu i modularnego monolitu (NestJS) według kluczowych metryk (na podstawie badania Tahsin App)
Utrzymywalność
•	TDR spadł z 11,5% do 4,5% (redukcja o 32% szacowanego wysiłku), ocena SonarQube poprawiła się z C do A.
•	Wzrost liczby problemów (223 → 272) wynika głównie z drobnych ostrzeżeń o znikomy wpływie na profil utrzymywalności; struktura modułowa skutecznie zmniejszyła redundancję kodu.
Złożoność kodu
•	Złożoność cyklomatyczna wzrosła znacząco (341 → 1152) – typowy efekt separacji odpowiedzialności.
•	Złożoność kognitywna pozostaje stabilna (165 → 172), co oznacza, że wysiłek umysłowy potrzebny do zrozumienia kodu nie wzrósł.
Wydajność i skalowalność
•	Żądania sekwencyjne: przy 500 użytkownikach modularny monolit osiągnął ponad 100% poprawę przepustowości (do 799,75 req/s) i znacząco niższe opóźnienia. Przy 1000 użytkowników współczynnik błędów wynosi 24,29%, sygnalizując początek wąskich gardeł.
•	Żądania równoległe (GET-heavy): tradycyjny monolit przewyższa modularny pod względem czasu odpowiedzi i przepustowości, co wskazuje, że ścisła integracja monolitu sprzyja szybszemu pobieraniu danych przy intensywnych odczytach.
•	CPU (słabszy sprzęt – Lenovo): modularny monolit utrzymuje zużycie poniżej 50% z niskimi wahaniami; tradycyjny monolit wykazuje większe fluktuacje.

Wnioski: Modularny monolit z NestJS oferuje wyraźne korzyści w utrzymywalności i stabilności, czyniąc go zrównoważoną alternatywą dla małych i średnich systemów dążących do skalowalności bez pełnego narzutu mikroserwisów. Tradycyjny monolit może jednak przewyższać wersję modułową w scenariuszach intensywnego odczytu danych. Wzrost złożoności cyklomatycznej jest równoważony przez stabilną złożoność kognitywną i lepszą strukturę kodu.

2.2.2 „Comparative Analysis of Node.js Frameworks” – B. Zima, M. Barszcz

Cel: Porównanie dwóch popularnych frameworków Node.js – ExpressJS i NestJS – pod kątem wydajności aplikacji, metryk jakości kodu, dokumentacji oraz popularności i wsparcia społeczności.

Porównywane elementy: Dwie aplikacje CRUD (z JWT i PostgreSQL) napisane od podstaw z minimalną liczbą zewnętrznych bibliotek, o identycznej funkcjonalności.

Metodologia: Testy wydajnościowe przy 1 użytkowniku (100 prób, średnni czas CRUD dla żądań autoryzowanych i nieautoryzowanych) oraz testy obciążeniowe (120 000 użytkowników, 3 × 40 000), mierzone JMeterem. Analiza metryk kodu (liczba plików i linii dla jednej operacji GET) oraz jakościowa ocena dokumentacji i popularności (Stack Overflow, GitHub).

Metryki: 
•	Liczba plików i linii kodu dla jednej operacji.
•	Średnni czas odpowiedzi (ms) dla operacji CRUD przy 1 użytkowniku.
•	Średnni czas odpowiedzi i procent błędów przy dużym obciążeniu (120 000 VU).
•	Liczba wątków na StackOverflow i repozytoriów na GitHubie (popularność).
•	Subiektywna ocena jakości dokumentacji.
Wyniki: 
Kryterium	ExpressJS	NestJS
Architektura	Minimalistyczna, brak narzuconej struktury	Modułowa, DI, ORM, silne typowanie
Rozmiar kodu	Mniej plików i linii	Więcej zasobów
Wydajność (1 użytkownik)	Szybszy (GET/POST/DELETE)	Nieznacznie wolniejszy
Odporność (duże obciążenie)	✔ Lepsza (mniej błędów)	Wysoki odsetek błędów przy autoryzacji
Dokumentacja	Prosta, wystarczająca	✔ Szczegółowa, kompleksowa
Popularność (SO/GitHub)	✔ Zdecydowanie wyższa	Rosnąca
Zastosowanie	Proste, szybkie systemy	Duże, złożone projekty
Tabela 2. Porównanie ExpressJS i NestJS według głównych kryteriów (na podstawie Zima & Barszcz)

Wydajność
•	Pojedynczy użytkownik: ExpressJS jest z reguły szybszy w operacjach GET, POST i DELETE; różnice rzędu milisekund są praktycznie nieodczuwalne dla użytkownika końcowego.
•	Duże obciążenie: ExpressJS jest bardziej odporny – przetwarza procentowo znacznie więcej żądań; NestJS przy autoryzacji wykazuje wysoki odsetek błędów, co czyni go nieptymalnym przy ekstremalnym obciążeniu.

Dokumentacja i popularność
•	NestJS oferuje bardziej szczegółową i kompleksową dokumentację z przykładami i regularnymi aktualizacjami.
•	ExpressJS dominuje pod względem popularności na Stack Overflow i GitHubie; NestJS wciąż rośnie, lecz startował znacznie później.

Wnioski: ExpressJS jest lepszym wyborem dla prostych, szybkich systemów o mniejszej złożoności i wysokich wymaganiach wydajnościowych. NestJS, mimo większego narzutu i gorszej odporności przy ekstremalnym obciążeniu, zapewnia uporządkowaną architekturę, lepszą dokumentację i większy komfort pracy w dużych, złożonych projektach wymagających długoterminowego utrzymania


2.2.3  „Evaluating the Performance of the Node.js Frameworks Express, Fastify, and NestJS in Modern Cloud Environments” – I. Alasmar

Cel: Ocena i porównanie wydajności trzech frameworków Node.js – Express, Fastify i NestJS – w środowisku serverless (AWS Lambda) pod kątem zastosowania w nowoczesnych, wysokoobciążonych API.

Porównywane elementy: Trzy frameworki Node.js jako osobne funkcje AWS Lambda z identycznymi zasobami (Node.js 22.14, 1024 MB RAM). Zakres ograniczono do backendu API – bez baz danych ani zewnętrznych usług.

Metodologia: Testy obciążeniowe narzędziem Artillery w trzech scenariuszach: 100 VU (lekki load), 1000 VU (Średni), 10 000 VU (ciężki); operacja POST /users z identycznym payloadem. Monitoring przez AWS CloudWatch.

Metryki: 
•	Zimny start (Init Duration).
•	Latencja: Średnia i p95 (ms).
•	Przepustowość (req/s).
•	Stabilność: procent błędów 5xx i timeoutów.
•	Zużycie pamięci i czas wykonania (CloudWatch).

Wyniki: 
Scenariusz	Express	Fastify	NestJS
Zimny start (init)	✔ Najniższy	Średni	Najwyższy
Pamięć	✔ Najniższa	Średnia	Najwyższa
100 VU (lekki load) – latencja	✔ Najniższa, 0 błędów	Stabilna	Stabilna
1000 VU (średni) – avg+p95	Wysoki % błędów	Wysoki % błędów	✔ Najniższa latencja, najwyższy RPS (~95% błędów)
10 000 VU (ciężki) – załamanie	Najgorszy (latencja+RPS)	✔ Najniższa latencja wśród udanych	✔ Najwyższy throughput
Tabela 3. Porównanie Express, Fastify i NestJS w AWS Lambda według scenariuszy obciążenia (na podstawie Alasmar; ✔ = najlepsza wartość w kategorii)

•	Express: najniższy zimny start i zużycie pamięci; świetny przy lekkim obciążeniu (najniższa latencja, brak błędów), lecz szybko traci wydajność i osiąga najgorsze parametry przy bardzo dużym ruchu.
•	Fastify: dobry kompromis szybkości i skalowania – lepszy niż Express przy dużym obciążeniu; przy 10 000 VU osiąga najniższą latencję wśród udanych żądań.
•	NestJS: najwyższy narzut na zimny start i pamięć, lecz najstabilniejszy przy Średnim i dużym obciążeniu; osiąga najwyższy throughput przy 10 000 VU.
•	Przy 1000 i 10 000 VU wszystkie frameworki wykazują bardzo wysoki odsetek błędów (∼95–99%), co świadczy o ograniczeniach środowiska AWS Lambda, a nie konkretnych frameworków.

Wnioski: Brak jednego wyraźnego zwycięzcy: Express wygrywa prostotą i lekkością przy małym ruchu, Fastify jest dobrym kompromisem między wydajnością a skalowalnością, a NestJS – mimo wyższego kosztu zasobowęgo – najlepiej utrzymuje stabilność i throughput przy rosnącym obciążeniu w AWS Lambda.

2.2.4 „ NestJS vs Express.js: A Comprehensive Comparison” – J. J. Munirov
 
Cel: Opisowe omówienie różnic między Express.js i NestJS z punktu widzenia architektury, typowych zastosowań, zalet i wad oraz kierunków rozwoju społeczności. Praca ma charakter przeglądowy, nie raportuje eksperymentów ani pomiarów wydajności.

Porównywane elementy: Express.js i NestJS w następujących aspektach: koncepcje architektoniczne, obsługa wstrzykiwania zależności (DI), narzędzia CLI, krzywa uczenia się, typowe zastosowania, zalety i wady.

Metodologia: Analiza jakościowa na podstawie dokumentacji, źródeł literatury i doświadczeń społeczności. Główny wynik zaprezentowano w formie tabeli porównawczej kluczowych różnic (Key Differences).

Metryki: Wyłącznie jakościowe – brak benchmarków, pomiarów czasu odpowiedzi ani zużycia pamięci.

Wyniki: 
Architektura i cechy
•	Express.js: minimalistyczny framework bez narzuconej struktury; daje dużą swobodę projektu, łatwy start i niski narzut.
•	NestJS: modułowa, opiniową architektura inspirowana Angularem - z wbudowanym DI, dekoratorami, CLI i obsługą TypeScript „out-of-the-box”.
Zastosowania – co wypada lepiej
•	Express.js: lekkie API, szybkie prototypowanie, projekty z niestandardową architekturą, mikroserwisy bez dużej struktury.
•	NestJS: aplikacje enterprise ze złożoną logiką, GraphQL, projekty wymagające modularności, łatwej konserwacji i silnego typowania.

Zalety i wady
•	Express.js: zalety – łatwy start, bogaty ekosystem, minimalizm; wady - brak domyślnej architektury, ręczna konfiguracja zaawansowanych funkcji.
•	NestJS: zalety – skalowalna architektura, DI, testowalność, TypeScript; wady - wyższa złożoność i nieznacznie większy narzut przy małych aplikacjach.

Wnioski: Express.js jest lepszy dla małych, szybkich i elastycznych projektów, NestJS – dla dużych, złożonych systemów wymagających długoterminowego utrzymania i wyraźnej struktury. Artykuł nie zawiera pomiarów ilościowych; wnioski opierają się wyłącznie na analizie architektonicznej i przeglądzie literatury.


2.2.5 „Node.js Performance Benchmarking and Analysis at VirtualBox, Docker, and Podman Environment Using Node-Bench Method” – I P. A. E. Pratama, I M. S. Raharja

Cel: Zbadanie, które połączenie środowisko uruchomieniowe + framework Node.js + biblioteka dostępu do bazy danych daje najlepsze parametry wydajnościowe (liczba obsłużonych żądań, latencja, throughput). Autorzy wskazują lukę badawczą: brak porównań środowiska wirtualnego z kontenerowym dla frameworków Node.js.

Porównywane elementy: 
•	Środowiska uruchomieniowe: VirtualBox (wirtualizacja), Docker i Podman (kontenery).
•	Frameworki Node.js: Adonis, Connect, Express, Fastify, Foxify, Hapi, Koa, Molecular, Plumier, Restify, Sails.
•	Biblioteki bazodanowe: ORM Sequelize vs. zapytania surowe (raw query) Mysql2; uzupełniająco: Bookshelf, Knex, MySQL, MySQL2.

Metodologia: Metoda Node-Bench z NPM skupiona na wydajności aplikacji (nie protokołów sieciowych). Sprzęt: laptop Dell i7-6500U, 16 GB RAM, Ubuntu 22.04 LTS. Kroki: przygotowanie środowisk, instalacja frameworków i bibliotek, uruchomienie testów dla każdej kombinacji framework:biblioteka w trzech środowiskach, zapis i uśrednianie wyników.

Metryki: 
•	Liczba obsłużonych żądań (requests).
•	Opóźnienie (latency).
•	Przepustowość (throughput).

Wyniki: 
Środowiska – VirtualBox vs. kontenery
•	Kontenery (szczególnie Podman) osiągają wyższą wydajność niż wirtualizacja VirtualBox.
Frameworki i biblioteki
•	W środowisku VirtualBox Sequelize (ORM) wykazuje ok. 7× lepszą wydajność niż Mysql2 (raw query) dla większości frameworków (Connect, Express, Fastify, Foxify, Hapi).
•	Wyjątek: dla Adonis Mysql2 jest prawie 2× lepszy od Sequelize.
•	Najlepsza kombinacja ogółem: Fastify + Sequelize w środowisku kontenerowym (Docker/Podman) – najwyższa liczba żądań i throughput, najniższa latencja.
•	Najgorsza kombinacja: Express + Mysql2 na VirtualBox – najniższa wydajność spośród wszystkich testowanych konfiguracji.

Wnioski: Wybór środowiska uruchomieniowego ma duży wpływ na wydajność aplikacji Node.js. Autorzy rekomendują kontenery (Docker/Podman) i Fastify z Sequelize jako optymalną konfigurację, odradzając VirtualBox z Mysql2 jako zdecydowanie najmniej wydajne rozwiązanie.
 
2.2.6 „Node.js: Event Driven Concurrency for Web Applications” – G. Iyer
 
 Cel: Porównanie dwóch głównych architektur współbieżności w serwerach WWW – wielowątkowej i zdarzeniowej – oraz przedstawienie Node.js jako praktycznej realizacji modelu zdarzeniowego, rozwiązującej problem skalowalności przy bardzo dużej liczbie równoległych połączeń (problem C10k).

Porównywane elementy: 
•	Serwery wielowątkowe: model proces-per-połączenie i wątek-per-połączenie (np. Apache).
•	Serwery zdarzeniowe: jeden wątek z pętlą zdarzeń obsługujący wiele połączeń równocześnie (Node.js).
Metodologia: Przegląd teoretyczny oparty na wcześniejszych pracach o współbieżności (threads vs. events, C10k, SEDA) oraz dokumentacji i badaniach nad Node.js i silnikiem V8. Uzupełnienie stanowią przykłady kodu: prosty serwer „Hello World” i strumieniowy serwer plików HTTP, ilustrujące rejestrację callbacków zamiast blokującego I/O.

Metryki / kryteria porównania: 
•	Przepustowość (throughput) przy rosnącej liczbie równoległych zadań.
•	Degradacja wydajności przy dużym obciążeniu.
•	Zużycie zasobów: pamięć na stosy wątków, koszt przełączeń kontekstu.
•	Możliwość obsługi bardzo dużej liczby równoległych połączeń (C10k).

Wyniki: 
Serwery wielowątkowe
•	Oferują prosty, sekwencyjny model programowania, lecz słabo skalują się przy dużej liczbie połączeń: istnieje graniczna liczba wątków T’, powyżej której przepustowość spada istotnie.
•	Główne problemy: duże zużycie pamięci na stosy wątków i kosztowne przełączanie kontekstu.
Architektura zdarzeniowa / Node.js
•	Jeden wątek z pętlą zdarzeń obsługuje wiele połączeń jednocześnie, radykalnie redukując liczbę wątków i eliminując nadmiarowe przełączanie kontekstu.
•	Przepustowość serwera zdarzeniowego przekracza serwer wielowątkowy i nie degraduje się przy rosnącej współbieżności w tym samym scenariuszu testowym.
•	Node.js implementuje ten model przez: pojedynczy proces, asynchroniczne I/O, wbudowaną pętlę zdarzeń (libev) i JavaScript sprzyjający callbackom i closures. Architektura opiera się na silniku V8 i bibliotece libeio (asynchroniczne wywołania POSIX).
Ograniczenia i wyzwania Node.js
•	Asynchroniczne programowanie zdarzeniowe utrudnia debugowanie: ślad stosu nie odzwierciedla przepływu sterowania.
•	Brak luźnego sprzężenia między serwerem HTTP a aplikacją wskazywany jako wada architektoniczna.
•	Platforma była względnie młoda w chwili pisania artykułu, co stanowiło ryzyko przy wdrożeniach produkcyjnych.

Wnioski: Dla bardzo współbieżnych aplikacji webowych architektura zdarzeniowa wykazuje lepszą skalowalność, stabilność przy dużym obciążeniu i efektywniejsze wykorzystanie zasobów niż klasyczne serwery wielowątkowe. Node.js jest przedstawiony jako konkretna i praktyczna realizacja tego modelu z wbudowaną pętlą zdarzeń i asynchronicznym I/O – choć z zastrzeżeniami dotyczącymi złożoności programowania asynchronicznego.
 
2.3 Mapowanie obiektowo-relacyjne 

2.3.1 „Analysis of ORM Framework Approaches for Node.js” – S. Zhadko-Bazilevych

Cel: Eksperymentalne porównanie wydajności trzech ORM dla Node.js: Sequelize, Prisma i TypeORM, w różnych trybach pracy z bazą PostgreSQL (zapytania pojedyncze z/bez cache, obciążenie równoległe), w celu oceny narzutu ORM, jakości generowanych zapytań SQL i przydatności w typowych scenariuszach aplikacji webowej (sklep internetowy).

Porównywane elementy: Trzy ORM: Sequelize, Prisma, TypeORM, w kontekście różnych scenariuszy:
•	Proste operacje CRUD (Create/Read/Update/Delete na użytkowniku).
•	Złożone odczyty (lista produktów z filtrowaniem, sortowaniem i paginacją; odczyt zamówienia z pozycjami przez JOIN).
•	Złożone zapisy i transakcje (tworzenie zamówienia, potwierdzenie zamówienia w transakcji).
•	Struktury hierarchiczne (drzewo komentarzy – model Adjacency List).
•	Porównanie strategii generowania zapytań SQL: JOIN, dwa zapytania sekwencyjne, zagnieżdżone podzapytanie.

Metodologia: Dla każdego endpointu i trybu wysyłano 1000 zapytań w trzech trybach: single cached (cache PostgreSQL zachowany), single uncached (cache czyszczony między zapytaniami), parallel (50 równoległych zapytań do docelowej liczby). Główna metryka – czas wykonania zapytania – była rozbita na: czas tworzenia SQL, czas wysyłki/odbioru oraz czas wykonania w bazie. Autor porównał również z ręcznie napisanym SQL i użył EXPLAIN (ANALYZE) do oceny planów wykonania.

Metryki: 
•	Czas tworzenia zapytania SQL (ms).
•	Czas wysyłki i odbioru odpowiedzi (ms).
•	Czas wykonania zapytania w bazie danych (ms).

Wyniki: 
Scenariusz	Sequelize	Prisma	TypeORM	Uwagi
Read (cached)	✔ Dobry	−3 0% wolniejsza	≈ Sequelize	Prisma generuje więcej SQL
Read (uncached)	✔ Najszybszy	~3× wolniejsza	Średni	Duża różnica Prisma
Read (parallel 50)	Najwolniejszy	✔ Najszybsza	Średni	Pula połączeń Prismy
Create/Update/Delete (single)	✔ Najszybszy	Wolniejsza	Średni	
Create/Delete (parallel)	Najwolniejszy	✔ Najszybsza	Średni	Prisma skaluje się lepiej
Złożone odczyty (JOIN)	LEFT OUTER JOIN (1 zapytanie)	2 zapytania sekwencyjne	Zagnieżdżone podzapytanie (✔ najgorszy)	Strategia SQL wpływa na wynik
Transakcje	Duże narzuty przy parallel	Średnie	Najwolniejszy (koszt gen. SQL)	
Hierarchie (drzewa)	Adjacency List	Adjacency List	✔ Closure Table, Nested Set, MP	TypeORM ma bogatszą obsługę
Tabela 4. Porównanie Sequelize, Prisma i TypeORM w różnych scenariuszach i trybach testowych

Proste CRUD
•	Sequelize jest najszybszy dla pojedynczych, mniej obciążonych zapytań; Prisma wykazuje wyraźne osłabienie przy uncached reads (prawie 3× wolniejsza od Sequelize).
•	Przy dużej równoległości (parallel) Prisma staje się najszybsza – lepsza obsługa puli połączeń.
Złożone odczyty
•	READ ORDER (JOIN dwóch tabel): Sequelize generuje jedno zapytanie LEFT OUTER JOIN, Prisma – dwa zapytania sekwencyjne, TypeORM – zagnieżdżone podzapytanie. TypeORM ma najdłuższy czas wykonania SQL i generacji w każdym trybie.
•	Dla złożonych joinów przy dużej liczbie rekordów Sequelize i Prisma wypadają lepiej; konkretna strategia generowania SQL ma znaczący wpływ na wyniki TypeORM.
Złożone zapisy i transakcje
•	W trybie parallel czas wykonania Sequelize jest ok. 3× dłuższy niż Prismy przy tworzeniu zamówienia.
•	TypeORM jest najwolniejszy przy potwierdzeniu zamówienia głównie przez koszt generacji SQL; Prisma pod równoległym obciążeniem również wykazuje widoczny wzrost czasu.
Struktury hierarchiczne
•	TypeORM wyróżnia się obsługą zaawansowanych modeli drzew (Closure Table, Nested Set, Materialized Path), co daje mu przewagę przy głębokich hierarchiach; Sequelize i Prisma obsługują tylko model Adjacency List.

Wnioski: Sequelize sprawdza się najlepiej dla prostych, pojedynczych zapytań CRUD; Prisma ma lepszą skalowalność i stabilność pod równoległym obciążeniem; TypeORM jest najbardziej „zbalansowany” bez dramatycznych regresji, z bogatą obsługą transakcji i hierarchii, lecz bywa wolniejszy przy złożonych zapytaniach z JOIN-ami.

2.3.2 „Comparison of Performance Between Raw SQL and Eloquent ORM in Laravel” – H. Halimi

Cel: Porównanie wydajności Eloquent ORM i surowego SQL (Raw SQL) w Laravelu na przykładzie aplikacji blogowej, w celu ustalenia, w jakim stopniu warstwa abstrakcji danych wpływa na czas wykonania operacji bazodanowych przy rosnącej skali danych i złożoności zapytań.

Porównywane elementy: 
Techniki dostępu do bazy: Eloquent ORM vs. Raw SQL w Laravelu.
Operacje: INSERT (1000–10 000 rekordów), UPDATE (1000–10 000 rekordów), SELECT z 1, 3 i 4 JOIN-ami.

Metodologia: Aplikacja blogowa z 5 tabelami (users, articles, tags, comments, article_tag) uruchomiona na dedykowanym serwerze z bazą MySQL. Pomiar czasu żądania narzędziem Laravel Debugbar, generowanie danych – Faker, znaczniki czasu – Carbon. Każda operacja wykonywana w pętli w ramach jednego żądania; Średnia z 3 prób.

Metryki: 
•	Czas odpowiedzi (ms) – Średnia z 3 prób.
•	Odchylenie standardowe.

Wyniki: 
Operacja	Raw SQL	Eloquent ORM	Różnica	Uwagi
INSERT (10 000 rekordów)	✔ Szybszy	Wolniejszy	~1,5 s	Narzut tworzenia obiektów
UPDATE (10 000 rekordów)	✔ Szybszy	Wolniejszy	Mniejsza niż INSERT	2 pola – mniejszy narzut
SELECT + 1 JOIN	✔ Szybszy	Porównywalna	Niewielka	
SELECT + 3 JOIN	✔ 155,2 ms	~1540 ms (~10× wolniejszy)	~10×	ORM generuje wiele SQL
SELECT + 4 JOIN	✔ Szybszy	Wielokrotnie wolniejszy	Duża	
Tabela 5. Porównanie Raw SQL i Eloquent ORM w Laravelu według operacji

•	INSERT: Raw SQL zawsze szybszy; Średnia różnica ok. 1,5 s przy 10 000 rekordów. Narzut Eloquent pochodzi z tworzenia obiektów modeli i mapowania obiekt–relacja.
•	UPDATE: Raw SQL szybszy we wszystkich iteracjach; różnica rośnie wraz z liczbą rekordów, lecz jest mniejsza niż przy INSERT (aktualizowane są tylko dwa pola).
•	SELECT + 1 JOIN: różnica nieznaczna; Eloquent porónywalny z Raw SQL.
•	SELECT + 3 JOIN: Eloquent jest ok. 10× wolniejszy (∼1540 ms vs. 155,2 ms). ORM generuje wiele złożonych zapytań zamiast jednego JOIN-a.
•	SELECT + 4 JOIN: różnica jeszcze bardziej wzrasta na niekorzyść Eloquent.

Wnioski: Pod względem czystej wydajności Raw SQL w Laravelu wyraźnie przewyższa Eloquent ORM przy masowych insertach, update’ach i złożonych SELECT-ach z wieloma JOIN-ami. Eloquent ORM przynosi natomiast duże zyski w ergonomii i utrzymaniu kodu, bezpieczeństwie i czytelności – dlatego autorzy rekomendują jego stosowanie w prostych, mniej krytycznych wydajnościowo częściach aplikacji, a Raw SQL tam, gdzie przetwarza się duże ilości danych lub wymagane są złożone, optymalizowane zapytania.
 







3. Analiza istniejących badań porównawczych

3.1 „Performance Comparison Between Laravel and ExpressJs Framework Using Apache JMeter”, Mangapul Siahaan, R. W. Wijaya

 Cel: Ustalenie, który z dwóch popularnych frameworków – Laravel (PHP) lub Express.js (Node.js) – lepiej sprawdza się przy budowie RESTful API obsługującego wielu równoległych użytkowników odczytujących dane z bazy MySQL.

Porównywane elementy: 
•	Laravel 10.25.2 (PHP) vs. Express.js na Node.js 18.12.0.
•	Zakres: wyłącznie operacja HTTP GET na jednym endpoincie zwracającym do 1000 rekordów studentów przy rosnącej liczbie równoległych użytkowników (100–1000 VU).
•	Środowisko: dwa osobne serwery VPS DigitalOcean o identycznej specyfikacji (1 CPU, 25 GB SSD), Nginx, MySQL 8.0.34.

Metodologia: Testy obciążeniowe przy użyciu Apache JMeter 5.6.2 (symulacja wirtualnych użytkowników, pomiar czasu odpowiedzi i błędów) z wtyczkami JMeter do monitorowania CPU i RAM. Dane testowe: 100–1000 rekordów studentów wprowadzonych ręcznie do MySQL.

Metryki: 
•	Średnni czas odpowiedzi API (ms) przy 100–1000 VU.
•	Zużycie CPU (%) i pamięci RAM (%) na serwerze.
•	Kryteria akceptacji: czas odpowiedzi < 5 s przy 1000 VU; CPU i RAM < 75%.

Wyniki: 
Metryka	Laravel	Express.js
Średnni czas odpowiedzi (100–1000 VU)	✔ 1 745,7 ms	10 855,1 ms
Średnnie zużycie CPU (%)	✔ Niższe	Wyższe
Średnnie zużycie RAM (%)	✔ Niższe	Wyższe
Stabilność przy dużym obciążeniu	✔ Lepsza	Mniej stabilny
Spełnienie kryteriów (<5 s, <75% CPU/RAM)	✔ Tak	Nie
Tabela 1. Porównanie wydajności Laravel i Express.js przy GET /api/mahasiswa/1000

•	Laravel osiąga średni czas odpowiedzi 1 745,7 ms – ponad 6-krotnie krótszy niż Express.js (10 855,1 ms) w tym samym zakresie obciążenia.
•	Express.js jest mniej stabilny przy dużej liczbie jednoczesnych żądań i nie spełnia kryterium akceptacji dla 1000 VU.
•	Laravel osiąga niższe średnie zużycie CPU i RAM, co świadczy o efektywniejszym wykorzystaniu zasobów serwera w tym scenariuszu.
Uwaga: wyniki są specyficzne dla jednego scenariusza GET na dużym zbiorze danych z MySQL. Autorzy rekomendują Express.js dla aplikacji z mniejszym równoległym ruchem.
Wnioski: W badanym scenariuszu (RESTful API GET, MySQL, duża współbieżność) Laravel wyraźnie przewyższa Express.js pod względem czasu odpowiedzi i efektywności zasobowej. Autorzy rekomendują Laravel dla systemów o dużej liczbie jednoczesnych użytkowników i wysokim obciążeniu RESTful API, a Express.js – dla prostszych zastosowań z mniejszym współbieżnym ruchem 

 3.2 „Comparative Analysis for Web Development Performance in Node.JS and Python Technologies” – A. Muttemwar, Y. Likhar, R. Bagade

Cel: Opracowanie wytycznych dla deweloperów i organizacji przy wyborze technologii backendowej (Node.js vs. Python) w projektach webowych, z uwzględnieniem wydajności, skalowalności, współbieżności i zużycia zasobów.

Porównywane elementy: Dwie technologie i powiązane z nimi frameworki:
•	Node.js z frameworkiem Express.
•	Python z frameworkiem Flask.
•	Główne obszary porównania: czas wykonania i szybkość obsługi żądań; model współbieżności i asynchroniczności; skalowalność; zużycie zasobów (CPU, RAM, wątki); dopasowanie do typu zadań (I/O-bound vs. CPU-bound); bezpieczeństwo i ekosystem.

Metodologia: Przegląd i integracja istniejących benchmarków i case studies (m.in. Netflix/PayPal dla Node.js; Instagram/Dropbox dla Pythona). Autorzy nie opisują własnego środowiska testowego; prezentują tabelaryczne zestawienie metryk z zewnętrznych źródeł.

Metryki: 
•	Czas odpowiedzi i liczba żądań na sekundę (HTTP benchmarki).
•	Współbieżność: obsługa tysięcy równoległych połączeń.
•	Zużycie CPU, RAM i liczba wątków.
•	Skalowalność przy dużym ruchu.

Wyniki: 
Kryterium	Node.js (Express)	Python (Flask)
Współbieżność / asynchroniczność	✔ Event-loop, non-blocking I/O	GIL, domyślnie synchroniczny
Obsługa równoległych żądań (I/O-bound)	✔ Tysiące req/s	Niższy
Skalowalność pozioma	✔ Lepsza (mikroserwisy)	Wymaga dodatkowej infrastruktury
Zużycie zasobów (CPU/RAM)	✔ Efektywniejsze	Wyższe
Zadania CPU-intensive (ML/AI)	Słabsze	✔ Lepsze (NumPy, TensorFlow itp.)
Bezpieczeństwo (wbudowane)	Wymaga uwagi przy zależnościach	✔ Więcej wbudowanych mechanizmów
Prostota i krzywa uczenia	Średnia	✔ Prostsza, łatwiejsza nauka
Tabela 2. Porównanie Node.js (Express) i Pythona (Flask) według głównych kryteriów

Node.js (Express) wypada lepiej:
•	Obsługa dużej liczby równoległych żądań dzięki modelowi event-loop i nieblokującemu I/O (real-time, chat, streaming, API, WebSockets).
•	Skalowalność pozioma i efektywność zasobowa przy wysokim obciążeniu.
Python (Flask) wypada lepiej:
•	Zadania obliczeniowe (ML, AI, przetwarzanie danych i obrazów) dzięki ekosystemowi bibliotek (NumPy, TensorFlow itp.).
•	Prostota tworzenia, łatwość nauki, budowa mniejszych API bez dużej współbieżności; wbudowane mechanizmy bezpieczeństwa.

Wnioski: Do zastosowań real-time, wysokiej współbieżności i API o dużym ruchu lepszy jest Node.js + Express. Do ML/AI, analityki danych i mniejszych serwisów, gdzie kluczowa jest prostota i bezpieczeństwo, lepiej sprawdza się Python + Flask. Ostateczny wybór powinien zależeć od charakteru obciążenia (I/O-bound vs. CPU-bound) i wymagań projektu.

3.3 „Node.js or PHP? Determining the Better Website Server Backend Scripting Language” – Q. Odeniran, H. Wimmer, C. Rebman

 Cel: Porównanie wydajności PHP i Node.js przy przetwarzaniu czterech algorytmów (sortowania i permutacji) w warunkach symulowanego obciążenia serwera webowego. Celem jest wskazanie, która technologia jest ogólnie szybsza jako backend webowy.

Porównywane elementy: 
•	Technologie: PHP (szeroko stosowany, dojrzały) vs. Node.js (zdarzeniowy, non-blocking I/O).
•	Algorytmy testowe: binary sort, bubble sort, quick sort (tablice 100, 1000, 10 000 elementów) oraz algorytm Heap – generowanie wszystkich permutacji (tablice 5, 7, 9 elementów).
Metodologia: Dla każdego algorytmu w obu technologiach wykonano po 30 uruchomień przy danej wielkości tablicy. Obciążenie generowano narzędziem Apache JMeter (100 wirtualnych użytkowników, ramp-up 10 s). Dane z JMeter (m.in. latencja) zapisywano przez Table Listener i eksportowano do CSV. Do statystycznej walidacji wyników zastosowano test t-Studenta dla średniej latencji (podano t-statystyki, wariancje, p-value jednostronne).

Metryki: 
•	Latencja (ms) – główna metryka.
•	Pomocniczo: sample time, connect time, wysłane bajty.

Wyniki: 
Algorytm / rozmiar	PHP Śr. latencja (ms)	Node.js Śr. latencja (ms)	Przewaga Node.js	p-value
Binary sort, n = 100	Wyższa	✔ Niższa	Istotna	~0
Binary sort, n = 10 000	Wyższa	✔ Niższa	Istotna	~0
Bubble sort, n = 10 000	>10× wyższa	✔ Niższa	>10×	~0
Quick sort, n = 10 000	Wyższa	✔ Niższa	Istotna	~0
Heap (permutacje), n = 9	Wyższa	✔ Niższa	Wyraźna	~0
Tabela 3. Porównanie średniej latencji PHP vs. Node.js dla wybranych algorytmów i rozmiarów danych 

•	Node.js konsekwentnie osiąga niższą latencję od PHP dla wszystkich algorytmów i wszystkich rozmiarów tablic.
•	Przewaga Node.js rośnie wraz z wielkością danych: dla bubble sort przy 10 000 elementów Średnia latencja PHP jest ponad 10-krotnie wyższa.
•	We wszystkich testach p-value ≈ 0 (test jednostronny), co świadczy o istotności statystycznej różnic na korzyść Node.js.
Ograniczenia: porównanie zawężone do wybranych algorytmów i jednej metryki (latencja). Wyniki mogą nie przenosić się bezpośrednio na inne typy aplikacji webowych.

Wnioski: Node.js jest istotnie statystycznie szybszy od PHP dla wszystkich badanych scenariuszy algorytmicznych i szczególnie zyskuje przy rosnącym rozmiarze danych. Autorzy zaznaczają jednak, że wybór technologii w praktyce powinien uwzględniać również inne czynniki: skalowalność, łatwość utrzymania i charakter aplikacji.
 
3.4 „Performance Comparison and Evaluation of Web Development Technologies in PHP, Python, and Node.js” – K. Lei, Y. Ma, Z. Tan

Cel: Porównanie wydajności trzech popularnych technologii backendowych – PHP (Apache), Python-Web (WebPy) i Node.js (Express) – w warunkach wysokiej liczby użytkowników i dużej liczby żądań oraz wskazanie, do jakich typów aplikacji najlepiej nadaje się każda z nich.

Porównywane elementy: 
•	Node.js (Express), PHP (Apache), Python-Web (WebPy).
•	Zakres: wyłącznie wydajność; bezpieczeństwo i skalowalność poza zakresem pracy.

Metodologia: 
Benchmarki (ApacheBench)
•	„Hello World” – prosta obsługa żądania.
•	„Calculate Fibonacci” – obciążenie obliczeniowe (n = 10/20/30).
•	„Select Operation of DB” – operacja I/O na bazie danych.
•	Stałe: 10 000 żądań; 10–100–500–1000 VU.
Scenariusze (LoadRunner, zachowanie użytkownika)
•	Login – wejście do systemu (I/O-intensive).
•	Encryption – logowanie z szyfrowaniem hasła (compute-intensive).
Metryki: 
•	Średnia liczba żądań na sekundę (req/s) i Średni czas na jedno żądanie (ms) – benchmarki.
•	Przepustowość (B/s), hits per second, Średni czas odpowiedzi transakcji – scenariusze.

Wyniki: 
Scenariusz	Node.js (req/s)	PHP (req/s)	Python-Web (req/s)	Lider
Hello World (10 VU)	~3 703	~1 850	~55	✔ Node.js
Fibonacci(30), 10 VU	~59	~1,78	~3	✔ Node.js
Select DB (I/O), 10 VU	~3 164	~1 582	~158	✔ Node.js
Tabela 4. Porównanie średniej liczby żądań na sekundę [req/s] dla wybranych scenariuszy

Hello World – podstawowa wydajność
•	Node.js osiąga ok. 3703 req/s – ok. 2× więcej niż PHP i ok. 67× więcej niż Python-Web.
Fibonacci – obliczeniowe
•	Wszystkie technologie tracą wydajność przy Fibonacci(30); Node.js jest jednak nadal najlepszy (ok. 59 req/s vs. ok. 3 dla Python-Web i ok. 1,78 dla PHP).
•	Wniosek: żadna z technologii nie jest odpowiednia do zadań compute-intensive, ale Node.js wypada relatywnie najlepiej.
Select DB – I/O-intensive
•	Node.js: ok. 3164 req/s – ok. 2× więcej niż PHP i ok. 20× więcej niż Python-Web. Potwierdza wyższość Node.js w zadaniach I/O-intensive.

Wnioski: Node.js jest najlepszy przy wysokiej współbieżności i aplikacjach I/O-intensive, choć nie jest optymalny dla zadań obliczeniowych. PHP autorzy rekomendują dla małych i średnich serwisów z umiarkowanym obciążeniem. Python-Web wypada najgorzej wydajnościowo w testach I/O i compute, lecz postrzegany jest jako przyjazny dla dużych architektur dzięki bogatemu ekosystemowi (poza zakresem pomiarów w tej pracy).
 
3.5 „Performance Comparison of Development Frameworks in Selected Environments in REST API Architecture” – M. Szewczyk, M. Skublewska-Paszkowska

Cel: Porównanie pięciu popularnych frameworków REST API – ASP.NET, Spring Boot, Express.js, Laravel i Django REST Framework – w jednolitych warunkach (ta sama baza MySQL, te same endpointy, Docker, Azure), w celu wskazania, które rozwiązania są najszybsze i najbardziej zasobooszczędne. Weryfikowane są trzy hipotezy: (H1) ASP.NET i Spring Boot osiągają podobną wydajność; (H2) Express.js jest najszybszy dla małych odpowiedzi; (H3) Django jest najwolniejszy.

Porównywane elementy: 
•	ASP.NET 8.0, Spring Boot 3.3.2, Express.js 4.19.2, Laravel 11.9, Django REST Framework 3.15.2.
•	Wspólna specyfikacja: OpenAPI, pełny CRUD, JWT, ta sama baza MySQL z 1000 rekordów studentów.
•	Uruchomienie jako kontenery Docker w Azure App Service.

Metodologia: Testy z Postman Runner przez 10 minut na scenariusz; 11 scenariuszów (różne GET/POST/PUT/PATCH/DELETE, od 1 do 1000 rekordów). Monitoring CPU i RAM przez Azure.

Metryki: 
•	Średnni czas odpowiedzi i percentyl 90% (ms) dla 11 scenariuszy.
•	Średnnie obciążenie CPU (%) i RAM (%) z Azure.
•	Liczba plików i linii kodu (narzędzie CLOC).
•	Rozmiar obrazu Docker (MB).

Wyniki: 
Framework	Czas odp. (ogół)	CPU (%)	RAM (%)	Linie kodu	Rozmiar Dockera	Ocena
ASP.NET 8.0	✔ Najszybszy	37,6	77,1	Najwięcej	✔ Mały	Szybki, zasobochłonny
Spring Boot 3.3	Bliski ASP.NET	Śr.	Śr.	Śr.	✔ Mały	Dobra równowaga
Express.js 4.19	Dobry	40,8	64,2	Śr.	Duży	✔ Zrównoważone zasoby
Laravel 11.9	Najwolniejszy	~53,5	Niższy	Najwięcej plików	Największy	Słaba wydajność
Django REST 3.15	Słaby (skok przy 1000 rek.)	~52	Niższy	✔ Najmniej	Śr.	Zwarty kod, słaba wydajność
Tabela 5. Porównanie pięciu frameworków REST API według czasu odpowiedzi, zasobów i rozmiaru kodu
Czasy odpowiedzi
•	ASP.NET był najszybszy w większości scenariuszy; Laravel najwolniejszy – często nawet 10× wolniejszy od lidera.
•	Django REST Framework wykazywał duży skok czasu przy pobieraniu 1000 rekordów (scenariusz 4), co wskazuje na problemy ze skalowaniem przy większych wolumenach danych.
•	Hipoteza H2 (Express.js najszybszy dla małych odpowiedzi) została odrzucona – wyniki zbliżone do ASP.NET, z jedynie minimalną przewagą w jednym scenariuszu.
•	Hipoteza H1 (ASP.NET ≈ Spring Boot) potwierdzona częściowo – wyniki zbliżone, lecz nie wszystkie metryki mieszczą się w granicy 15%.
•	Hipoteza H3 (Django najwolniejszy) potwierdzona.
Zasoby (CPU i RAM)
•	ASP.NET: szybki, ale relatywnie wysokie średnie CPU (37,6%) i RAM (77,1%).
•	Django i Laravel: najwyższe CPU (ok. 52–53,5%), ale niższe zużycie RAM.
•	Express.js: zrównoważone zużycie CPU (40,8%) i RAM (64,2%); wyróżnia się stabilnym zarządzaniem zasobami.
Kod i obrazy Docker
•	Najbardziej kompaktowy kod: Django REST Framework (najmniej linii i plików).
•	Najbardziej rozbudowany: ASP.NET (najwięcej linii) i Laravel (najwięcej plików).
•	Najmniejsze obrazy Docker: ASP.NET i Spring Boot; największe: Laravel i Express.js; Django pośrodku.

Wnioski: ASP.NET jest ogólnie najszybszy i ma małe obrazy Dockera, lecz kosztem wyższego zużycia zasobów i większej złożoności kodu. Express.js oferuje zrównoważone zużycie CPU/RAM, choć generuje większe obrazy. Spring Boot jest bliski ASP.NET pod względem szybkości i rozmiaru obrazu. Django REST Framework i Laravel zapewniają zwarty kod (Django) lub bogaty ekosystem (Laravel), lecz wyraźnie gorszą wydajność. Optymalny wybór zależy od priorytetów: czas odpowiedzi, koszty chmury, prostota utrzymania.
 
3.6 „Performance Evaluation of REST and GraphQL API Approaches in Data Retrieval Scenarios Using NestJS” – K. Stępień, M. Skublewska-Paszkowska

Cel: Porównanie wydajności REST i GraphQL w realnym środowisku (NestJS + PostgreSQL) przy różnych typach zapytań i poziomach obciążenia. Badanie ma odpowiedzieć, kiedy REST jest szybszy, a kiedy GraphQL osiąga przewagę, również pod kątem rozmiaru przesyłanych danych. Stawiane hipotezy: (H1) GraphQL jest szybszy dla złożonych, zagnieżdżonych danych; (H2) dla prostych odczytów wydajność REST i GraphQL jest porównywalna.

Porównywane elementy: 
•	Dwie aplikacje o identycznej funkcjonalności w NestJS: jedna zaimplementowana jako REST API, druga jako GraphQL.
•	Wspólna baza danych: PostgreSQL (baza Northwind, 15 tabel, użyto 4).
•	Scenariusze: (1) zapytania do jednej tabeli (orders) – 1, 100, 500 rekordów; (2) złożone zapytanie do 4 powiązanych tabel.
•	Warianty GraphQL: pełny (wszystkie pola) i zredukowany (ograniczone pola – ok. 7% struktury dla scenariusza 1; ok. 45% dla scenariusza 2).
•	Warianty REST: wiele endpointów (8 żądań) i jeden endpoint agregujący.

Metodologia: Testy narzędziem Grafana k6 przy 1 000–24 000 wirtualnych użytkowników. Każda konfiguracja uruchamiana 6-krotnie; wyniki uśrednianie.

Metryki: 
•	Średnni czas odpowiedzi w funkcji liczby równoległych użytkowników (ms/s).
•	Przepustowość (req/s).
•	Rozmiar danych wysyłanych i odbieranych na żądanie (kB).

Wyniki: 
Scenariusz	REST	GraphQL pełny	GraphQL zredukowany	Wniosek
1 rek., 1 000 VU	✔ 3,37 ms	4,49 ms	—	REST nieznacznie lepszy
1 rek., 24 000 VU	✔ 1 208 ms	2 425 ms	—	REST 2× szybszy
500 rek., wysokie VU	19,7 s	42 s	✔ ~58% szybszy od REST	GraphQL-red. wygrywa
4 tabele, 24 000 VU (8 req)	16,8 s	7,5 s	✔ 6,6 s	GraphQL 2×+ szybszy
4 tabele – 1 aggr. endpoint	✔ 2,42 s	—	—	REST z 1 endpointem najszybszy
Rozmiar odpowiedzi (4 tabele)	4,0 kB	—	✔ 1,2 kB (~70% mniej)	GraphQL-red. zdecydowanie mniejszy
Tabela 6. Porównanie REST i GraphQL w NestJS według scenariuszy i obciążenia 

Proste zapytania (1 tabela)
•	Przy małej liczbie rekordów i niskim obciążeniu czasy są podobne (REST 3,37 ms vs. GraphQL 4,49 ms przy 1000 VU).
•	Wraz ze wzrostem obciążenia REST wyraźnie wygrywa: przy 24 000 VU REST osiąga 1208 ms vs. GraphQL 2425 ms (ponad 2× szybszy).
•	GraphQL z ograniczonymi polami może być do ok. 58% szybszy od pełnego REST przy dużej liczbie rekordów i jednocześnie zmniejsza rozmiar odpowiedzi do ok. 6% rozmiaru REST.
•	REST uzyskuje najwyższą przepustowość w prostych scenariuszach.
Złożone zapytania (4 tabele)
•	Standardowy REST (8 żądań) ma najgorsze czasy: 16,8 s przy 24 000 VU vs. GraphQL pełny 7,5 s i GraphQL zredukowany 6,6 s (GraphQL ponad 2× szybszy).
•	REST z jednym endpointem agregującym jest najszybszy (ok. 2,42 s), jednak wymaga dodatkowej logiki po stronie serwera i jest mniej elastyczny.
•	GraphQL z ograniczonymi polami znacznie zmniejsza rozmiar odpowiedzi: 1,2 kB vs. 4,0 kB dla REST (∧0% redukcji).
Weryfikacja hipotez
•	H1 potwierdzona: GraphQL jest szybszy dla złożonych, zagnieżdżonych zapytań do wielu tabel.
•	H2 częściowo potwierdzona: dla prostych odczytów przy niskim obciążeniu wydajność jest porównywalna, jednak przy rosnącej liczbie VU REST zdecydowanie wygrywa.

Wnioski: REST jest bardziej wydajny i skalowalny dla prostych zapytań i wysokiego obciążenia, oferuje krótsze czasy odpowiedzi i wyższą przepustowość. GraphQL zyska przewagę przy złożonych, relacyjnych strukturach danych oraz gdy kluczowa jest minimalizacja rozmiaru odpowiedzi; GraphQL z ograniczonymi polami może również przewyższyć REST w prostszych scenariuszach pod względem rozmiaru danych. Ostateczny wybór zależy od złożoności modelu danych, profilu obciążenia i wymagań klienta.
 







4. Metodologia testów wydajnościowych
 
4.1 „Comparative Analysis of Jmeter and Postman for API- Based Performance Testing”, S. Khlamov, M. Mendielieva, O. Vovk,  Z. Deineko

Cel: Porównanie dwóch narzędzi do testowania wydajności REST API – Postmana i Apache JMetera – pod kątem skuteczności przy różnych typach obciążenia (niskie, umiarkowane, szczytowe), metodach HTTP i profilach ruchu.

Porównywane elementy: 
•	Postman Desktop 11.50.2 vs. Apache JMeter 5.6.3, uruchamiane na tej samej maszynie (Windows 11, procesor 8-rdzeniowy, 16 GB RAM).
•	Pięć publicznych API: ReqRes, DummyJSON, SampleAPIs, JsonPlaceholder, FakeStoreAPI.
•	Cztery profile obciążenia: Ramp Up, Spike, Fixed Load, Peak Load.
•	Metody HTTP: GET, POST, PUT, DELETE (pełny cykl CRUD) dla każdego API.

Metodologia: Osiem scenariuszów testowych łączących różne profile obciążenia, liczbę wirtualnych użytkowników (10–80), think time (1–10 s) i czas trwania (5–10 min). W obu narzędziach zastosowano sekwencyjne wykonanie GET → POST → PUT → DELETE z opóźnieniem między żądaniami. W JMeterze użyto różnych typów Thread Group z timerami w celu odwzorowania profilów obciążenia Postmana. Wyniki agregowane po 8 scenariuszach i 5 API.

Profile obciążenia: 
Profil	Opis	Liczba VU / think time	Czas trwania
Ramp Up	Stopniowe zwiększanie obciążenia	Do 80 VU / 10 s	5–10 min
Spike	Nagły skok obciążenia	Do 80 VU / 1 s	5–10 min
Fixed Load	Stałe obciążenie przez cały test	40 VU / 10 s	5 min
Peak Load	Obciążenie szczytowe	80 VU / 1 s	5 min
Tabela 1. Charakterystyka czterech profilów obciążenia zastosowanych w badaniu

Metryki: 
•	Średnni średni czas odpowiedzi (Mean Avg, ms).
•	Średnni minimalny czas odpowiedzi (Mean Min, ms).
•	Średnni maksymalny czas odpowiedzi (Mean Max, ms).
•	Procent błędów (Mean Error %).

Wyniki: 
Scenariusz / warunki	Postman 11.50.2	Apache JMeter 5.6.3	Lider
Niskie obciążenie (długi think time ≥1 s, mało VU)	✔ Krótsze Średnie czasy avg/min/max w większości API i scenariuszy	Wyższe Średnie czasy	Postman
Umiarkowane obciążenie (stały, niezbyt intensywny ruch)	✔ Niższe czasy odpowiedzi	Porównywalny lub słabszy	Postman
Wysokie obciążenie / szczytowe (think time ≤5 s, dużo VU)	Wyższe czasy przy dużym ruchu	✔ Lepsze czasy, lepsza skalowalność	JMeter
Błędy przy bardzo dużym obciążeniu (429, 502)	Występują w obu narzędziach	Występują w obu narzędziach	Remis
Tabela 2. Porównanie Postmana i JMetera w zależności od poziomu obciążenia

Niskie i umiarkowane obciążenie
–	Postman osiąga krótsze średnie czasy odpowiedzi w większości API i scenariuszy; agregaty z tabeli zbiorczej potwierdzają ogólnie lepsze czasy avg/min/max dla Postmana (poza drobnymi wyjątkami).
–	Przykład: w scenariuszu TC1 dla ReqRes wszystkie metody HTTP były szybsze w Postmanie.
Wysokie obciążenie i szczyty ruchu
–	W scenariuszach z krótkim think time (≤5 s) i dużą liczbą VU JMeter osiąga lepsze czasy odpowiedzi i lepiej skaluje się przy szczytach ruchu (np. scenariusz TC6).
–	Przy bardzo dużym obciążeniu oba narzędzia napotykają błędy 429/502 – wynikające z ograniczeń testowanych API, nie samych narzędzi.
Uwaga: autorzy zaznaczają, że są to wstępne wyniki z pojedynczego przebiegu; ustrój wyników jest jednak spójny i oparty na setkach żądań na scenariusz.

Wnioski: Postman sprawdza się lepiej przy stałym lub umiarkowanym obciążeniu i dłuższych przerwach między żądaniami – jest wygodnym narzędziem do testów funkcjonalnych i wydajnościowych API w typowych warunkach deweloperskich. JMeter wygrywa przy dużym obciążeniu i wysokiej intensywności ruchu, zwłaszcza w scenariuszach szczytowych – jest narzędziem do pełnoskalowych testów obciążeniowych. Wybór narzędzia powinien zależeć od planowanego profilu testu.

4.2 „Generating Representative Web Workloads for Network and Server Performance Evaluation” – P. Barford, M. Crovella

Cel: Opracowanie narzędzia SURGE (Scalable URL Reference Generator) analitycznego generatora obciążeń WWW, który wiernie odtwarza statystyczne cechy rzeczywistego ruchu HTTP i nadaje się do oceny wydajności serwerów, proxy i sieci. Autorzy wskazują, że dotychczasowe benchmarki (np. SPECweb96) nadmiernie upraszczają rzeczywiste zachowanie użytkowników i nie zachowują kluczowych właściwości statystycznych ruchu.

Porównywane elementy: 
•	SURGE – nowy generator obciążeń oparty na sześciu modelach statystycznych cech ruchu WWW.
•	SPECweb96 – typowy benchmark WWW stosowany jako punkt odniesienia.
•	Oba generatory skonfigurowane tak, by przesłać podobną łączną ilość danych w trakcie 30-minutowych testów.
Metodologia: Pre-generowane sekwencje żądań, liczby obiektów osadzonych i czasów OFF; wielowątkowy klient w Javie wykonuje HTTP/0.9. Eksperymenty: do 5 klientów, Apache 1.2.4 na Linuksie, sieć 100 Mbps, okresy testów 30 minut. W SURGE wprowadzono koncepcję user equivalents (UE) procesów ON/OFF reprezentujących sesje użytkowników, co umożliwia realistyczne odwzorowanie burstowości obciążenia. Dla SPECweb96 dobierano liczbę operacji/s i wątków tak, by łączna ilość przesłanych danych była porównywalna z SURGE.

Modelowane cechy ruchu (SURGE): 
Charakterystyka ruchu	Model statystyczny	Rola w generatorze
Rozmiary plików na serwerze	Hybrydowy: lognormal + Pareto	Odwzorowanie rzeczywistej dystrybucji plików
Rozmiary żądań klientów	Pareto	Zróżnicowanie wielkości żądań
Popularność plików	Zipf (potęgowy)	Efekt „kilku popularnych” zasobów
Obiekty osadzone w dokumencie	Pareto	Wiele zasobów per stronę
Lokalność czasowa (cache)	Lognormal (odległość stosowa)	Realistyczny efekt pamięci podręcznej
Czasy OFF użytkowników	Aktywne: Weibull Nieaktywne: Pareto	Burstowość obciążenia (ON/OFF)
Tabela 3. Sześć charakterystyk statystycznych ruchu WWW modelowanych przez SURGE

Metryki: 
•	Obciążenie CPU serwera (%) i jego zmienność w czasie.
•	Liczba równoległych połączeń TCP i ich zmienność.
•	Samopodobieństwo ruchu sieciowego (burstiness na wielu skalach czasowych) – mierzone wykładnikiem Hursta / nachyleniem variance-time plot.

Wyniki: 
Obciążenie serwera
•	Przy podobnej przepustowości (ok. 500 pak/s) SURGE generuje znacznie wyższe i bardziej zmienne obciążenie CPU niż SPECweb96: szczyty do ok. 76% vs. maksimum ok. 37% dla SPECweb96.
•	SURGE utrzymuje znacznie więcej jednoczesnych połączeń TCP oraz większą ich zmienność, co lepiej odzwierciedla rzeczywisty ruch.
Właściwości ruchu sieciowego
•	Ruch generowany przez SURGE zachowuje samopodobieństwo (burstiness na wielu skalach czasowych) również przy wysokim obciążeniu.
•	SPECweb96 traci samopodobieństwo przy wysokim obciążeniu: nachylenie wykresu variance-time dąży do 1/2 (ruch białego szumu), co nie odzwierciedla rzeczywistego zachowania.

Wnioski: SURGE realizuje cel wiernej emulacji ruchu WWW poprzez odwzorowanie sześciu statystycznych cech obciążenia i zachowania populacji użytkowników. W porównaniu ze SPECweb96 generuje znacznie bardziej wymagające scenariusze: wyższe obciążenie CPU, więcej równoległych połączeń i trwałe samopodobieństwo ruchu przy dużym obciążeniu. Wyniki sugerują, że tradycyjne benchmarki mogą systematycznie zaniżać realne wymagania wobec serwerów i sieci.

4.3 „Performance and Load Testing: Tools and Challenges” – M. Yenugula, R. Kodam, D. He

Cel: Opisanie narzędzi i wyzwań w testach wydajności i obciążenia aplikacji webowych, mobilnych i usług w chmurze, ze szczególnym uwzględnieniem porównania narzędzi Apache JMeter, SoapUI, Locust, k6, Taurus i ZAPTEST LOAD. Praca łączy część przeglądową z krótkim eksperymentem porównawczym JMeter vs. Locust vs. HULK Analyzer.

Porównywane elementy: 
•	Część przeglądowa: narzędzia open-source (JMeter, Locust, k6, Taurus) vs. komercyjne platformy (SoapUI Pro, ZAPTEST LOAD) – pod kątem kosztu, złożoności konfiguracji, elastyczności, skalowalności i wsparcia.
•	Część eksperymentalna: Apache JMeter vs. Locust (Python) vs. HULK Analyzer przy identycznym obciążeniu i warunkach ruchu.

Metodologia: Trzy narzędzia poddano identycznym warunkom obciążenia i zmierzono czas wykonania testu w pięciu kolejnych próbach. Porównanie jakościowe narzędzi oparto na przeglądzie dokumentacji, literatury i doświadczeń społeczności.

Metryki: 
•	Czas wykonania testu (s) – porównanie eksperymentalne JMeter / Locust / HULK.
•	Czas odpowiedzi (response time, ms) i przepustowość (hity/s, transakcje/s) – ogólne metryki testów obciążeniowych opisane w części przeglądowej.
•	Metryki sprzętowe: CPU (%), RAM (%), dysk I/O.
•	Metryki bazodanowe: operacje odczytu/zapisu, liczba połączeń.
•	Baseline i benchmark performance: poziom odniesienia i porównanie z SLA.

Wyniki: 
Eksperyment porównawczy: czas wykonania testu
Próba	Apache JMeter (s)	Locust (s)	HULK Analyzer (s)
1	3,102	✔ 2,641	2,991
2	3,241	✔ 2,703	2,912
3	3,388	✔ 2,756	2,876
4	3,401	✔ 2,784	2,901
5	3,422	✔ 2,819	2,954
Tabela 4. Czasy wykonania testu [s] dla JMeter, Locust i HULK Analyzer w pięciu próbach przy identycznym obciążeniu


•	Locust konsekwentnie osiąga najkrótsze czasy wykonania we wszystkich pięciu próbach (np. próba 5: 2,819 s vs. 3,422 s dla JMetera).
•	JMeter jest najwolniejszy spośród trzech narzędzi w każdej próbie; HULK Analyzer plasuje się pośrodku.
•	
Porównanie jakościowe narzędzi
Kryterium	JMeter	Locust	SoapUI	k6 / Taurus
Koszt	Bezpłatny	Bezpłatny	Bezpł. + wersja pro	Bezpłatny
Skalowalność	✔ Wysoka (duże obciążenia)	Dobra	Ograniczona	Dobra
Czas wykonania testu	Najdłuższy	✔ Najkrótszy	Śr.	Śr.
Złożoność konfiguracji	Wysoka	✔ Niższa (Python)	Średnia	Niższa
Wsparcie QoS / SOAP	Ograniczone	Brak	✔ Dedykowane	Ograniczone
Ekosystem i wsparcie	✔ Dojrzały, duża społ.	Dobry	Dobry	Rosnący
Tabela 5. Porównanie jakościowe wybranych narzędzi do testów obciążeniowych

•	Apache JMeter: uznany za bardzo silne narzędzie do symulacji dużego obciążenia serwera; dojrzały ekosystem i duża społeczność, lecz wysoka złożoność konfiguracji.
•	SoapUI: dedykowane wsparcie dla testów QoS i SOAP/REST; najlepszy wybór do oceny jakości usług pod zmiennym obciążeniem.
•	Narzędzia open-source ogólnie: niski koszt i elastyczność, lecz brak formalnego wsparcia, złożoność konfiguracji i potencjalne limity liczby użytkowników przy bardzo dużym obciążeniu.

Wyzwania i przyszłe kierunki
•	Główne wyzwania testów obciążeniowych: trudność wykazania wymiernych korzyści biznesowych („niemate rialność”) oraz złożoność techniczna narzędzi wymagająca doświadczonego zespołu.
•	Autorzy wskazują na potrzebę rozwoju nowych bibliotek open-source oraz wskazują bezpieczeństwo aplikacji webowych i integrację z testami (m.in. analiza podatności) jako obszary przyszłych badań.

Wnioski: Locust wykazuje najkrótsze czasy wykonania testów spośród trzech porównywanych narzędzi, podczas gdy JMeter – mimo najdłuższych czasów wykonania – jest rekomendowany do wysokich obciążeń produkcyjnych ze względu na dojrzałość i ekosystem. SoapUI pozostaje najlepszym wyborem do oceny QoS i usług SOAP. Wybór narzędzia powinien uwzględniać specyfikę testu, wymagany poziom obciążenia oraz dostępne zasoby zespołu. Narzędzia open-source oferują atrakcyjny kompromis między kosztem a możliwościami, lecz wymagają większego nakładu pracy przy konfiguracji i utrzymaniu.
 
