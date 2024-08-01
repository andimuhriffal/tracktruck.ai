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

        #map-container {
            border: 5px solid #3FA2F6;
            border-radius: 10px;
            box-sizing: border-box;
            overflow: hidden;
        }

        #map {
            height: 450px;
            width: 100%;
            border-radius: 5px;
        }

        .camera-section img {
            width: 70%;
            height: auto;
            object-fit: cover;
            border: 5px solid #3FA2F6;
            border-radius: 5px;
        }

        .speedometer-container {
            text-align: center;
        }

        .speedometer-container h3 {
            font-size: 24px;
            margin: 10px 0;
        }

        .speedometer-section #speedometer-1 {
            width: 100%;
            max-width: 400px;
            height: auto;
            padding: 5px;
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
        <a class="navbar-brand" href="#">TrackTruck.Ai</a>
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
        <div class="row">
            <div class="col-md-6 map-section">
                <h3>Lokasi Truck</h3>
                <div id="map-container">
                    <div id="map"></div>
                </div>
            </div>

            <div class="col-md-6 camera-section">
                <h3>Camera Dashboard Mobil</h3>
		<img src="http://192.168.137.211:5000/video_feed" alt="Camera Feed" id="cameraFeed1">
            </div>

            <div class="col-md-1.7 speedometer-section">
                <div class="speedometer-container">
                    <h3>Kecepatan Truck</h3>
                    <div id="speedometer-1"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var map = L.map('map').setView([-0.981094, 100.392701], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Define custom icon
        var customIcon = L.icon({
            iconUrl: '{{ asset('images/truck.png') }}', // Path to your PNG icon
            iconSize: [35, 40], // size of the icon
            iconAnchor: [12, 41], // point of the icon which will correspond to marker's location
            popupAnchor: [1, -34] // point from which the popup should open relative to the iconAnchor
        });

        function updateMarker(latitude, longitude) {
            if (window.marker) {
                map.removeLayer(window.marker);
            }
            window.marker = L.marker([latitude, longitude], { icon: customIcon }).addTo(map)
                .bindPopup('Lokasi GPS')
                .openPopup();
        }

        function showSearchingMessage() {
            if (window.marker) {
                map.removeLayer(window.marker);
            }
            window.marker = L.marker([-0.981094, 100.392701], { icon: customIcon }).addTo(map)
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
            initVal: 0,
            edgeRadius: 120,
            indicatorRadius: 105,
            indicatorNumbRadius: 75,
            speedoNobeW: 60,
            id: 'mani-1'
        });

        document.getElementById('speedometer-1').innerHTML = '';
        document.getElementById('speedometer-1').append(speedoMeter1.elm);

        function updateSpeedometer(speed) {
            speedoMeter1.setPosition(speed);
        }

        setInterval(fetchData, 5000);
        fetchData();
    </script>
</body>

</html>
