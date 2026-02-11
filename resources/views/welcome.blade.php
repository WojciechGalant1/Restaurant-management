<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restauracja</title>
    <!-- Latest compiled and minified CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Abril+Fatface&display=swap');

        .tlo_strony1 {
            width: 100%;
            height: 20%;
            margin-top: -270px;
            position: relative;
        }

        .tlo_strony2 {
            width: 100%;
            height: 20%;
            margin-top: -52px;
            position: relative;
        }

        .tlo_strony3 {
            width: 100%;
            height: 20%;
            margin-top: -52px;
            position: relative;
        }

        .tlo_strony4 {
            width: 100%;
            height: 20%;
            margin-top: -52px;
            position: relative;
        }

        .tlo_strony5 {
            width: 100%;
            height: 20%;
            margin-top: -52px;
            position: relative;
        }

        .tlo_strony6 {
            width: 100%;
            height: 20%;
            margin-top: -52px;
            position: relative;
        }

        .nawigacja {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            margin-top: 41px;
            height: 77px;
            background-color: transparent;
            z-index: 999;
        }

        .logo {
            margin-left: 20px;
            color: white;
        }

        .nav-link {
            text-decoration: none;
            color: white;
            padding: 14px;
            margin: 12px;
        }

        .nav-link:hover {
            color: orange;
            border-color: orange;
            border-radius: 20px;
            border-width: 3px;
            border-style: solid;
            transform: scale(1.2);
        }

        .content {
            position: relative;
            margin-top: 100px; /* Dodana wartość margin-top */
        }

        .text-on-image {
            position: absolute;
            top: 36%;
            left: 26%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            color: white;
            text-align: center;
            font-family: 'Abril Fatface', cursive;
            font-size: 56px;
        }

        .text-on-image2 {
            position: absolute;
            top: 45%;
            left: 26%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            color: white;
            text-align: center;
            font-family: 'Abril Fatface', cursive;
            font-size: 56px;
        }

        .restauracja {
            color: orange;
        }

        .pagination {
            margin-top: 10px;
        }

        .pagination button,
        .pagination a {
            padding: 5px 10px;
            border: none;
            background-color: #f0f0f0;
            color: #333;
            text-decoration: none;
            cursor: pointer;
        }

        .pagination button:disabled,
        .pagination a.disabled {
            opacity: 0.5;
            cursor: default;
        }

        .carousel-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .carousel_slide {
            width: 80%;
            height: 50%;
            position: absolute;
            top: 25%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .tlo_strony3 {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

    </style>
</head>

<body>
<header>
    <div class="nawigacja">
        <div class="logo">
            <h1 class="text-on-image">Mieszany/Mieszany</h1>
        </div>
        <ul class="nav justify-content-end">
            <li class="nav-item">
                <a class="nav-link" href="#strona-glowna">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#info">Info</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#menu">Menu</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#galeria">Gallery</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#kucharze">Chefs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#kontakt">Contact</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('login') }}">Login</a>
            </li>
        </ul>
    </div>
</header>

<section id="strona-glowna">
    <div class="content">
        <img class="tlo_strony1" src="https://images.pexels.com/photos/1639561/pexels-photo-1639561.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2">
        <div class="text-on-image">Witaj w naszej <span class="restauracja">Restauracji</span></div>
        <div class="text-on-image2">Jesteśmy z wami 10 lat </div>
    </div>
</section>


<section id="info">
    <h2>Info</h2>
    <img class="tlo_strony2" src="https://images.pexels.com/photos/1126728/pexels-photo-1126728.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2">

</section>

<section id="menu">
    <h2>Menu</h2>
    <div class="menucontent">
        <div class="carousel-container">
            <img class="tlo_strony3" src="https://images.pexels.com/photos/313700/pexels-photo-313700.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2">
            <div class="carousel_slide" data-bs-ride="carousel">

                <div class="carousel-indicators">
            <button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="2"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="3"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="4"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="5"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="6"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="7"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="8"></button>
            <button type="button" data-bs-target="#demo" data-bs-slide-to="9"></button>
        </div>


        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://www.browarikuchnia.pl/wp-content/uploads/2023/03/0004.jpg" alt="Los Angeles" class="d-block w-100">
            </div>
            <div class="carousel-item">
                <img src="https://www.browarikuchnia.pl/wp-content/uploads/2023/03/0005.jpg" alt="Chicago" class="d-block w-100">
            </div>
            <div class="carousel-item">
                <img src="https://www.browarikuchnia.pl/wp-content/uploads/2023/03/0006.jpg" alt="New York" class="d-block w-100">
            </div>
            <div class="carousel-item">
                <img src="https://www.browarikuchnia.pl/wp-content/uploads/2023/03/0007.jpg" alt="New York" class="d-block w-100">
            </div>
            <div class="carousel-item">
                <img src="https://www.browarikuchnia.pl/wp-content/uploads/2023/03/0008.jpg" alt="New York" class="d-block w-100">
            </div>
            <div class="carousel-item">
                <img src="https://www.browarikuchnia.pl/wp-content/uploads/2023/03/0009.jpg" alt="New York" class="d-block w-100">
            </div>
            <div class="carousel-item">
                <img src="https://www.browarikuchnia.pl/wp-content/uploads/2023/03/0010.jpg" alt="New York" class="d-block w-100">
            </div>
            <div class="carousel-item">
                <img src="https://www.browarikuchnia.pl/wp-content/uploads/2023/03/0011.jpg" alt="New York" class="d-block w-100">
            </div>
            <div class="carousel-item">
                <img src="https://www.browarikuchnia.pl/wp-content/uploads/2023/03/0012.jpg" alt="New York" class="d-block w-100">
            </div>
            <div class="carousel-item">
                <img src="https://www.browarikuchnia.pl/wp-content/uploads/2023/03/0013.jpg" alt="New York" class="d-block w-100">
            </div>
        </div>


        <button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
    </div>
</section>

<section id="galeria">
    <img class="tlo_strony4" src="https://images.pexels.com/photos/735869/pexels-photo-735869.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2">
</section>

<section id="kucharze">
    <img class="tlo_strony5" src="https://images.pexels.com/photos/2544829/pexels-photo-2544829.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2">

</section>

<section id="kontakt">
    <img class="tlo_strony6" src="https://images.pexels.com/photos/1416530/pexels-photo-1416530.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2">

</section>
</body>
</html>
