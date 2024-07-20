<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truck Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/speedometer.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/gauge.js/dist/gauge.min.js"></script>
    <script src="{{ asset('js/speedometer.js') }}"></script>

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

        .dashboard-container {
            padding: 20px;
        }

        .row1 {
            display: flex;
            justify-content: space-between;
            width: 100%;
            flex-wrap: wrap;
        }

        .map-section,
        .camera-section,
        .speedometer-section {
            flex: 1;
            margin: 10px;
        }

        .map-section,
        .camera-section {
            max-width: 45%;
        }

        .speedometer-section {
            max-width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 10px;
        }

        .map-section h3,
        .camera-section h3,
        .speedometer-section h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        #map-container {
            width: 100%;
            padding: 10px;
            border: 5px solid #3FA2F6;
            border-radius: 10px;
            box-sizing: border-box;
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 5px;
        }

        .camera-section img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border: 5px solid #3FA2F6;
            border-radius: 5px;
        }

        .speedometer-container {
            position: relative;
            text-align: center;
        }

        .speedometer-container h3 {
            position: relative;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 24px;
            padding: 10px;
        }

        .speedometer-section #speedometer-1 {
            width: 100%;
            max-width: 400px;
            height: auto;
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
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
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
            <div class="map-section">
                <h3>Lokasi Truck</h3>
                <div id="map-container">
                    <div id="map"></div>
                </div>
            </div>

            <div class="camera-section">
                <h3>Camera Dashboard Mobil</h3>
                <img src="http://127.0.0.1:5000/video_feed" alt="Camera Feed" id="cameraFeed1">
            </div>

            <div class="speedometer-section">
                <div class="speedometer-container">
                    <h3>Kecepatan Truck</h3>
                    <div id="speedometer-1"></div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <button onclick="goBack()">
            <i class="fas fa-arrow-left"></i>
            <span>Back</span>
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
                .bindPopup('Mencari koordinat Truck...')
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
                    if (data.speed !== undefined) {
                        updateSpeedometer(data.speed);
                    }
                })
                .catch(error => console.error('Error fetching GPS data:', error));
        }

        var speedoMeter1 = new speedometer({
            divFact: 10,
            initVal: 0, // Set initial speed value to 0
            edgeRadius: 170,
            indicatorRadius: 100,
            indicatorNumbRadius: 130,
            speedoNobeW: 100,
            id: 'mani-1'
        });

        document.getElementById('speedometer-1').innerHTML = '';
        document.getElementById('speedometer-1').append(speedoMeter1.elm);

        function updateSpeedometer(speed) {
            speedoMeter1.setPosition(speed); // Adjust this based on how your speedometer's API works
        }

        setInterval(fetchData, 5000);
        fetchData();
    </script>
</body>

</html>
