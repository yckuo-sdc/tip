$(document).ready(function() {

    // Coordinates for Tawina (example coordinates, replace with actual if different)
    var tawinaCoords = [37.7749, -122.4194]; // Replace with actual coordinates
    var map = L.map('map') 
    var bounds = [
        [21.8369, 119.5343], // Southwest corner
        [25.3008, 122.0067]  // Northeast corner
    ];
    map.fitBounds(bounds);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    L.marker([25.040857, 121.561348]).addTo(map)
        .bindPopup('<b>國防部</b><br>Taiwan')
        .openPopup();

    L.marker([22.922985, 120.289470]).addTo(map)
        .bindPopup('<b>資安院</b><br>Taiwan')
        .openPopup();
});
