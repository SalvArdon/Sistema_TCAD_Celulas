(() => {
  const main = document.querySelector('main[data-base-url]');
  const BASE_URL = main?.dataset.baseUrl || '';
  let paginaActual = 1;
  let totalPaginas = 1;
  let celulas = [];

  document.addEventListener('DOMContentLoaded', () => {
    cargarCelulas();
    cargarOfrendas();
  });

  async function cargarCelulas() {
    try {
      const resp = await fetch(`${BASE_URL}api/celulas.php?accion=listar&estado=activa&limite=500`);
      const json = await resp.json();
      celulas = json.data || [];
      pintarFiltroSugerencias('');
    } catch (e) {
      console.error(e);
    }
  }

  async function cargarOfrendas() {
    setLoading(true);
    const estado = document.getElementById('filtro-estado')?.value || '';
    const celula_id = document.getElementById('filtro-celula-id')?.value || '';
    const inicio = document.getElementById('filtro-inicio')?.value || '';
    const fin = document.getElementById('filtro-fin')?.value || '';

    const params = new URLSearchParams({ accion: 'listar', pagina: paginaActual });
    if (estado) params.append('estado', estado);
    if (celula_id) params.append('celula_id', celula_id);
    if (inicio) params.append('fecha_inicio', inicio);
    if (fin) params.append('fecha_fin', fin);

    const resp = await fetch(`${BASE_URL}api/ofrendas.php?${params.toString()}`);
    const json = await resp.json();
    if (json.exito) {
      renderTabla(json.data);
      renderPaginacion(json.total, json.limite, json.pagina);
    }
    setLoading(false);
  }
  window.cargarOfrendas = cargarOfrendas;

  function renderTabla(data) {
    const tbody = document.getElementById('tabla-ofrendas');
    const cards = document.getElementById('cards-ofrendas');
    if (cards) cards.innerHTML = '';
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-500 text-sm">Sin resultados</td></tr>';
      if (cards) cards.innerHTML = '<div class="bg-white rounded-xl shadow p-4 text-center text-gray-500 text-sm">Sin resultados</div>';
      return;
    }
    data.forEach(o => {
      const tr = document.createElement('tr');
      tr.className = 'border-b hover:bg-gray-50';
      tr.innerHTML = `
        <td class="px-3 sm:px-4 py-3 text-gray-900 font-medium">${(o.fecha_reporte || '').substring(0,10)}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${o.celula_nombre ?? ''}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${o.lider_nombre ?? ''}</td>
        <td class="px-3 sm:px-4 py-3 text-right text-gray-900 font-semibold">${o.monto ? '$'+parseFloat(o.monto).toFixed(2) : '-'}</td>
        <td class="px-3 sm:px-4 py-3 text-center">${estadoBadge(o.estado)}</td>
        <td class="px-3 sm:px-4 py-3 text-center">${accionButtons(o)}</td>
      `;
      tbody.appendChild(tr);

      if (cards) {
        const card = document.createElement('div');
        card.className = 'w-full max-w-full bg-white rounded-xl shadow p-4 flex flex-col gap-3 min-w-0';
        card.innerHTML = `
          <div class="flex justify-between items-start gap-2">
            <div class="flex-1 min-w-0">
              <p class="text-xs text-gray-500 truncate">${o.celula_nombre ?? ''}</p>
              <p class="text-lg font-semibold text-gray-900 break-words">${(o.fecha_reporte || '').substring(0,10)}</p>
              <p class="text-sm text-gray-600 truncate">LÃ­der: ${o.lider_nombre ?? ''}</p>
            </div>
            ${estadoBadge(o.estado, true)}
          </div>
          <div class="text-sm text-gray-700 flex flex-wrap gap-x-4 gap-y-1 w-full">
            <span class="flex items-center gap-1">ðŸ’° ${o.monto ? '$'+parseFloat(o.monto).toFixed(2) : '-'}</span>
          </div>
          <div class="flex flex-wrap gap-2 pt-1 w-full">${accionButtons(o)}</div>
        `;
        cards.appendChild(card);
      }
    });
  }

  function accionButtons(o) {
    const base = "inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold";
    const icon = (path, color) => `<svg class="w-4 h-4 sm:w-5 sm:h-5 ${color}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="${path}"/></svg>`;
    const verBtn = `<button class="${base} text-indigo-600 bg-indigo-50 hover:bg-indigo-100" onclick="verOfrenda(${o.id})" aria-label="Ver detalle">${icon('M12 20.5c5 0 9-4.5 9-8.5s-4-8.5-9-8.5-9 4.5-9 8.5 4 8.5 9 8.5zm0-5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z', 'text-indigo-600')}</button>`;
    const next = siguienteEstado(o.estado);
    const prev = estadoAnterior(o.estado);
    const toggleBtn = next ? `<button class="${base} text-emerald-600 bg-emerald-50 hover:bg-emerald-100" onclick="cambiarEstado(${o.id}, '${next}')" aria-label="Avanzar a ${next}">${icon('M5 13l4 4L19 7', 'text-emerald-600')}</button>` : '';
    const revertBtn = prev ? `<button class="${base} text-amber-600 bg-amber-50 hover:bg-amber-100" onclick="cambiarEstado(${o.id}, '${prev}')" aria-label="Revertir a ${prev}">${icon('M6 6l12 12M6 18L18 6', 'text-amber-600')}</button>` : '';
    const deleteBtn = `<button class="${base} text-red-600 bg-red-50 hover:bg-red-100" onclick="eliminarOfrenda(${o.id})" aria-label="Eliminar">${icon('M6 7h12M10 11v6M14 11v6M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M5 7h14l-1 12a2 2 0 0 1-2 1.8H8a2 2 0 0 1-2-1.8L5 7z', 'text-red-600')}</button>`;
    return `<div class="flex flex-wrap gap-2 justify-center">${verBtn}${toggleBtn}${revertBtn}${deleteBtn}</div>`;
  }

  function siguienteEstado(actual) {
    if (actual === 'reportada') return 'recibida';
    if (actual === 'recibida') return 'conciliada';
    return null;
  }
  function estadoAnterior(actual) {
    if (actual === 'conciliada') return 'recibida';
    if (actual === 'recibida') return 'reportada';
    return null;
  }

  window.verOfrenda = async function(id) {
    try {
      const resp = await fetch(`${BASE_URL}api/ofrendas.php?accion=obtener&id=${id}`);
      if (!resp.ok) throw new Error('No se pudo obtener el detalle');
      const json = await resp.json();
      if (!json.exito) throw new Error(json.mensaje || 'Error al obtener detalle');
      const d = json.data;
      document.getElementById('det-ofr-celula').textContent = d.celula_nombre || '—';
      document.getElementById('det-ofr-lider').textContent = d.lider_nombre || '—';
      document.getElementById('det-ofr-fecha').textContent = (d.fecha_reporte || d.fecha_reunion || '—').substring(0,10);
      document.getElementById('det-ofr-monto').textContent = d.monto ? '$'+parseFloat(d.monto).toFixed(2) : '—';
      document.getElementById('det-ofr-reunion').textContent = d.reunion_id ? `#${d.reunion_id}` : '—';
      const notasVal = d.notas || d.descrepancia || '';
      const notasEl = document.getElementById('det-ofr-notas');
      const notasWrap = document.getElementById('det-ofr-notas-wrap');
      if (notasEl) notasEl.textContent = notasVal || '—';
      if (notasWrap) notasWrap.classList.toggle('hidden', !notasVal);
      const badge = document.getElementById('det-ofr-estado');
      if (badge) {
        const map = {
          reportada: ['bg-amber-100 text-amber-700','reportada'],
          recibida: ['bg-blue-100 text-blue-700','recibida'],
          conciliada: ['bg-green-100 text-green-700','conciliada']
        };
        const [cls,label] = map[d.estado] || ['bg-gray-200 text-gray-700', d.estado || 'â€”'];
        badge.className = `px-3 py-1.5 rounded-full text-xs font-semibold ${cls}`;
        badge.textContent = label;
      }
      document.getElementById('modal-detalle-ofrenda')?.classList.remove('hidden');
    } catch (e) {
      toast(e.message || 'Error al cargar detalle', true);
    }
  };

  window.cambiarEstado = async function(id, nuevo) {
    try {
      const resp = await fetch(`${BASE_URL}api/ofrendas.php?accion=cambiar-estado`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ofrenda_id: id, nuevo_estado: nuevo })
      });
      const text = await resp.text();
      let json;
      try { json = JSON.parse(text); } catch (e) { throw new Error(text || 'Respuesta no vÃ¡lida'); }
      if (!json.exito) throw new Error(json.mensaje || 'Error al cambiar estado');
      toast('Estado actualizado');
      cargarOfrendas();
    } catch (e) {
      toast(e.message || 'No se pudo cambiar el estado', true);
    }
  };

  // Cerrar modal detalle
  window.cerrarDetalleOfrenda = function() {
    document.getElementById('modal-detalle-ofrenda')?.classList.add('hidden');
  };

  window.eliminarOfrenda = async function(id) {
    if (!confirm('Â¿Eliminar esta ofrenda?')) return;
    try {
      const resp = await fetch(`${BASE_URL}api/ofrendas.php?accion=eliminar&id=${id}`, {
        method: 'POST'
      });
      const json = await resp.json();
      if (!json.exito) throw new Error(json.mensaje || 'No se pudo eliminar');
      toast('Ofrenda eliminada');
      cargarOfrendas();
    } catch (e) {
      toast(e.message || 'Error al eliminar', true);
    }
  };

  function renderPaginacion(total, limite, pagina) {
    const cont = document.getElementById('paginacion-ofrendas');
    if (!cont) return;
    totalPaginas = Math.max(1, Math.ceil(total / limite));
    paginaActual = pagina;
    cont.innerHTML = `
      <span class="text-gray-600">Pagina ${pagina} de ${totalPaginas} &middot; ${total} registros</span>
      <div class="space-x-2">
          <button class="px-3 py-1 rounded-lg ${pagina<=1?'bg-gray-200 text-gray-400':'bg-white border'}" ${pagina<=1?'disabled':''} onclick="cambiarPaginaOfrendas(${pagina-1})">Anterior</button>
          <button class="px-3 py-1 rounded-lg ${pagina>=totalPaginas?'bg-gray-200 text-gray-400':'bg-white border'}" ${pagina>=totalPaginas?'disabled':''} onclick="cambiarPaginaOfrendas(${pagina+1})">Siguiente</button>
      </div>`;
  }
  window.cambiarPaginaOfrendas = function(p) {
    if (p < 1 || p > totalPaginas) return;
    paginaActual = p;
    cargarOfrendas();
  };

  window.limpiarFiltrosOfrendas = function() {
    document.getElementById('filtro-estado').value = '';
    document.getElementById('filtro-celula-search').value = '';
    document.getElementById('filtro-celula-id').value = '';
    document.getElementById('filtro-celula-suggestions')?.classList.add('hidden');
    document.getElementById('filtro-inicio').value = '';
    document.getElementById('filtro-fin').value = '';
    paginaActual = 1;
    cargarOfrendas();
  };

  function estadoBadge(estado, mini = false) {
    const map = {
      reportada: ['bg-amber-100 text-amber-700', 'reportada'],
      recibida: ['bg-blue-100 text-blue-700', 'recibida'],
      conciliada: ['bg-green-100 text-green-700', 'conciliada']
    };
    const [cls, label] = map[estado] || ['bg-gray-200 text-gray-700', estado || 'â€”'];
    const pad = mini ? 'px-2 py-1' : 'px-3 py-1.5';
    return `<span class="${pad} rounded-full text-xs font-semibold ${cls}">${label}</span>`;
  }

  function setLoading(estado) {
    // placeholder
  }

  function pintarFiltroSugerencias(term) {
    const box = document.getElementById('filtro-celula-suggestions');
    const input = document.getElementById('filtro-celula-search');
    const hidden = document.getElementById('filtro-celula-id');
    if (!box || !input || !hidden) return;
    if (!term || term.length < 2) {
      box.classList.add('hidden');
      hidden.value = '';
      return;
    }
    const lista = celulas.filter(c => (c.nombre || '').toLowerCase().includes(term));
    if (lista.length === 0) {
      box.classList.add('hidden');
      hidden.value = '';
      return;
    }
    box.innerHTML = '';
      lista.forEach(c => {
        const item = document.createElement('div');
        item.className = 'px-3 py-2 hover:bg-purple-50 cursor-pointer';
        item.textContent = c.nombre;
        item.onclick = () => {
          input.value = c.nombre;
          hidden.value = c.id;
          box.classList.add('hidden');
        // prefill reuniÃ³n reciente
        prefillReunionReciente(c.id);
        };
        box.appendChild(item);
      });
      box.classList.remove('hidden');
  }

})();
