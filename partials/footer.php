<footer class="footer">
  <div class="footer-container">
    <div class="fcol">
      <h3>About Jungle Mart</h3>
      <p>Jungle Mart is a B2B marketplace to explore plant-related products and request quotations directly from suppliers.</p>
    </div>
    <div class="fcol">
      <h3>Quick Links</h3>
      <a href="/index.php">Home</a>
      <a href="/pages/categories.php">Categories</a>
      <a href="/pages/product.php">Products</a>
      <a href="/pages/cart.php">Quotation Cart</a>
      <a href="/pages/contact.php">Contact</a>
    </div>
    <div class="fcol">
      <h3>Newsletter</h3>
      <p>Subscribe for updates and offers</p>
      <form class="newsletter-form">
        <input type="email" placeholder="Your email" required>
        <button type="submit">Subscribe</button>
      </form>
    </div>
    <div class="fcol">
      <h3>Contact</h3>
      <p>Email: info@junglemart.com</p>
      <p>WhatsApp: +91 72060 60607</p>

      <p>Find us on the map</p>
      <div id="map" style="width: 100%; height: 200px; border-radius: 8px; margin-top: 15px;"></div>
    </div>
  </div>
  <div class="footer-bottom">
    © <?php echo date("Y"); ?> Jungle Mart. All rights reserved.
  </div>
</footer>

<!-- Google Maps API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script>
<script>
function initMap() {
  // Jungle Mart office location - Chuliana, Haryana 124501
  const jungleMartLocation = { lat: 28.8167, lng: 76.8333 }; // Chuliana, Haryana coordinates

  const map = new google.maps.Map(document.getElementById('map'), {
    zoom: 15,
    center: jungleMartLocation,
    styles: [
      {
        "featureType": "all",
        "elementType": "geometry.fill",
        "stylers": [
          {
            "weight": "2.00"
          }
        ]
      },
      {
        "featureType": "all",
        "elementType": "geometry.stroke",
        "stylers": [
          {
            "color": "#9c9c9c"
          }
        ]
      },
      {
        "featureType": "all",
        "elementType": "labels.text",
        "stylers": [
          {
            "visibility": "on"
          }
        ]
      },
      {
        "featureType": "landscape",
        "elementType": "all",
        "stylers": [
          {
            "color": "#f2f2f2"
          }
        ]
      },
      {
        "featureType": "landscape",
        "elementType": "geometry.fill",
        "stylers": [
          {
            "color": "#ffffff"
          }
        ]
      },
      {
        "featureType": "landscape.man_made",
        "elementType": "geometry.fill",
        "stylers": [
          {
            "color": "#ffffff"
          }
        ]
      },
      {
        "featureType": "poi",
        "elementType": "all",
        "stylers": [
          {
            "visibility": "off"
          }
        ]
      },
      {
        "featureType": "road",
        "elementType": "all",
        "stylers": [
          {
            "saturation": -100
          },
          {
            "lightness": 45
          }
        ]
      },
      {
        "featureType": "road",
        "elementType": "geometry.fill",
        "stylers": [
          {
            "color": "#eeeeee"
          }
        ]
      },
      {
        "featureType": "road",
        "elementType": "labels.text.fill",
        "stylers": [
          {
            "color": "#7b7b7b"
          }
        ]
      },
      {
        "featureType": "road",
        "elementType": "labels.text.stroke",
        "stylers": [
          {
            "color": "#ffffff"
          }
        ]
      },
      {
        "featureType": "road.highway",
        "elementType": "all",
        "stylers": [
          {
            "visibility": "simplified"
          }
        ]
      },
      {
        "featureType": "road.arterial",
        "elementType": "labels.icon",
        "stylers": [
          {
            "visibility": "off"
          }
        ]
      },
      {
        "featureType": "transit",
        "elementType": "all",
        "stylers": [
          {
            "visibility": "off"
          }
        ]
      },
      {
        "featureType": "water",
        "elementType": "all",
        "stylers": [
          {
            "color": "#46bcec"
          },
          {
            "visibility": "on"
          }
        ]
      },
      {
        "featureType": "water",
        "elementType": "geometry.fill",
        "stylers": [
          {
            "color": "#c8d7d4"
          }
        ]
      },
      {
        "featureType": "water",
        "elementType": "labels.text.fill",
        "stylers": [
          {
            "color": "#070707"
          }
        ]
      },
      {
        "featureType": "water",
        "elementType": "labels.text.stroke",
        "stylers": [
          {
            "color": "#ffffff"
          }
        ]
      }
    ]
  });

  const marker = new google.maps.Marker({
    position: jungleMartLocation,
    map: map,
    title: 'Jungle Mart Office',
    icon: {
      url: 'data:image/svg+xml;charset=UTF-8,%3csvg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"%3e%3ccircle cx="20" cy="20" r="18" fill="%234CAF50" stroke="%23fff" stroke-width="3"/%3e%3cpath d="M20 8 L28 16 L24 16 L24 24 L16 24 L16 16 L12 16 Z" fill="%23fff"/%3e%3c/svg%3e',
      scaledSize: new google.maps.Size(40, 40)
    }
  });

  const infoWindow = new google.maps.InfoWindow({
    content: `
      <div style="font-family: Arial, sans-serif; max-width: 200px;">
        <h3 style="margin: 0 0 8px 0; color: #2E7D32;">Jungle Mart Office</h3>
        <p style="margin: 0; font-size: 14px; color: #666;">
          Your trusted partner for plant-related products and quotations.
        </p>
        <p style="margin: 8px 0 0 0; font-size: 12px; color: #999;">
          📍 Chuliana, Haryana 124501
        </p>
      </div>
    `
  });

  marker.addListener('click', () => {
    infoWindow.open(map, marker);
  });
}

// Fallback for when Google Maps fails to load
window.addEventListener('load', function() {
  setTimeout(function() {
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
      const mapElement = document.getElementById('map');
      if (mapElement) {
        mapElement.innerHTML = `
          <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f5f5f5; border-radius: 8px; color: #666; font-size: 14px; text-align: center; padding: 20px;">
            <div>
              <div style="font-size: 24px; margin-bottom: 10px;">📍</div>
              <div>Map loading...</div>
              <div style="font-size: 12px; margin-top: 5px;">Please check your internet connection</div>
            </div>
          </div>
        `;
      }
    }
  }, 5000);
});
</script>
