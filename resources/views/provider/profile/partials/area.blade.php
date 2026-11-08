{{-- Service area: area name, map pin (Leaflet + OpenStreetMap) and radius. --}}
<section id="area" class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-semibold mb-1">Service area</h2>
    <p class="text-sm text-gray-600 mb-3">Click the map to place your pin, then choose how far you can travel. Customers will only see the area name and the distance, not your exact pin.</p>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <form method="POST" action="{{ route('provider.area.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="area_name" class="block text-sm font-medium">Area name (for example: Mirpur, Dhaka)</label>
            <input type="text" id="area_name" name="area_name" maxlength="100"
                   value="{{ old('area_name', $profile->area_name) }}"
                   class="border rounded p-2 w-full">
        </div>

        <div id="map" class="w-full rounded border" style="height: 320px;"></div>

        <div class="flex flex-wrap gap-4">
            <div>
                <label for="latitude" class="block text-sm font-medium">Latitude</label>
                <input type="text" id="latitude" name="latitude" readonly
                       value="{{ old('latitude', $profile->latitude) }}"
                       class="border rounded p-2 w-40 bg-gray-100">
            </div>
            <div>
                <label for="longitude" class="block text-sm font-medium">Longitude</label>
                <input type="text" id="longitude" name="longitude" readonly
                       value="{{ old('longitude', $profile->longitude) }}"
                       class="border rounded p-2 w-40 bg-gray-100">
            </div>
            <div>
                <label for="radius_km" class="block text-sm font-medium">Radius in km (1 to 50)</label>
                <input type="number" id="radius_km" name="radius_km" min="1" max="50"
                       value="{{ old('radius_km', $profile->radius_km ?? 10) }}"
                       class="border rounded p-2 w-28">
            </div>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save service area</button>
    </form>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var latInput = document.getElementById('latitude');
        var lngInput = document.getElementById('longitude');
        var radiusInput = document.getElementById('radius_km');

        // Start at the saved pin. If there is none yet, start in Dhaka.
        var hasPin = latInput.value !== '' && lngInput.value !== '';
        var startLat = parseFloat(latInput.value) || 23.8103;
        var startLng = parseFloat(lngInput.value) || 90.4125;

        var map = L.map('map').setView([startLat, startLng], 12);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = null;
        var circle = null;

        // The circle shows how far the provider can travel.
        function updateCircle() {
            if (circle !== null) {
                circle.setRadius((parseInt(radiusInput.value) || 0) * 1000);
            }
        }

        // Put the pin and the circle on the map.
        function showPin(lat, lng) {
            if (marker === null) {
                marker = L.marker([lat, lng]).addTo(map);
                circle = L.circle([lat, lng], { radius: 1000 }).addTo(map);
            } else {
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
            }
            updateCircle();
        }

        // When the provider clicks the map: move the pin and fill the two boxes.
        map.on('click', function (e) {
            var point = e.latlng.wrap();
            latInput.value = point.lat.toFixed(7);
            lngInput.value = point.lng.toFixed(7);
            showPin(point.lat, point.lng);
        });

        radiusInput.addEventListener('input', updateCircle);

        if (hasPin) {
            showPin(startLat, startLng);
        }
    </script>
</section>
