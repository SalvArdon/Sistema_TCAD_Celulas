(() => {
  const main = document.querySelector('main[data-base-url]');
  const BASE_URL = main?.dataset.baseUrl || '';
  let paginaActual = 1;
  let totalPaginas = 1;
  let areas = [];
  let selectedAreas = [];
  let chipBox, suggestBox, searchInput, hiddenSelect;

  document.addEventListener('DOMContentLoaded', () => {
    cargarAreas();
    cargarServidores();
    setupAreaPicker();
    const q = document.getElementById('filtro-q');
    if (q) q.addEventListener('keyup', e => { if (e.key === 'Enter') cargarServidores(); });
  });

  function setupAreaPicker() {
    hiddenSelect = document.getElementById('srv-area');
    if (!hiddenSelect) return;
    hiddenSelect.classList.add('hidden');

    const wrapper = document.createElement('div');
    wrapper.className = 'w-full border rounded-lg px-3 py-2.5 text-sm bg-white';

    chipBox = document.createElement('div');
    chipBox.id = 'srv-area-chips';
    chipBox.className = 'flex flex-wrap gap-2 mb-2';

    searchInput = document.createElement('input');
    searchInput.id = 'srv-area-search';
    searchInput.type = 'text';
    searchInput.placeholder = 'Buscar ministerio / área';
    searchInput.className = 'w-full focus:outline-none';
    searchInput.autocomplete = 'off';
    searchInput.addEventListener('focus', () => renderSuggestions(searchInput.value));
    searchInput.addEventListener('input', () => renderSuggestions(searchInput.value));

    suggestBox = document.createElement('div');
    suggestBox.id = 'srv-area-suggestions';
    suggestBox.className = 'hidden mt-2 max-h-40 overflow-y-auto border rounded-lg bg-white shadow text-sm';

    hiddenSelect.parentNode.insertBefore(wrapper, hiddenSelect);
    wrapper.appendChild(chipBox);
    wrapper.appendChild(searchInput);
    wrapper.appendChild(suggestBox);

    // inicializar desde opciones seleccionadas
    selectedAreas = Array.from(hiddenSelect.selectedOptions).map(o => ({id:o.value, nombre:o.textContent}));
    renderChips();
  }

  async function cargarAreas() {
    try {
      const resp = await fetch(`${BASE_URL}api/areas.php?accion=listar&estado=activa&limite=500`);
      const json = await resp.json();
      areas = json?.data || json?.data?.data || json?.areas || [];
      poblarSelect('filtro-area', areas, 'Todas', false);
      poblarSelect('srv-area', areas, 'Seleccione áreas', true);
    } catch (e) { console.error(e); }
  }

  function poblarSelect(id, data, placeholder, multiple=false) {
    const sel = document.getElementById(id);
    if (!sel) return;
    sel.innerHTML = '';
    sel.multiple = !!multiple;
    if (placeholder && !multiple) {
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = placeholder;
      sel.appendChild(opt);
    }
    data.forEach(a => {
      const opt = document.createElement('option');
      opt.value = a.id;
      opt.textContent = a.nombre;
      sel.appendChild(opt);
    });

    // si es el select oculto principal, refrescar chips
    if (id === 'srv-area' && selectedAreas.length === 0) {
      selectedAreas = Array.from(sel.selectedOptions).map(o => ({id:o.value, nombre:o.textContent}));
      renderChips();
    }
  }

  async function cargarServidores() {
    setLoading(true);
    const q = document.getElementById('filtro-q')?.value || '';
    const area = document.getElementById('filtro-area')?.value || '';
    const params = new URLSearchParams({ accion: 'listar', pagina: paginaActual });
    if (q) params.append('q', q);
    if (area) params.append('area_servicio_id', area);
    try {
      const resp = await fetch(`${BASE_URL}api/servidores.php?${params.toString()}`);
      const text = await resp.text();
      let json;
      try { json = JSON.parse(text); } catch (e) { throw new Error(text || 'Respuesta no válida'); }
      if (!resp.ok || !json.exito) throw new Error(json.error || json.mensaje || 'Error al listar');
      renderTabla(json.data);
      renderPaginacion(json.total, json.limite, json.pagina);
    } catch (e) {
      renderTabla([]);
      toast(e.message || 'Error cargando servidores', true);
    } finally {
      setLoading(false);
    }
  }
  window.cargarServidores = cargarServidores;

  function renderTabla(data) {
    const tbody = document.getElementById('tabla-servidores');
    const cards = document.getElementById('cards-servidores');
    if (cards) cards.innerHTML = '';
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-500 text-sm">Sin resultados</td></tr>';
      if (cards) cards.innerHTML = '<div class="bg-white rounded-xl shadow p-4 text-center text-gray-500 text-sm">Sin resultados</div>';
      return;
    }
    data.forEach(s => {
      const tr = document.createElement('tr');
      tr.className = 'border-b hover:bg-gray-50';
      tr.innerHTML = `
        <td class="px-3 sm:px-4 py-3 text-gray-900 font-medium">${s.nombre_completo || ''}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${s.email || ''}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${s.telefono || '—'}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${s.areas || s.area_nombre || '—'}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${s.fecha_ingreso || '—'}</td>
        <td class="px-3 sm:px-4 py-3 text-center">${accionButtons(s)}</td>
      `;
      tbody.appendChild(tr);

      if (cards) {
        const card = document.createElement('div');
        card.className = 'w-full bg-white rounded-xl shadow p-4 flex flex-col gap-3';
        card.innerHTML = `
          <div class="flex justify-between items-start gap-2">
            <div class="flex-1 min-w-0">
              <p class="text-xs text-gray-500 truncate">${s.areas || s.area_nombre || '—'}</p>
              <p class="text-lg font-semibold text-gray-900 break-words">${s.nombre_completo || ''}</p>
              <p class="text-sm text-gray-600 truncate">${s.email || ''}</p>
            </div>
          </div>
          <div class="text-sm text-gray-700 flex flex-wrap gap-x-4 gap-y-1 w-full">
            <span class="flex items-center gap-1">📞 ${s.telefono || '—'}</span>
            <span class="flex items-center gap-1">📅 ${s.fecha_ingreso || '—'}</span>
            <span class="flex items-center gap-1"># ${s.codigo_membresia || '—'}</span>
          </div>
          <div class="flex flex-wrap gap-2 pt-1 w-full">${accionButtons(s)}</div>
        `;
        cards.appendChild(card);
      }
    });
  }

  function accionButtons(s) {
    const base = "inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold";
    const icon = (path, color) => `<svg class="w-4 h-4 sm:w-5 sm:h-5 ${color}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="${path}"/></svg>`;
    const verBtn = `<button class="${base} text-indigo-600 bg-indigo-50 hover:bg-indigo-100" onclick="verServidor(${s.id})" aria-label="Ver detalle">${icon('M12 20.5c5 0 9-4.5 9-8.5s-4-8.5-9-8.5-9 4.5-9 8.5 4 8.5 9 8.5zm0-5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z', 'text-indigo-600')}</button>`;
    const editBtn = `<button class="${base} text-purple-600 bg-purple-50 hover:bg-purple-100" onclick="editarServidor(${s.id})" aria-label="Editar">${icon('M4 20h4.5L19 9.5l-4.5-4.5L4 15.5V20zm11-11l-1.5-1.5', 'text-purple-600')}</button>`;
    const delBtn = `<button class="${base} text-red-600 bg-red-50 hover:bg-red-100" onclick="eliminarServidor(${s.id})" aria-label="Eliminar">${icon('M6 7h12M10 11v6M14 11v6M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M5 7h14l-1 12a2 2 0 0 1-2 1.8H8a2 2 0 0 1-2-1.8L5 7z', 'text-red-600')}</button>`;
    return `<div class="flex flex-wrap gap-2 justify-center">${verBtn}${editBtn}${delBtn}</div>`;
  }

  window.verServidor = async function(id) {
    const resp = await fetch(`${BASE_URL}api/servidores.php?accion=obtener&id=${id}`);
    const json = await resp.json();
    if (!json.exito) return;
    const s = json.data;
    document.getElementById('det-nombre').textContent = s.nombre_completo || '';
    document.getElementById('det-email').textContent = s.email || '—';
    document.getElementById('det-telefono').textContent = s.telefono || '—';
    document.getElementById('det-areas').textContent = s.areas || s.area_nombre || '—';
    document.getElementById('det-codigo').textContent = s.codigo_membresia || '—';
    document.getElementById('det-ingreso').textContent = s.fecha_ingreso || '—';
    document.getElementById('det-dui').textContent = s.dui || '—';
    document.getElementById('det-genero').textContent = s.genero || '—';
    document.getElementById('det-bautizado').textContent = s.bautizado ? 'Sí' : 'No';
    document.getElementById('det-bautizo-fecha').textContent = s.fecha_bautizo || '—';
    document.getElementById('det-nacimiento').textContent = s.fecha_nacimiento || '—';
    document.getElementById('modal-det-servidor')?.classList.remove('hidden');
  };

  window.cerrarDetalleServidor = function() {
    document.getElementById('modal-det-servidor')?.classList.add('hidden');
  };

  window.editarServidor = async function(id) {
    await cargarAreas();
    const resp = await fetch(`${BASE_URL}api/servidores.php?accion=obtener&id=${id}`);
    const json = await resp.json();
    if (!json.exito) return;
    const s = json.data;
    document.getElementById('srv-id').value = s.id;
    document.getElementById('srv-nombre').value = s.nombre_completo || '';
    document.getElementById('srv-email').value = s.email || '';
    document.getElementById('srv-telefono').value = s.telefono || '';
    document.getElementById('srv-dui').value = s.dui || '';
    document.getElementById('srv-genero').value = s.genero || '';
    document.getElementById('srv-nacimiento').value = s.fecha_nacimiento || '';
    document.getElementById('srv-bautizado').checked = !!s.bautizado;
    document.getElementById('srv-bautizo-fecha').value = s.fecha_bautizo || '';
    const areaSel = document.getElementById('srv-area');
    if (areaSel) {
      const ids = (s.area_ids || '').toString().split(',').filter(Boolean);
      Array.from(areaSel.options).forEach(opt => {
        opt.selected = ids.includes(opt.value);
      });
      selectedAreas = areas.filter(a => ids.includes(String(a.id)));
      renderChips();
      renderSuggestions(searchInput?.value || '');
    }
    document.getElementById('srv-direccion').value = s.direccion || '';
    document.getElementById('srv-fecha').value = s.fecha_ingreso || '';
    document.getElementById('srv-codigo').value = s.codigo_membresia || '';
    document.getElementById('modal-servidor-titulo').textContent = 'Editar servidor';
    document.getElementById('modal-servidor')?.classList.remove('hidden');
  };

  window.eliminarServidor = async function(id) {
    if (!confirm('¿Eliminar este servidor?')) return;
    await fetch(`${BASE_URL}api/servidores.php?accion=eliminar&id=${id}`, { method: 'POST' });
    toast('Servidor eliminado');
    cargarServidores();
  };

  window.abrirModalServidor = function() {
    cargarAreas();
    document.getElementById('form-servidor').reset();
    document.getElementById('srv-id').value = '';
    document.getElementById('srv-bautizado').checked = false;
    selectedAreas = [];
    renderChips();
    renderSuggestions('');
    document.getElementById('modal-servidor-titulo').textContent = 'Nuevo servidor';
    document.getElementById('modal-servidor')?.classList.remove('hidden');
  };

  window.cerrarModalServidor = function() {
    document.getElementById('modal-servidor')?.classList.add('hidden');
  };

  window.guardarServidor = async function(e) {
    e.preventDefault();
    const areaSel = document.getElementById('srv-area');
    // asegurar que el select tenga los mismos seleccionados que los chips
    if (areaSel) {
      Array.from(areaSel.options).forEach(opt => {
        opt.selected = selectedAreas.some(a => String(a.id) === opt.value);
      });
    }
    const areasSel = selectedAreas.map(a => a.id);
    if (!areasSel.length) {
      toast('Selecciona al menos un área', true);
      return;
    }
    const payload = {
      nombre_completo: document.getElementById('srv-nombre').value,
      email: document.getElementById('srv-email').value,
      telefono: document.getElementById('srv-telefono').value,
      dui: document.getElementById('srv-dui').value,
      genero: document.getElementById('srv-genero').value,
      fecha_nacimiento: document.getElementById('srv-nacimiento').value,
      bautizado: document.getElementById('srv-bautizado').checked ? 1 : 0,
      fecha_bautizo: document.getElementById('srv-bautizo-fecha').value,
      areas: areasSel,
      direccion: document.getElementById('srv-direccion').value,
      fecha_ingreso: document.getElementById('srv-fecha').value,
      codigo_membresia: document.getElementById('srv-codigo').value
    };
    const id = document.getElementById('srv-id').value;
    const accion = id ? 'actualizar&id='+id : 'crear';
    const resp = await fetch(`${BASE_URL}api/servidores.php?accion=${accion}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await resp.json();
    if (json.exito) {
      toast('Guardado');
      cerrarModalServidor();
      cargarServidores();
    } else {
      toast(json.mensaje || 'Error al guardar', true);
    }
  };

  function renderPaginacion(total, limite, pagina) {
    const cont = document.getElementById('paginacion-servidores');
    if (!cont) return;
    totalPaginas = Math.max(1, Math.ceil(total / limite));
    paginaActual = pagina;
    cont.innerHTML = `
      <span class="text-gray-600">Página ${pagina} de ${totalPaginas} · ${total} registros</span>
      <div class="space-x-2">
          <button class="px-3 py-1 rounded-lg ${pagina<=1?'bg-gray-200 text-gray-400':'bg-white border'}" ${pagina<=1?'disabled':''} onclick="cambiarPaginaServidores(${pagina-1})">Anterior</button>
          <button class="px-3 py-1 rounded-lg ${pagina>=totalPaginas?'bg-gray-200 text-gray-400':'bg-white border'}" ${pagina>=totalPaginas?'disabled':''} onclick="cambiarPaginaServidores(${pagina+1})">Siguiente</button>
      </div>`;
  }
  window.cambiarPaginaServidores = function(p) {
    if (p < 1 || p > totalPaginas) return;
    paginaActual = p;
    cargarServidores();
  };

  window.limpiarFiltrosServ = function() {
    document.getElementById('filtro-q').value = '';
    document.getElementById('filtro-area').value = '';
    paginaActual = 1;
    cargarServidores();
  };

  function setLoading(estado) { /* placeholder */ }

  function renderChips() {
    if (!chipBox) return;
    chipBox.innerHTML = '';
    if (!selectedAreas.length) {
      chipBox.innerHTML = '<span class="text-gray-400 text-xs">Sin áreas seleccionadas</span>';
      return;
    }
    selectedAreas.forEach(a => {
      const chip = document.createElement('span');
      chip.className = 'inline-flex items-center gap-2 bg-purple-50 text-purple-700 px-3 py-1 rounded-full text-xs';
      chip.innerHTML = `${a.nombre} <button class="text-purple-700 font-bold" aria-label="Quitar" data-id="${a.id}">×</button>`;
      chip.querySelector('button').onclick = () => {
        selectedAreas = selectedAreas.filter(x => String(x.id) !== String(a.id));
        renderChips();
        renderSuggestions(searchInput?.value || '');
      };
      chipBox.appendChild(chip);
    });
  }

  function renderSuggestions(term='') {
    if (!suggestBox) return;
    const t = term.toLowerCase();
    const list = areas
      .filter(a => !selectedAreas.some(x => String(x.id) === String(a.id)))
      .filter(a => t && a.nombre.toLowerCase().includes(t));
    suggestBox.innerHTML = '';
    if (!list.length) {
      suggestBox.classList.add('hidden');
      return;
    }
    list.forEach(a => {
      const item = document.createElement('div');
      item.className = 'px-3 py-2 hover:bg-purple-50 cursor-pointer';
      item.textContent = a.nombre;
      item.onclick = () => {
        selectedAreas.push({id:String(a.id), nombre:a.nombre});
        renderChips();
        renderSuggestions(searchInput?.value || '');
      };
      suggestBox.appendChild(item);
    });
    suggestBox.classList.remove('hidden');
  }

  function toast(msg, error=false) {
    const t = document.getElementById('toast-servidores');
    if (!t) return;
    t.textContent = msg;
    t.className = `fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-sm ${error ? 'bg-red-600 text-white' : 'bg-gray-900 text-white'} animate-fade`;
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 2400);
  }
})();
