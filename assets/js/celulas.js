(() => {
  const main = document.querySelector("main[data-base-url]");
  const BASE_URL = main?.dataset.baseUrl || "";
  const USER_ROLE = main?.dataset.userRole || "";
  let opciones = {
    lideres: [],
    areas: [],
    usuarios: [],
    estados: [],
    dias: [],
  };
  let paginaActual = 1;
  let totalPaginas = 1;

  document.addEventListener("DOMContentLoaded", () => {
    cargarOpciones();
    cargarCelulas();
    const buscar = document.getElementById("filtro-buscar");
    if (buscar) {
      buscar.addEventListener("keyup", (e) => {
        if (e.key === "Enter") cargarCelulas();
      });
    }
    if (["pastor", "lider_area"].includes(USER_ROLE)) {
      const btnNueva = document.getElementById("btn-nueva");
      if (btnNueva) btnNueva.classList.remove("hidden");
    }
  });

  async function cargarOpciones() {
    // áreas desde API nueva
    try {
      const respAreas = await fetch(`${BASE_URL}api/areas.php?accion=listar&estado=activa&limite=500`);
      const jsonAreas = await respAreas.json();
      opciones.areas = jsonAreas?.data || [];
    } catch (e) { opciones.areas = []; }

    const resp = await fetch(`${BASE_URL}api/celulas.php?accion=opciones`);
    const json = await resp.json();
    if (!json.exito) return;
    opciones = { ...json.data, areas: opciones.areas.length ? opciones.areas : json.data.areas };
    poblarSelect("lider_id", opciones.lideres, "Seleccione líder");
    poblarSelect(
      "lider_area_id",
      opciones.usuarios,
      "Seleccione líder de área",
    );
    poblarSelect("anfitrion_id", opciones.usuarios, "Seleccione anfitrión");
    poblarSelect("area_servicio_id", opciones.areas, "Seleccione área");
    poblarSelect("filtro-area", opciones.areas, "Todas");
    poblarSelect(
      "dia_semana",
      opciones.dias.map((d) => ({ id: d, nombre: d })),
      "Seleccione día",
    );
    poblarSelect(
      "estado",
      opciones.estados.map((e) => ({
        id: e,
        nombre: e.charAt(0).toUpperCase() + e.slice(1),
      })),
      "",
    );
    poblarSelect(
      "filtro-estado",
      opciones.estados.map((e) => ({
        id: e,
        nombre: e.charAt(0).toUpperCase() + e.slice(1),
      })),
      "Todos",
    );
  }

  function poblarSelect(id, data, placeholder) {
    const sel = document.getElementById(id);
    if (!sel) return;
    sel.innerHTML = "";
    if (placeholder) {
      const opt = document.createElement("option");
      opt.value = "";
      opt.textContent = placeholder;
      sel.appendChild(opt);
    }
    data.forEach((item) => {
      const opt = document.createElement("option");
      opt.value = item.id;
      opt.textContent = item.nombre_completo || item.nombre;
      sel.appendChild(opt);
    });
  }

  async function cargarCelulas() {
    setLoading(true);

    const termino =
      document.getElementById("filtro-buscar")?.value.trim() || "";
    const areaSel = document.getElementById("filtro-area")?.value || "";
    const estadoSel = document.getElementById("filtro-estado")?.value;

    const params = new URLSearchParams({
      accion: "listar",
      pagina: paginaActual,
    });

    if (termino) {
      params.append("q", termino);
    }

    if (areaSel) {
      params.append("area_id", areaSel);
    }

    /**
     * SOLO enviamos estado
     * si el usuario selecciona uno
     */

    if (estadoSel && estadoSel !== "") {
      params.append("estado", estadoSel);
    }

    const resp = await fetch(`${BASE_URL}api/celulas.php?${params.toString()}`);

    const json = await resp.json();

    if (json.exito) {
      renderTabla(json.data);

      renderPaginacion(json.total, json.limite, json.pagina);
    }

    setLoading(false);
  }

  window.cargarCelulas = cargarCelulas;

  function renderTabla(celulas) {
    const tbody = document.getElementById("tabla-celulas");
    const cards = document.getElementById("cards-celulas");
    if (cards) cards.innerHTML = "";
    if (!tbody) return;
    tbody.innerHTML = "";
    if (!celulas || celulas.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="8" class="px-4 py-6 text-center text-gray-500 text-sm">Sin resultados</td></tr>';
      if (cards)
        cards.innerHTML =
          '<div class="bg-white rounded-xl shadow p-4 text-center text-gray-500 text-sm">Sin resultados</div>';
      return;
    }
    celulas.forEach((c) => {
      const tr = document.createElement("tr");
      tr.className = "border-b hover:bg-gray-50";
      const hora = (c.hora_inicio || "").substring(0, 5);
      tr.innerHTML = `
        <td class="px-3 sm:px-4 py-3 font-medium text-gray-900">${c.nombre}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${c.lider || "—"}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700 hidden sm:table-cell">${c.area_servicio || ""}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${c.dia_semana} · ${hora}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700 hidden sm:table-cell">${c.zona || ""}</td>
        <td class="px-3 sm:px-4 py-3 hidden sm:table-cell">
            <span class="px-2 py-1 rounded-full text-xs ${badgeColor(c.estado)}">${c.estado}</span>
        </td>
        <td class="px-3 sm:px-4 py-3 text-right text-gray-700 hidden sm:table-cell">${c.cantidad_promedio_asistentes ?? 0}</td>
        <td class="px-3 sm:px-4 py-3">
            ${accionButtons(c.id)}
        </td>
      `;
      tbody.appendChild(tr);

      // Tarjeta para mobile
      if (cards) {
        const horaCard = (c.hora_inicio || "").substring(0, 5);

        const card = document.createElement("div");

        card.className =
          "w-full bg-white rounded-xl shadow p-4 flex flex-col gap-3";

        card.innerHTML = `
  
  <div class="flex justify-between items-start gap-2 w-full">

      <div class="flex-1 min-w-0">
          <p class="text-xs text-gray-500 truncate">${c.area_servicio || ""}</p>

          <p class="text-lg font-semibold text-gray-900 break-words">
              ${c.nombre}
          </p>

          <p class="text-sm text-gray-600">
              Líder: ${c.lider || "—"}
          </p>
      </div>

      <span class="px-2 py-1 rounded-full text-xs ${badgeColor(c.estado)} whitespace-nowrap">
          ${c.estado}
      </span>

  </div>

  <div class="text-sm text-gray-700 flex flex-wrap gap-x-4 gap-y-1 w-full">

      <span class="flex items-center gap-1">
          📅 ${c.dia_semana} · ${horaCard}
      </span>

      <span class="flex items-center gap-1">
          📍 ${c.zona || "Sin zona"}
      </span>

      <span class="flex items-center gap-1">
          👥 ${c.cantidad_promedio_asistentes ?? 0}
      </span>

  </div>

  <div class="flex flex-wrap gap-2 pt-1 w-full">
      ${accionButtons(c.id)}
  </div>

  `;

        cards.appendChild(card);
      }
    });
  }

  function renderPaginacion(total, limite, pagina) {
    const cont = document.getElementById("paginacion");
    if (!cont) return;
    totalPaginas = Math.max(1, Math.ceil(total / limite));
    paginaActual = pagina;
    cont.innerHTML = `
      <span class="text-gray-600">Página ${pagina} de ${totalPaginas} · ${total} registros</span>
      <div class="space-x-2">
          <button class="px-3 py-1 rounded-lg ${pagina <= 1 ? "bg-gray-200 text-gray-400" : "bg-white border"}" ${pagina <= 1 ? "disabled" : ""} onclick="cambiarPagina(${pagina - 1})">Anterior</button>
          <button class="px-3 py-1 rounded-lg ${pagina >= totalPaginas ? "bg-gray-200 text-gray-400" : "bg-white border"}" ${pagina >= totalPaginas ? "disabled" : ""} onclick="cambiarPagina(${pagina + 1})">Siguiente</button>
      </div>`;
  }

  window.cambiarPagina = function (p) {
    if (p < 1 || p > totalPaginas) return;
    paginaActual = p;
    cargarCelulas();
  };

  function accionButtons(id) {
    const base =
      "inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold";
    const icon = (path, color) =>
      `<svg class="w-4 h-4 sm:w-5 sm:h-5 ${color}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="${path}"/></svg>`;
    const historialBtn = `<button class="${base} text-indigo-600 bg-indigo-50 hover:bg-indigo-100" onclick="verHistorial(${id})" aria-label="Historial">${icon("M12 20.5a8.5 8.5 0 1 1 8.5-8.5v.5H17", "text-indigo-600")}</button>`;
    if (!["pastor", "lider_area"].includes(USER_ROLE))
      return `<div class="flex flex-wrap gap-2 justify-center">${historialBtn}</div>`;
    const editarBtn = `<button class="${base} text-purple-600 bg-purple-50 hover:bg-purple-100" onclick="editarCelula(${id})" aria-label="Editar">${icon("M4 20h4.5L19 9.5l-4.5-4.5L4 15.5V20zm11-11l-1.5-1.5", "text-purple-600")}</button>`;
    const eliminarBtn = `<button class="${base} text-red-600 bg-red-50 hover:bg-red-100" onclick="confirmarEliminar(${id})" aria-label="Eliminar">${icon("M6 7h12M10 11v6M14 11v6M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M5 7h14l-1 12a2 2 0 0 1-2 1.8H8a2 2 0 0 1-2-1.8L5 7z", "text-red-600")}</button>`;
    return `<div class="flex flex-wrap gap-2 justify-center">${historialBtn}${editarBtn}${eliminarBtn}</div>`;
  }

  function badgeColor(estado) {
    if (estado === "activa") return "bg-green-100 text-green-700";
    if (estado === "pausada") return "bg-amber-100 text-amber-700";
    return "bg-gray-200 text-gray-700";
  }

  window.abrirModal = function (celula = null) {
    document.getElementById("modal").classList.remove("hidden");
    if (celula) {
      document.getElementById("modal-titulo").textContent = "Editar célula";
      document.getElementById("celula-id").value = celula.id;
      document.getElementById("nombre").value = celula.nombre;
      document.getElementById("lider_id").value = celula.lider_id;
      document.getElementById("area_servicio_id").value =
        celula.area_servicio_id;
      document.getElementById("zona").value = celula.zona ?? "";
      document.getElementById("direccion").value = celula.direccion ?? "";
      document.getElementById("dia_semana").value = celula.dia_semana;
      document.getElementById("hora_inicio").value = (
        celula.hora_inicio || ""
      ).substring(0, 5);
      document.getElementById("estado").value = celula.estado;
      document.getElementById("cantidad_promedio_asistentes").value =
        celula.cantidad_promedio_asistentes ?? 0;
    } else {
      document.getElementById("modal-titulo").textContent = "Nueva célula";
      document.getElementById("form-celula").reset();
      document.getElementById("celula-id").value = "";
    }
  };

  window.cerrarModal = function () {
    document.getElementById("modal").classList.add("hidden");
  };

  window.guardarCelula = async function (e) {
    e.preventDefault();
    const id = document.getElementById("celula-id").value;
    const payload = {
      nombre: document.getElementById("nombre").value,
      lider_id: document.getElementById("lider_id").value,
      lider_area_id: document.getElementById("lider_area_id").value,
      anfitrion_id: document.getElementById("anfitrion_id").value,
      area_servicio_id: document.getElementById("area_servicio_id").value,
      zona: document.getElementById("zona").value,
      direccion: document.getElementById("direccion").value,
      dia_semana: document.getElementById("dia_semana").value,
      hora_inicio: document.getElementById("hora_inicio").value,
      estado: document.getElementById("estado").value,
      cantidad_promedio_asistentes: document.getElementById(
        "cantidad_promedio_asistentes",
      ).value,
    };
    const accion = id ? "actualizar&id=" + id : "crear";
    setLoading(true, "guardando");
    const resp = await fetch(`${BASE_URL}api/celulas.php?accion=${accion}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const json = await resp.json();
    if (json.exito) {
      cerrarModal();
      cargarCelulas();
      toast("Guardado correctamente");
    } else {
      toast(json.mensaje || "No se pudo guardar", true);
    }
    setLoading(false);
  };

  window.editarCelula = async function (id) {
    const resp = await fetch(
      `${BASE_URL}api/celulas.php?accion=obtener&id=${id}`,
    );
    const json = await resp.json();
    if (json.exito) abrirModal(json.data);
  };

  window.confirmarEliminar = function (id) {
    document.getElementById("confirm-mensaje").textContent =
      "¿Desactivar esta célula?";
    document.getElementById("confirm-aceptar").onclick = () =>
      eliminarCelula(id);
    document.getElementById("modal-confirm").classList.remove("hidden");
  };

  window.cerrarConfirm = function () {
    document.getElementById("modal-confirm").classList.add("hidden");
  };

  async function eliminarCelula(id) {
    const resp = await fetch(
      `${BASE_URL}api/celulas.php?accion=eliminar&id=${id}`,
      { method: "POST" },
    );
    const json = await resp.json();
    cerrarConfirm();
    if (json.exito) {
      toast("Célula desactivada");
      cargarCelulas();
    } else {
      toast(json.mensaje || "No se pudo eliminar", true);
    }
  }

  window.limpiarFiltros = function () {
    document.getElementById("filtro-buscar").value = "";
    document.getElementById("filtro-area").value = "";
    document.getElementById("filtro-estado").value = "";
    cargarCelulas();
  };

  function toast(mensaje, error = false) {
    const t = document.getElementById("toast");
    t.textContent = mensaje;
    t.className = `fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-sm ${error ? "bg-red-600 text-white" : "bg-gray-900 text-white"} animate-fade`;
    t.classList.remove("hidden");
    setTimeout(() => t.classList.add("hidden"), 2500);
  }

  function setLoading(estado, contexto = "") {
    const form = document.getElementById("form-celula");
    if (!form) return;
    [...form.elements].forEach(
      (el) => (el.disabled = estado && contexto === "guardando"),
    );
  }

  window.verHistorial = async function (id) {
    document.getElementById("historial-body").innerHTML =
      '<tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Cargando...</td></tr>';
    document.getElementById("modal-historial").classList.remove("hidden");
    try {
      const resp = await fetch(
        `${BASE_URL}api/celulas.php?accion=historial&id=${id}`,
      );
      const json = await resp.json();
      if (json.exito) {
        renderHistorial(json.data);
      } else {
        document.getElementById("historial-body").innerHTML =
          '<tr><td colspan="6" class="px-3 py-4 text-center text-red-500">No se pudo cargar historial</td></tr>';
      }
    } catch (e) {
      document.getElementById("historial-body").innerHTML =
        '<tr><td colspan="6" class="px-3 py-4 text-center text-red-500">Error al cargar</td></tr>';
    }
  };

  function renderHistorial(data) {
    const tbody = document.getElementById("historial-body");
    if (!data || data.length === 0) {
      tbody.innerHTML =
        '<tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">Sin reuniones registradas</td></tr>';
      return;
    }
    tbody.innerHTML = "";
    data.forEach((r) => {
      const tr = document.createElement("tr");
      tr.className = "border-b";
      tr.innerHTML = `
        <td class="px-3 py-2 text-gray-700">${r.fecha_reunion ?? ""}</td>
        <td class="px-3 py-2 text-gray-700">${r.cantidad_asistentes ?? 0}</td>
        <td class="px-3 py-2 text-gray-700">${r.cantidad_nuevos ?? 0}</td>
        <td class="px-3 py-2 text-gray-700">${r.realizada ? "Sí" : "No"}</td>
        <td class="px-3 py-2 text-gray-700">${r.ofrenda_monto ? "$" + parseFloat(r.ofrenda_monto).toFixed(2) : "-"}</td>
        <td class="px-3 py-2 text-gray-700">${r.comentarios ?? ""}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  window.cerrarHistorial = function () {
    document.getElementById("modal-historial").classList.add("hidden");
  };
})();
