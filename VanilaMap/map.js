// ============================================================
//  DATOS DE EJEMPLO
//  Reemplazá mapData con tu fuente real de datos
// ============================================================
const mapData = {
  initialPoint: { lat: -34.9011, lng: -56.1645 },
  initialZoom: 14,
  categories: [
    {
      name: "Cocheras cubiertas",
      color: "#e2b96f",
      points: [
        {
          id: 1,
          title: "Cochera A1",
          city: "Montevideo",
          status: "disponible",
          lat: -34.9011,
          lng: -56.1645,
        },
        {
          id: 2,
          title: "Cochera A2",
          city: "Montevideo",
          status: "ocupado",
          lat: -34.9025,
          lng: -56.166,
        },
        {
          id: 3,
          title: "Cochera A3",
          city: "Montevideo",
          status: "disponible",
          lat: -34.904,
          lng: -56.163,
        },
      ],
    },
    {
      name: "Estacionamiento al aire libre",
      color: "#74b9ff",
      points: [
        {
          id: 4,
          title: "Playa B1",
          city: "Montevideo",
          status: "ocupado",
          lat: -34.9,
          lng: -56.168,
        },
        {
          id: 5,
          title: "Playa B2",
          city: "Montevideo",
          status: "disponible",
          lat: -34.9015,
          lng: -56.17,
        },
        {
          id: 6,
          title: "Playa B3",
          city: "Montevideo",
          status: "ocupado",
          lat: -34.9055,
          lng: -56.1615,
        },
      ],
    },
    {
      name: "Motos y bicicletas",
      color: "#fd79a8",
      points: [
        {
          id: 7,
          title: "Moto C1",
          city: "Montevideo",
          status: "disponible",
          lat: -34.899,
          lng: -56.164,
        },
        {
          id: 8,
          title: "Moto C2",
          city: "Montevideo",
          status: "ocupado",
          lat: -34.9035,
          lng: -56.159,
        },
      ],
    },
  ],
};

// ============================================================
//  INICIALIZACIÓN DEL MAPA
// ============================================================
const map = L.map("map").setView(
  [mapData.initialPoint.lat, mapData.initialPoint.lng],
  mapData.initialZoom,
);

// Tile layer (OpenStreetMap)
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution:
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  maxZoom: 19,
}).addTo(map);

// ============================================================
//  CREACIÓN DE MARCADORES
// ============================================================

/**
 * Genera el SVG del pin con el color dado.
 * @param {string} color  Color hexadecimal
 * @returns {string}
 */
function pinSvg(color) {
  return `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 34" class="marker-pin">
      <defs>
        <filter id="ds" x="-40%" y="-10%" width="180%" height="160%">
          <feDropShadow dx="0" dy="2" stdDeviation="1.5" flood-color="rgba(0,0,0,0.45)"/>
        </filter>
      </defs>
      <path d="M12 0C5.373 0 0 5.373 0 12c0 8 12 22 12 22S24 20 24 12C24 5.373 18.627 0 12 0z"
            fill="${color}" filter="url(#ds)"/>
      <circle cx="12" cy="12" r="5" fill="white" opacity="0.9"/>
    </svg>`;
}

/**
 * Crea un L.DivIcon con el globo de cómic + pin para un punto.
 * @param {{ status: string, color: string }} opts
 * @returns {L.DivIcon}
 */
function createIcon(opts) {
  const { status, color } = opts;
  const label = status === "disponible" ? "Disponible" : "Ocupado";
  const bubbleClass = status === "disponible" ? "disponible" : "ocupado";

  const html = `
    <div class="marker-wrapper">
      <div class="speech-bubble ${bubbleClass}">${label}</div>
      ${pinSvg(color)}
    </div>`;

  return L.divIcon({
    className: "", // Evita clase "leaflet-div-icon" con fondo blanco
    html,
    iconSize: [80, 62],
    iconAnchor: [40, 62], // Punta del pin al centro-fondo
    popupAnchor: [0, -64],
  });
}

// Mapa: id → marcador (para navegación desde sidebar)
const markerMap = new Map();

/**
 * Agrega todos los marcadores al mapa.
 * @param {typeof mapData.categories} categories
 */
function addMarkers(categories) {
  categories.forEach((cat) => {
    cat.points.forEach((point) => {
      const icon = createIcon({ status: point.status, color: cat.color });

      const marker = L.marker([point.lat, point.lng], { icon }).addTo(map);

      markerMap.set(point.id, marker);
    });
  });
}

addMarkers(mapData.categories);

// ============================================================
//  SIDEBAR DINÁMICO
// ============================================================
const sidebarContent = document.getElementById("sidebarContent");

function buildSidebar(categories) {
  sidebarContent.innerHTML = "";

  if (categories.length === 0) {
    sidebarContent.innerHTML =
      '<p style="color:#7a7a9a;padding:1rem;text-align:center">No se encontraron cocheras</p>';
    return;
  }

  categories.forEach((cat) => {
    const block = document.createElement("div");
    block.className = "category-block";

    // Encabezado de categoría
    const btn = document.createElement("button");
    btn.className = "category-btn";
    btn.innerHTML = `
      <span class="category-color-dot" style="background:${cat.color}"></span>
      <span>${cat.name}</span>
      <span class="category-count" style="color:#7a7a9a;font-size:0.78rem">(${cat.points.length})</span>
      <span class="category-arrow">▼</span>`;

    const pointsList = document.createElement("div");
    pointsList.className = "category-points";

    btn.addEventListener("click", () => {
      const isOpen = pointsList.classList.toggle("open");
      btn.querySelector(".category-arrow").classList.toggle("open", isOpen);
    });

    // Puntos de la categoría
    cat.points.forEach((point) => {
      const pointBtn = document.createElement("button");
      pointBtn.className = "point-btn";
      const statusClass =
        point.status === "disponible" ? "disponible" : "ocupado";
      const statusLabel =
        point.status === "disponible" ? "Disponible" : "Ocupado";

      pointBtn.innerHTML = `
        <span>${point.title}${point.city ? ` <small style="color:#7a7a9a">· ${point.city}</small>` : ""}</span>
        <span class="status-badge ${statusClass}">${statusLabel}</span>`;

      pointBtn.addEventListener("click", () => {
        const marker = markerMap.get(point.id);
        if (marker) {
          map.flyTo([point.lat, point.lng], 15, { duration: 0.8 });
        }

        // Cerrar sidebar en móvil
        if (window.innerWidth <= 700) {
          document.getElementById("sidebar").classList.remove("open");
        }
      });

      pointsList.appendChild(pointBtn);
    });

    block.appendChild(btn);
    block.appendChild(pointsList);
    sidebarContent.appendChild(block);
  });
}

buildSidebar(mapData.categories);

// ============================================================
//  BÚSQUEDA
// ============================================================
document.getElementById("searchInput").addEventListener("input", (e) => {
  const term = e.target.value
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");

  const filtered = mapData.categories
    .map((cat) => ({
      ...cat,
      points: cat.points.filter(
        (p) =>
          p.title
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .includes(term) ||
          (p.city || "")
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .includes(term),
      ),
    }))
    .filter((cat) => cat.points.length > 0);

  buildSidebar(filtered);

  // Mostrar/ocultar marcadores según búsqueda
  const filteredIds = new Set(
    filtered.flatMap((cat) => cat.points.map((p) => p.id)),
  );
  markerMap.forEach((marker, id) => {
    if (filteredIds.size === 0 && term === "") {
      // Sin término → mostrar todos
      if (!map.hasLayer(marker)) map.addLayer(marker);
    } else if (filteredIds.has(id)) {
      if (!map.hasLayer(marker)) map.addLayer(marker);
    } else {
      if (map.hasLayer(marker)) map.removeLayer(marker);
    }
  });
});

// ============================================================
//  BOTÓN MENÚ (MÓVIL)
// ============================================================
document.getElementById("menuBtn").addEventListener("click", () => {
  document.getElementById("sidebar").classList.toggle("open");
});
