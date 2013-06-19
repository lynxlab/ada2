/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function makeMap(Lat, Lon, Zoom, PopupContent) {
    var Position = new L.LatLng(Lat, Lon);
//    var map = L.map('map').setView([51.505, -0.09], 13);
//    var map = L.map('map').setView([41.65,12.52], 13);
    var map = L.map('map').setView(Position, Zoom);
//    var map = L.map('map').setView([LatLon], Zoom);

    // add an OpenStreetMap tile layer
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // add a marker in the given location, attach some popup content to it and open the popup
//    L.marker([51.5, -0.09]).addTo(map)
    L.marker(Position).addTo(map)
        .bindPopup(PopupContent)
//        .bindPopup('A pretty CSS3 popup. <br> Easily customizable.')
        .openPopup();
}
    