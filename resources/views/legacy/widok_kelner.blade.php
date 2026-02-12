@extends('layout.app')
@section('content')
    <div id="zawartosc_strony">

        <h1>Widok pracownika - {{ auth()->user()->imie }} {{ auth()->user()->nazwisko }}</h1>

        <table>
            <thead>
            <tr>
                <th>Numer stolika</th>
                <th>Data</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($stoliki->sortBy('data') as $stolik)
                @php
                    $yesterday = now()->subDay();
                    $tomorrow = now()->addDay();
                @endphp
                @if ($stolik->data > $yesterday && $stolik->data < $tomorrow)
                    <tr>
                        <td>{{ $stolik->id_stolika1 }} {{ $stolik->id_stolika2 }} {{ $stolik->id_stolika3 }}</td>
                        <td>{{ $stolik->data }}</td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            <a href="{{ route('zamowienia.store') }}" class="btn-dodaj" target="_blank" style="max-width: 200px; padding: 20px">Dodaj zamówienie</a>
        </div>

        <table>
            <thead>
            <tr>
                <th>Numer zamówienia</th>
                <th>Przyjęte</th>
                <th>Numer stolika</th>
                <th>Status</th>


                <th>Nazwa zamówionej pozycji</th>
                <th>Cena jednostkowa</th>
                <th>Ilość</th>
                <th>Suma</th>
                <th>Wydane</th>
                <th>Akcje</th>
            </tr>
            </thead>
            <tbody>
            @foreach($zamowienia as $zamowienie)
                @if ($zamowienie->status != 'opłacone')
                    @foreach($zamowienie->szczegoly as $index => $szczegol)
                        <tr>
                            @if ($index === 0)
                                <td rowspan="{{ $zamowienie->szczegoly->count() }}">{{ $zamowienie->id_zamowienia }}</td>
                                <td rowspan="{{ $zamowienie->szczegoly->count() }}">{{ $zamowienie->przyjecie_at }}</td>
                                <td rowspan="{{ $zamowienie->szczegoly->count() }}">{{ $zamowienie->stolik->Numer_stolika }}</td>
                            @endif
                            <td>{{ $szczegol->status }}</td>
                            <td>{{ $szczegol->menu->potrawa->nazwa }}</td>
                            <td>{{ number_format($szczegol->menu->cena, 2) }}</td>
                            <td>{{ $szczegol->ilosc }}</td>
                            <td>{{ number_format($szczegol->menu->cena * $szczegol->ilosc, 2) }}</td>
                            <td>
                                <div>
                                    <input type="checkbox" id="wydane_{{ $zamowienie->id_zamowienia }}_{{ $index }}" class="wydane-checkbox" name="items[{{ $index }}][wydane]" {{ $szczegol->wydane ? 'checked' : '' }}>
                                    <label for="wydane_{{ $zamowienie->id_zamowienia }}_{{ $index }}">Wydane</label>
                                </div>
                            </td>
                            @if ($index === 0)
                                <td rowspan="{{ $zamowienie->szczegoly->count() }}">
                                    <a href="{{ route('zamowieniaEdit', $zamowienie->id_zamowienia) }}" target="_blank" class="btn-edytuj">Edytuj</a>
                                    @php
                                        $wszystkieWydane = true;
                                    @endphp
                                    @foreach ($zamowienie->szczegoly as $szczegol)
                                        @if ($szczegol->status !== 'wydane z kuchni')
                                            @php
                                                $wszystkieWydane = false;
                                                break;
                                            @endphp
                                        @endif
                                    @endforeach

                                    @if ($wszystkieWydane)
                                        <a href="{{ route('rachunek.show', ['id' => $zamowienie->id_zamowienia]) }}" class="btn btn-primary">Rachunek</a>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endif
            @endforeach


            </tbody>
        </table>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('.wydane-checkbox');

                checkboxes.forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        const zamowienieId = checkbox.id.split('_')[1];
                        const szczegolIndex = checkbox.id.split('_')[2];

                        localStorage.setItem(`wydane_${zamowienieId}_${szczegolIndex}`, checkbox.checked);
                    });

                    const zamowienieId = checkbox.id.split('_')[1];
                    const szczegolIndex = checkbox.id.split('_')[2];
                    const savedValue = localStorage.getItem(`wydane_${zamowienieId}_${szczegolIndex}`);

                    if (savedValue === 'true') {
                        checkbox.checked = true;
                    }
                });

                function updateStatus(zamowienieId) {
                    // Pobierz stan zaznaczenia checkboxów dla danego zamówienia
                    const checkboxes = document.querySelectorAll(`.wydane-checkbox[data-zamowienie="${zamowienieId}"]`);
                    const szczegoly = [];

                    checkboxes.forEach(function(checkbox) {
                        const szczegolIndex = checkbox.getAttribute('data-szczegol');
                        const wydane = checkbox.checked;
                        szczegoly.push({ szczegolIndex, wydane });
                    });

                    // Wyślij dane na serwer
                    fetch(`/aktualizuj-status-zamowienia/${zamowienieId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ szczegoly: szczegoly })
                    })
                        .then(response => {
                            if (response.ok) {
                                console.log(`Zaktualizowano status zamówienia ${zamowienieId}`);
                            } else {
                                console.error(`Błąd podczas aktualizowania statusu zamówienia ${zamowienieId}`);
                            }
                        })
                        .catch(error => {
                            console.error(`Błąd podczas aktualizowania statusu zamówienia ${zamowienieId}`, error);
                        });
                }
            });
        </script>

        <script>
            setTimeout(function() {
                location.reload();
            }, 10000);
        </script>
@endsection