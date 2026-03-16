(() => {
  const main = document.querySelector('main[data-base-url]');
  const BASE_URL = main?.dataset.baseUrl || '';
  let paginaActual = 1;
  let totalPaginas = 1;
  let celulas = [];

  document.addEventListener('DOMContentLoaded', () => {
    cargarCelulas();
    cargarReuniones();
    const realizada = document.getElementById('realizada');
    if (realizada) {
      realizada.addEventListener('change', () => {
        const motivo = document.getElementById('div-motivo');
        if (motivo) motivo.classList.toggle('hidden', realizada.checked);
      });
    }
  });

  async function cargarCelulas() {
    try {
      const resp = await fetch(`${BASE_URL}api/celulas.php?accion=listar&estado=activa&limite=500`);
      const json = await resp.json();
      celulas = json.data || [];
      pintarSugerencias('');
      pintarFiltroSugerencias('');
    } catch (e) {
      console.error(e);
    }
  }

  async function cargarReuniones() {
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

    const resp = await fetch(`${BASE_URL}api/reuniones.php?${params.toString()}`);
    const json = await resp.json();
    if (json.exito) {
      renderTabla(json.data);
      renderPaginacion(json.total, json.limite, json.pagina);
    }
    setLoading(false);
  }
  window.cargarReuniones = cargarReuniones;

  function renderTabla(data) {
    const tbody = document.getElementById('tabla-reuniones');
    const cards = document.getElementById('cards-reuniones');
    if (cards) cards.innerHTML = '';
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!data || data.length === 0) {
      tbody.innerHTML = '<tr><td colspan=\"7\" class=\"px-4 py-6 text-center text-gray-500 text-sm\">Sin resultados</td></tr>';
      if (cards) cards.innerHTML = '<div class=\"bg-white rounded-xl shadow p-4 text-center text-gray-500 text-sm\">Sin resultados</div>';
      return;
    }
    data.forEach(r => {
      const tr = document.createElement('tr');
      tr.className = 'border-b hover:bg-gray-50';
      tr.innerHTML = `
        <td class=\"px-3 sm:px-4 py-3 text-gray-900 font-medium\">${r.fecha_reunion ?? ''}</td>
        <td class=\"px-3 sm:px-4 py-3 text-gray-700\">${r.celula_nombre ?? ''}</td>
        <td class=\"px-3 sm:px-4 py-3 text-gray-700\">${r.lider_nombre ?? ''}</td>
        <td class=\"px-3 sm:px-4 py-3 text-center\">${estadoBadge(r.realizada)}</td>
        <td class=\"px-3 sm:px-4 py-3 text-right text-gray-700\">${r.cantidad_asistentes ?? 0}</td>
        <td class=\"px-3 sm:px-4 py-3 text-right text-gray-700\">${r.ofrenda_monto ? '$'+parseFloat(r.ofrenda_monto).toFixed(2) : '-'}</td>
        <td class=\"px-3 sm:px-4 py-3 text-center\">${accionButtons(r)}</td>
      `;
      tbody.appendChild(tr);

      if (cards) {
        const card = document.createElement('div');
        card.className = 'w-full max-w-full bg-white rounded-xl shadow p-4 flex flex-col gap-3 min-w-0';
        card.innerHTML = `
          <div class=\"flex justify-between items-start gap-2\">
            <div class=\"flex-1 min-w-0\">
              <p class=\"text-xs text-gray-500 truncate\">${r.celula_nombre ?? ''}</p>
              <p class=\"text-lg font-semibold text-gray-900 break-words\">${r.fecha_reunion ?? ''}</p>
              <p class=\"text-sm text-gray-600 truncate\">Líder: ${r.lider_nombre ?? ''}</p>
            </div>
            ${estadoBadge(r.realizada, true)}
          </div>
          <div class=\"text-sm text-gray-700 flex flex-wrap gap-x-4 gap-y-1 w-full\">
            <span class=\"flex items-center gap-1\">👥 ${r.cantidad_asistentes ?? 0}</span>
            <span class=\"flex items-center gap-1\">💸 ${r.ofrenda_monto ? '$'+parseFloat(r.ofrenda_monto).toFixed(2) : '-'}</span>
          </div>
          <div class=\"flex flex-wrap gap-2 pt-1 w-full\">${accionButtons(r)}</div>
        `;
        cards.appendChild(card);
      }
    });
  }

  function accionButtons(r) {
    const base = "inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs sm:text-sm font-semibold";
    const icon = (path, color) => `<svg class=\"w-4 h-4 sm:w-5 sm:h-5 ${color}\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" viewBox=\"0 0 24 24\" aria-hidden=\"true\"><path d=\"${path}\"/></svg>`;
    const verBtn = `<button class=\"${base} text-indigo-600 bg-indigo-50 hover:bg-indigo-100\" onclick=\"verReunion(${r.id})\" aria-label=\"Ver detalle\">${icon('M12 20.5c5 0 9-4.5 9-8.5s-4-8.5-9-8.5-9 4.5-9 8.5 4 8.5 9 8.5zm0-5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z', 'text-indigo-600')}</button>`;
    const toggleBtn = `<button class=\"${base} text-emerald-600 bg-emerald-50 hover:bg-emerald-100\" onclick=\"toggleRealizada(${r.id}, ${r.realizada ? 'false' : 'true'})\" aria-label=\"Marcar realizada/no\">${icon('M5 13l4 4L19 7', 'text-emerald-600')}</button>`;
    return `<div class=\"flex flex-wrap gap-2 justify-center\">${verBtn}${toggleBtn}</div>`;
  }
  window.verReunion = async function(id) {
    const resp = await fetch(`${BASE_URL}api/reuniones.php?accion=obtener&id=${id}`);
    const json = await resp.json();
    if (json.exito) {
      const d = json.data;
      document.getElementById('det-celula').textContent = d.celula_nombre || '—';
      document.getElementById('det-fecha').textContent = d.fecha_reunion || '—';
      document.getElementById('det-lider').textContent = d.lider_nombre || '—';
      document.getElementById('det-asistentes').textContent = d.cantidad_asistentes ?? '—';
      document.getElementById('det-nuevos').textContent = d.cantidad_nuevos ?? '—';
      document.getElementById('det-ofrenda').textContent = d.ofrenda_monto ? '$'+parseFloat(d.ofrenda_monto).toFixed(2) : '—';
      document.getElementById('det-motivo').textContent = d.motivo_cancelacion || '—';
      document.getElementById('det-comentarios').textContent = d.comentarios || '—';
      document.getElementById('det-estado').className = (d.realizada ? 'px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700' : 'px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700');
      document.getElementById('det-estado').textContent = d.realizada ? 'realizada' : 'pendiente';
      document.getElementById('modal-detalle-reunion')?.classList.remove('hidden');
    }
  };

  window.cerrarDetalleReunion = function() {
    document.getElementById('modal-detalle-reunion')?.classList.add('hidden');
  };

  window.toggleRealizada = async function(id, nueva) {
    await fetch(`${BASE_URL}api/reuniones.php?accion=toggle&id=${id}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ realizada: nueva })
    });
    cargarReuniones();
  };

  function renderPaginacion(total, limite, pagina) {
    const cont = document.getElementById('paginacion-reuniones');
    if (!cont) return;
    totalPaginas = Math.max(1, Math.ceil(total / limite));
    paginaActual = pagina;
    cont.innerHTML = `
      <span class=\"text-gray-600\">Página ${pagina} de ${totalPaginas} · ${total} registros</span>
      <div class=\"space-x-2\">
          <button class=\"px-3 py-1 rounded-lg ${pagina<=1?'bg-gray-200 text-gray-400':'bg-white border'}\" ${pagina<=1?'disabled':''} onclick=\"cambiarPagina(${pagina-1})\">Anterior</button>
          <button class=\"px-3 py-1 rounded-lg ${pagina>=totalPaginas?'bg-gray-200 text-gray-400':'bg-white border'}\" ${pagina>=totalPaginas?'disabled':''} onclick=\"cambiarPagina(${pagina+1})\">Siguiente</button>
      </div>`;
  }
  window.cambiarPagina = function(p) {
    if (p < 1 || p > totalPaginas) return;
    paginaActual = p;
    cargarReuniones();
  };

  window.limpiarFiltros = function() {
    document.getElementById('filtro-estado').value = '';
    document.getElementById('filtro-celula-search').value = '';
    document.getElementById('filtro-celula-id').value = '';
    document.getElementById('filtro-celula-suggestions')?.classList.add('hidden');
    document.getElementById('filtro-inicio').value = '';
    document.getElementById('filtro-fin').value = '';
    paginaActual = 1;
    cargarReuniones();
  };

  function estadoBadge(realizada, mini = false) {
    const cls = realizada ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
    const label = realizada ? 'realizada' : 'pendiente';
    const padding = mini ? 'px-2 py-1' : 'px-3 py-1.5';
    return `<span class=\"${padding} rounded-full text-xs font-semibold ${cls}\">${label}</span>`;
  }

  function setLoading(estado) {
    // simple no-op placeholder; could add spinner if desired
  }

  function pintarSugerencias(term) {
    const box = document.getElementById('celula_suggestions');
    const input = document.getElementById('celula_search');
    const hidden = document.getElementById('celula_id');
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
      };
      box.appendChild(item);
    });
    box.classList.remove('hidden');
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
      };
      box.appendChild(item);
    });
    box.classList.remove('hidden');
  }

  const celulaSearch = document.getElementById('celula_search');
  if (celulaSearch) {
    celulaSearch.addEventListener('input', (e) => {
      const term = e.target.value || '';
      pintarSugerencias(term.toLowerCase());
    });
    celulaSearch.addEventListener('blur', () => {
      setTimeout(() => document.getElementById('celula_suggestions')?.classList.add('hidden'), 150);
    });
  }

  const filtroCelulaSearch = document.getElementById('filtro-celula-search');
  if (filtroCelulaSearch) {
    filtroCelulaSearch.addEventListener('input', (e) => {
      const term = e.target.value || '';
      pintarFiltroSugerencias(term.toLowerCase());
    });
    filtroCelulaSearch.addEventListener('blur', () => {
      setTimeout(() => document.getElementById('filtro-celula-suggestions')?.classList.add('hidden'), 150);
    });
  }

  // Modal handlers
  window.abrirModalReunion = function() {
    document.getElementById('form-reunion')?.reset();
    document.getElementById('realizada').checked = true;
    document.getElementById('div-motivo').classList.add('hidden');
    document.getElementById('celula_id').value = '';
    document.getElementById('celula_search').value = '';
    pintarSugerencias('');
    document.getElementById('modal-reunion')?.classList.remove('hidden');
  };

  window.cerrarModalReunion = function() {
    document.getElementById('modal-reunion')?.classList.add('hidden');
  };

  window.guardarReunion = async function(e) {
    e.preventDefault();
    const celulaId = document.getElementById('celula_id').value;
    const payload = {
      celula_id: celulaId,
      fecha_reunion: document.getElementById('fecha_reunion').value,
      realizada: document.getElementById('realizada').checked,
      motivo_cancelacion: document.getElementById('motivo_cancelacion').value,
      cantidad_asistentes: document.getElementById('cantidad_asistentes').value || 0,
      cantidad_nuevos: document.getElementById('cantidad_nuevos').value || 0,
      monto_ofrenda: document.getElementById('monto_ofrenda').value || 0,
      temas_tratados: document.getElementById('temas_tratados').value,
      comentarios: document.getElementById('comentarios').value
    };
    if (!payload.celula_id || !payload.fecha_reunion) {
      toast('Completa los campos obligatorios', true);
      return;
    }
    await fetch(`${BASE_URL}api/reuniones.php?accion=registrar`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    cerrarModalReunion();
    cargarReuniones();
    toast('Reunión reportada');
  };

  function toast(msg, error=false) {
    const t = document.getElementById('toast-reuniones');
    if (!t) return;
    t.textContent = msg;
    t.className = `fixed bottom-6 right-6 px-4 py-3 rounded-lg shadow-lg text-sm ${error ? 'bg-red-600 text-white' : 'bg-gray-900 text-white'} animate-fade`;
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 2400);
  }
})();
