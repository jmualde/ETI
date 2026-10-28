const botonTema = document.getElementById("botonTema");
const temaGuardado = localStorage.getItem("tema");

function aplicarTema(tema) {
    const modoClaro = tema === "light";
    document.documentElement.dataset.theme = tema;
    document.body.dataset.theme = tema;
    botonTema.setAttribute("aria-pressed", String(modoClaro));
    botonTema.textContent = modoClaro
        ? "Activar modo oscuro"
        : "Activar modo claro";
}

aplicarTema(temaGuardado === "light" ? "light" : "dark");

botonTema.addEventListener("click", () => {
    const nuevoTema = document.body.dataset.theme === "light"
        ? "dark"
        : "light";

    localStorage.setItem("tema", nuevoTema);
    aplicarTema(nuevoTema);
});
