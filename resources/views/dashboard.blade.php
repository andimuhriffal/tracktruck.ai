<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truck Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    body {
        background-color: #ffffff;
        position: relative;
        overflow-x: hidden;
    }

    body::before {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('{{ asset('images/background2.jpg') }}');
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        opacity: 0.3;
        z-index: -1;
        filter: blur(1px);
    }

    .dashboard-section {
        margin-bottom: 20px;
    }

    .dashboard-section h3 {
        margin-bottom: 30px;
        font-size: 28px;
        color: #f2f6fa;
        text-align: center;
        margin-top: 10px;
    }

    .dashboard-section img {
        max-width: 100%;
        height: auto;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-top: 1px;
    }

    .row1 {
        display: flex;
        justify-content: space-between;
        width: 100%;
        padding: 30px;
        flex-wrap: wrap;
    }

    .camera-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        text-align: center;
        order: 1;
    }

    .camera-section img {
        width: 50%;
        height: auto;
        object-fit: cover;
        border: 5px solid #3FA2F6;
        border-radius: 5px;
    }

    .camera-section h3 {
        margin-right: 20px;
    }

    .dashboard-section p {
        font-size: 16px;
        color: #ccc;
        margin-bottom: 20px;
    }

    #map-container {
        width: 40%;
        margin-bottom: 20px;
        padding: 10px;
        border: 10px solid #3FA2F6;
        border-radius: 10px;
        position: relative;
        order: 0;
    }

    #map {
        height: 400px;
        width: 100%;
        border-radius: 5px;
        overflow: hidden;
    }

    .nav-link {
        font-weight: bold;
        color: white !important;
    }

    .navbar-brand {
        margin-right: 1rem;
        color: white !important;
    }

    .navbar-nav .nav-link {
        padding: 0.5rem 1rem;
        font-size: 1rem;
        color: white !important;
    }

    @media (max-width: 576px) {
        .navbar-brand {
            margin-right: 0.5rem;
        }
    }

    .text-section {
        margin-top: 20px;
        text-align: center;
        max-width: 400px;
    }

    .row2 {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        width: 100%;
        padding: 20px;
        flex-wrap: wrap;
    }

    .gps-section .camera-section h3,
    .gps-section h3,
    .navbar-dark.bg-brown {
        background-color: #1089FF;
    }

    .navbar-dark.bg-brown .navbar-brand,
    .navbar-dark.bg-brown .navbar-nav .nav-link {
        color: #ffffff !important;
    }

    .navigation-container {
        display: flex;
        justify-content: flex-start;
        margin-top: 20px;
        padding-left: 20px;
    }

    .navigation-buttons {
        text-align: center;
    }

    .navigation-buttons .btn {
        margin: 0 10px;
        padding: 10px 20px;
        font-size: 16px;
        background-color: #3FA2F6;
        color: #ffffff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .navigation-buttons .btn:hover {
        background-color: #3FA2F6;
    }

    footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #96C9F4;
        color: #ffffff;
        padding: 10px 20px;
        text-align: center;
    }

    footer button {
        background-color: transparent;
        border: none;
        color: #1b1818;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    footer button:hover {
        text-decoration: underline;
    }

    .undefined-value {
        color: #BE1C2Ded;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-brown">
        <a class="navbar-brand" href="#">Informasi Truck</a>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="welcome-message" style="color: #ffffff">Welcome, {{ Auth::user()->name }}!</span>
            </li>
            <li class="nav-item">
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link" style="font-weight: bold;">Logout</button>
                </form>
            </li>
        </ul>
    </nav>

    <div class="dashboard-container">
        <div class="row1">
            <div id="map-container">
                <div id="map">
                    <h3>Camera Dashboard</h3>
                </div>
            </div>
            <div class="camera-section">
                <div>
                    <h3>Camera Dashboard</h3>
                    <img src="http://127.0.0.1:5000/video_feed" alt="Camera Feed" id="cameraFeed1">
                </div>
            </div>
        </div>
        <div class="row1">
        </div>
    </div>
    <footer>
        <button onclick="goBack()">
            <i class="fas fa-arrow-left"></i>
            <span></span>
        </button>
    </footer>

    <script>
    var map = L.map('map').setView([-0.981094, 100.392701], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    function updateMarker(latitude, longitude) {
        if (window.marker) {
            map.removeLayer(window.marker);
        }
        window.marker = L.marker([latitude, longitude]).addTo(map)
            .bindPopup('Lokasi GPS')
            .openPopup();
    }

    function showSearchingMessage() {
        if (window.marker) {
            map.removeLayer(window.marker);
        }
        window.marker = L.marker([-0.981094, 100.392701]).addTo(map)
            .bindPopup('Mencari koordinat...')
            .openPopup();
    }

    function fetchData() {
        fetch('/api/gps-data')
            .then(response => response.json())
            .then(data => {
                if (data.status === "searching") {
                    showSearchingMessage();
                } else if (data.latitude && data.longitude) {
                    updateMarker(data.latitude, data.longitude);
                }
            })
            .catch(error => console.error('Error fetching GPS data:', error));
    }

    setInterval(fetchData, 5000);
    fetchData();
</script>
</body>

</html>