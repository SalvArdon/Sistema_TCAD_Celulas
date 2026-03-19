(() => {
  const main = document.querySelector('main[data-base-url]');
  const BASE_URL = main?.dataset.baseUrl || '';
  let paginaActual = 1;
  let totalPaginas = 1;

  document.addEventListener('DOMContentLoaded', () => {
    cargarAreas();
    const q = document.getElementById('filtro-q');
    if (q) q.addEventListener('keyup', e => { if (e.key === 'Enter') cargarAreas(); });
    setupLiderSearch();
  });

  function setupLiderSearch() {
    const input = document.getElementById('area-lider-search');
    const box = document.getElementById('area-lider-suggestions');
    if (!input || !box) return;
    input.addEventListener('input', async () => {
      const term = input.value.trim();
      if (term.length < 2) { box.classList.add('hidden'); return; }
      try {
        const resp = await fetch(`${BASE_URL}api/usuarios.php?accion=listar&q=${encodeURIComponent(term)}&limite=10`);
        const json = await resp.json();
        const data = json.data || [];
        box.innerHTML = '';
        if (!data.length) { box.classList.add('hidden'); return; }
        data.forEach(u => {
          const item = document.createElement('div');
          item.className = 'px-3 py-2 hover:bg-purple-50 cursor-pointer';
          item.textContent = `${u.nombre_completo || ''} (${u.correo || ''})`;
          item.onclick = () => {
            document.getElementById('area-lider-id').value = u.id;
            input.value = u.nombre_completo || '';
            box.classList.add('hidden');
          };
          box.appendChild(item);
        });
        box.classList.remove('hidden');
      } catch (e) { box.classList.add('hidden'); }
    });
    input.addEventListener('blur', () => setTimeout(()=> box.classList.add('hidden'),150));
  }

  async function cargarAreas() {
    setLoading(true);
    const q = document.getElementById('filtro-q')?.value || '';
    const estado = document.getElementById('filtro-estado')?.value || '';
    const params = new URLSearchParams({ accion: 'listar', pagina: paginaActual });
    if (q) params.append('q', q);
    if (estado) params.append('estado', estado);
    try {
      const resp = await fetch(`${BASE_URL}api/areas.php?${params.toString()}`);
      const text = await resp.text();
      let json;
      try { json = JSON.parse(text); } catch (e) { throw new Error(text || 'Respuesta no válida'); }
      if (!resp.ok || !json.exito) throw new Error(json.error || json.mensaje || 'Error al listar');
      renderTabla(json.data);
      renderPaginacion(json.total, json.limite, json.pagina);
    } catch (e) {
      renderTabla([]);
      toast(e.message || 'Error cargando áreas', true);
    } finally { setLoading(false); }
  }
  window.cargarAreas = cargarAreas;

  function renderTabla(data) {
    const tbody = document.getElementById('tabla-areas');
    const cards = document.getElementById('cards-areas');
    if (cards) cards.innerHTML = '';
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-500 text-sm">Sin resultados</td></tr>';
      if (cards) cards.innerHTML = '<div class="bg-white rounded-xl shadow p-4 text-center text-gray-500 text-sm">Sin resultados</div>';
      return;
    }
    data.forEach(a => {
      const estadoBadge = a.activa ? '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">activa</span>'
                                   : '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">inactiva</span>';
      const tr = document.createElement('tr');
      tr.className = 'border-b hover:bg-gray-50';
      tr.innerHTML = `
        <td class="px-3 sm:px-4 py-3 text-gray-900 font-medium">${a.nombre || ''}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${a.lider_nombre || '—'}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${estadoBadge}</td>
        <td class="px-3 sm:px-4 py-3 text-gray-700">${a.fecha_creacion || '—'}</td>
        <td class="px-3 sm:px-4 py-3 text-center">${accionButtons(a)}</td>`;
      tbody.appendChild(tr);

      if (cards) {
        const card = document.createElement('div');
        card.className = 'w-full bg-white rounded-xl shadow p-4 flex flex-col gap-3';
        card.innerHTML = `
          <div class="flex justify-between items-start gap-2">
            <div class="flex-1 min-w-0">
              <p class="text-xs text-gray-500 truncate">Líder: ${a.lider_nombre || '—'}</p>
              <p class="text-lg font-semibold text-gray-900 break-words">${a.nombre || ''}</p>
              <p class="text-sm text-gray-600 truncate">${a.descripcion || ''}</p>
            </div>
            ${estadoBadge}
          </div>
          <div class="flex flex-wrap gap-2 pt-1 w-full">${accionButtons(a)}</div>
        `;
        cards.appendChild(card);
      }
    });
  }

  function accionButtons(a) {
    const base = "inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold";
    const icon = (path, color) => `<svg class="w-4 h-4 sm:w-5 sm:h-5 ${color}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="${path}"/></svg>`;
    const verBtn = `<button class="${base} text-indigo-600 bg-indigo-50 hover:bg-indigo-100" onclick="verArea(${a.id})" aria-label="Ver detalle">${icon('M12 20.5c5 0 9-4.5 9-8.5s-4-8.5-9-8.5-9 4.5-9 8.5 4 8.5 9 8.5zm0-5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z', 'text-indigo-600')}</button>`;
    const editBtn = `<button class="${base} text-purple-600 bg-purple-50 hover:bg-purple-100" onclick="editarArea(${a.id})" aria-label="Editar">${icon('M4 20h4.5L19 9.5l-4.5-4.5L4 15.5V20zm11-11l-1.5-1.5', 'text-purple-600')}</button>`;
    const delBtn = `<button class="${base} text-red-600 bg-red-50 hover:bg-red-100" onclick="eliminarArea(${a.id})" aria-label="Desactivar">${icon('M6 7h12M10 11v6M14 11v6M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M5 7h14l-1 12a2 2 0 0 1-2 1.8H8a2 2 0 0 1-2-1.8L5 7z', 'text-red-600')}</button>`;
    return `<div class="flex flex-wrap gap-2 justify-center">${verBtn}${editBtn}${delBtn}</div>`;
  }

  window.verArea = async function(id) {
    const resp = await fetch(`${BASE_URL}api/areas.php?accion=obtener&id=${id}`);
    const json = await resp.json();
    if (!json.exito) return;
    const a = json.data;
    document.getElementById('det-area-nombre').textContent = a.nombre || '—';
    document.getElementById('det-area-lider').textContent = a.lider_nombre || '—';
    document.getElementById('det-area-estado').textContent = a.activa ? 'Activa' : 'Inactiva';
    document.getElementById('det-area-desc').textContent = a.descripcion || '—';
    document.getElementById('det-area-creacion').textContent = a.fecha_creacion || '—';
    document.getElementById('det-area-mod').textContent = a.fecha_modificacion || '—';
    document.getElementById('modal-det-area')?.classList.remove('hidden');
  };

  window.cerrarDetalleArea = function() {
    document.getElementById('modal-det-area')?.classList.add('hidden');
  };

  window.editarArea = async function(id) {
    const resp = await fetch(`${BASE_URL}api/areas.php?accion=obtener&id=${id}`);
    const json = await resp.json();
    if (!json.exito) return;
    const a = json.data;
    document.getElementById('area-id').value = a.id;
    document.getElementById('area-nombre').value = a.nombre || '';
    document.getElementById('area-desc').value = a.descripcion || '';
    document.getElementById('area-lider-id').value = a.lider_id || '';
    document.getElementById('area-lider-search').value = a.lider_nombre || '';
    document.getElementById('area-estado').value = a.activa ? '1' : '0';
    document.getElementById('modal-area-titulo').textContent = 'Editar área';
    document.getElementById('modal-area')?.classList.remove('hidden');
  };

  window.eliminarArea = async function(id) {
    if (!confirm('¿Desactivar esta área?')) return;
    await fetch(`${BASE_URL}api/areas.php?accion=eliminar&id=${id}`, { method: 'POST' });
    toast('Área desactivada');
    cargarAreas();
  };

  window.abrirModalArea = function() {
    document.getElementById('form-area').reset();
    document.getElementById('area-id').value = '';
    document.getElementById('area-estado').value = '1';
    document.getElementById('area-lider-id').value = '';
    document.getElementById('area-lider-search').value = '';
    document.getElementById('modal-area-titulo').textContent = 'Nueva área';
    document.getElementById('modal-area')?.classList.remove('hidden');
  };

  window.cerrarModalArea = function() {
    document.getElementById('modal-area')?.classList.add('hidden');
  };

  window.guardarArea = async function(e) {
    e.preventDefault();
    const payload = {
      nombre: document.getElementById('area-nombre').value,
      descripcion: document.getElementById('area-desc').value,
      lider_id: document.getElementById('area-lider-id').value,
      activa: document.getElementById('area-estado').value
    };
    const id = document.getElementById('area-id').value;
    const accion = id ? 'actualizar&id='+id : 'crear';
    const resp = await fetch(`${BASE_URL}api/areas.php?accion=${accion}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await resp.json();
    if (json.exito) {
      toast('Guardado');
      cerrarModalArea();
      cargarAreas();
    } else {
      toast(json.mensaje || 'Error al guardar', true);
    }
  };

  function renderPaginacion(total, limite, pagina) {
    const cont = document.getElementById('paginacion-areas');
    if (!cont) return;
    totalPaginas = Math.max(1, Math.ceil(total / limite));
    paginaActual = pagina;
    cont.innerHTML = `
      <span class="text-gray-600">Página ${pagina} de ${totalPaginas} · ${total} registros</span>
      <div class="space-x-2">
          <button class="px-3 py-1 rounded-lg ${pagina<=1?'bg-gray-200 text-gray-400':'bg-white border'}" ${pagina<=1?'disabled':''} onclick="cambiarPaginaAreas(${pagina-1})">Anterior</button>
          <button class="px-3 py-1 rounded-lg ${pagina>=totalPaginas?'bg-gray-200 text-gray-400':'bg-white border'}" ${pagina>=totalPaginas?'disabled':''} onclick="cambiarPaginaAreas(${pagina+1})">Siguiente</button>
      </div>`;
  }
  window.cambiarPaginaAreas = function(p) {
    if (p < 1 || p > totalPaginas) return;
    paginaActual = p;
    cargarAreas();
  };

  window.limpiarFiltrosAreas = function() {
    document.getElementById('filtro-q').value = '';
    document.getElementById('filtro-estado').value = '';
    paginaActual = 1;
    cargarAreas();
  };

  function setLoading(estado) { /* placeholder */ }

  function toast(msg, error=false) {
    const t = document.getElementById('toast-areas');
    if (!t) return;
    t.textContent = msg;
    t.className = `fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-sm ${error ? 'bg-red-600 text-white' : 'bg-gray-900 text-white'} animate-fade`;
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 2400);
  }
})();
