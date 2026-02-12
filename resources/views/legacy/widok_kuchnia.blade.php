@extends('layout.wysuwany_kucharz')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

    <style>
        #button{
            padding: 20px;
        }

        #ramka {
            padding: 20px;
        }

    </style>
</head>

<body class="bg-light">
<div class="container">


    <div class="row">
        <div class="col-md-4">
            <h2>Przyjęte:</h2>
            <ul class="list-group">
                @foreach ($szczegoly as $szczegol)
                    @if($szczegol->status == 'oczekuje')
                        <li class="list-group-item list-group-item-danger">
                            <div>
                                <span class="font-weight-bold">Numer zamówienia:</span> {{ $szczegol->zamowienie->id_zamowienia }}
                            </div>

                            <div>
                                <div>
                                    <span class="font-weight-bold">Potrawa:</span> {{ $szczegol->menu->potrawa->nazwa }},
                                    <span class="font-weight-bold">Ilość:</span> {{ $szczegol->ilosc }},
                                    <span class="font-weight-bold">Status:</span> {{ $szczegol->status }},
                                </div>
                            </div>

                            <div class=" d-flex justify-content-between align-items-center" id="ramka">
                                <form action="{{ route('szczegolZamowien.update', $szczegol->id_szczegoly) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="w trakcie przygotowania">
                                    <button type="submit" class="btn btn-warning" id="button">W trakcie</button>
                                </form>

                                <form action="{{ route('szczegolZamowien.update', $szczegol->id_szczegoly) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="gotowe">
                                    <button type="submit" class="btn btn-success" id="button">Do wydania</button>
                                </form>
                            </div>
                        </li>

                    @endif
                @endforeach
            </ul>
        </div>
        <div class="col-md-4">
            <h2>W trakcie:</h2>
            <ul class="list-group">
                @foreach ($szczegoly as $szczegol)
                    @if($szczegol->status == 'w trakcie przygotowania')
                        <li class="list-group-item list-group-item-warning">
                            <div>
                                <span class="font-weight-bold">Numer zamówienia:</span> {{ $szczegol->zamowienie->id_zamowienia }}
                            </div>

                            <div>
                                <div>
                                    <span class="font-weight-bold">Potrawa:</span> {{ $szczegol->menu->potrawa->nazwa }},
                                    <span class="font-weight-bold">Ilość:</span> {{ $szczegol->ilosc }},
                                    <span class="font-weight-bold">Status:</span> {{ $szczegol->status }},
                                </div>
                            </div>

                            <div class=" d-flex justify-content-center align-items-center" id="ramka">


                                <form action="{{ route('szczegolZamowien.update', $szczegol->id_szczegoly) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="gotowe">
                                    <button type="submit" class="btn btn-success" id="button">Do wydania</button>
                                </form>
                            </div>
                        </li>

                    @endif
                @endforeach
            </ul>
        </div>
        <div class="col-md-4">
            <h2>Do wydania:</h2>
            <ul class="list-group">
                @foreach ($szczegoly as $szczegol)
                    @if($szczegol->status == 'gotowe')
                        <li class="list-group-item list-group-item-success">
                            <div>
                                <span class="font-weight-bold">Numer zamówienia:</span> {{ $szczegol->zamowienie->id_zamowienia }}
                            </div>

                            <div>
                                <div>
                                    <span class="font-weight-bold">Potrawa:</span> {{ $szczegol->menu->potrawa->nazwa }},
                                    <span class="font-weight-bold">Ilość:</span> {{ $szczegol->ilosc }},
                                    <span class="font-weight-bold">Status:</span> {{ $szczegol->status }},
                                </div>
                            </div>

                            <div class="d-flex justify-content-center align-items-center" id="ramka">
                                <form action="{{ route('szczegolZamowien.update', $szczegol->id_szczegoly) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="w trakcie przygotowania">
                                    <button type="submit" class="btn btn-warning" id="button">W trakcie</button>
                                </form>


                            </div>
                        </li>

                    @endif
                @endforeach
            </ul>
        </div>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script>
    setTimeout(function() {
        location.reload();
    }, 10000);
</script>

</body>
</html>