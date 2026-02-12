@extends('layout.app')

@section('content')
    <div class="container">
        <h1>Wszystkie twoje raporty</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>ID Raportu</th>
                <th>Pracownik</th>
                <th>Data Raportu</th>
                <th>Dochód</th>
            </tr>
            </thead>
            <tbody>
            @foreach($raport->sortByDesc('data_raportu') as $raport)
                @if($raport->id_pracownika == auth()->id())
                    @php
                        $pracownik = App\Models\User::find($raport->id_pracownika);
                    @endphp
                    <tr>
                        <td>{{ $raport->id_raportu }}</td>
                        <td>{{ $raport->id_pracownik }}{{ $pracownik->imie }} {{ $pracownik->nazwisko }}</td>
                        <td>{{ $raport->data_raportu }}</td>
                        <td>{{ $raport->dochod }}</td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>

        <div class="text-center mt-4">
            <form action="{{ route('raport_kelner.create') }}" method="GET">
                @csrf
                <button type="submit" class="btn btn-dodaj">Utwórz raport z dziś</button>
            </form>
        </div>
    </div>
@endsection