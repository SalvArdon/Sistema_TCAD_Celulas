(() => {
  const main = document.querySelector('main[data-base-url]');
  const BASE_URL = main?.dataset.baseUrl || '';
  let chart;

  document.addEventListener('DOMContentLoaded', () => {
    cargarDashboard();
  });

  async function cargarDashboard() {
    try {
      const resp = await fetch(`${BASE_URL}api/ofrendas.php?accion=dashboard-tesorero`);
      const json = await resp.json();
      if (!json.exito) throw new Error(json.error || json.mensaje || 'Error dashboard');
      const d = json.data;
      renderCards(d.totales, d.resumen_estados);
      renderAreas(d.ofrendas_por_area_mes);
      renderChart(d.totales_por_mes);
    } catch (e) {
      console.error(e);
      document.getElementById('dash-error')?.classList.remove('hidden');
    }
  }

  function renderCards(totales, resumenEstados) {
    const cardMes = document.getElementById('card-mes-actual');
    const cardVar = document.getElementById('card-variacion');
    const cardPend = document.getElementById('card-pendientes');
    if (cardMes) cardMes.textContent = `$${(totales?.mes_actual || 0).toFixed(2)}`;
    if (cardVar) {
      const v = totales?.variacion_pct;
      cardVar.textContent = (v === null ? '—' : `${v.toFixed(1)}%`);
      cardVar.className = v === null ? 'text-gray-600' : (v >= 0 ? 'text-emerald-700' : 'text-red-700');
    }
    if (cardPend && resumenEstados) {
      const pend = (resumenEstados.find(r => r.estado === 'reportada')?.cantidad) || 0;
      cardPend.textContent = pend;
    }
  }

  function renderAreas(data) {
    const cont = document.getElementById('tabla-areas');
    if (!cont) return;
    if (!data || !data.length) {
      cont.innerHTML = '<tr><td colspan="3" class="px-4 py-2 text-sm text-gray-500">Sin datos</td></tr>';
      return;
    }
    cont.innerHTML = '';
    data.forEach(r => {
      const tr = document.createElement('tr');
      const total = parseFloat(r.total_ofrendas || 0);
      tr.innerHTML = `<td class="px-3 py-2">${r.area ?? '-'}</td>
                      <td class="px-3 py-2 text-right">$${total.toFixed(2)}</td>
                      <td class="px-3 py-2 text-right">${r.cantidad_reportes || 0}</td>`;
      cont.appendChild(tr);
    });
  }

  function renderChart(data) {
    const ctx = document.getElementById('chart-ofrendas');
    if (!ctx) return;
    const labels = (data || []).map(d => d.mes);
    const valores = (data || []).map(d => parseFloat(d.total || 0));
    if (chart) chart.destroy();
    chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Ofrendas por mes',
          data: valores,
          backgroundColor: 'rgba(99, 102, 241, 0.6)',
          borderColor: 'rgba(79, 70, 229, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { callback: v => '$'+v } }
        }
      }
    });
  }
})();
