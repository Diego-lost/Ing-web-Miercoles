function resolveApiUrl(path) {
  return new URL(path, window.location.href).href;
}

const API = window.API_ENDPOINTS
  ? {
      catalogos: resolveApiUrl(window.API_ENDPOINTS.catalogos),
      carpetas: resolveApiUrl(window.API_ENDPOINTS.carpetas),
      prestamos: resolveApiUrl(window.API_ENDPOINTS.prestamos),
      reportes: resolveApiUrl(window.API_ENDPOINTS.reportes),
    }
  : {
      catalogos: resolveApiUrl('../services/catalogos/index.php'),
      carpetas: resolveApiUrl('../services/carpetas/index.php'),
      prestamos: resolveApiUrl('../services/prestamos/index.php'),
      reportes: resolveApiUrl('../services/reportes/index.php'),
    };

let fiscaliasCache = [];
let despachosCache = [];

async function apiGet(url, params = {}) {
  const qs = new URLSearchParams(params).toString();
  const res = await fetch(`${url}${qs ? '?' + qs : ''}`);
  const text = await res.text();
  try {
    return JSON.parse(text);
  } catch {
    throw new Error('Respuesta inválida del servidor');
  }
}

async function apiPost(url, params, body = {}) {
  const qs = new URLSearchParams(params).toString();
  const res = await fetch(`${url}?${qs}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  });
  return res.json();
}

function toast(msg, ok = true) {
  const el = document.createElement('div');
  el.className = `toast ${ok ? 'ok' : 'err'}`;
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

function showPanel(id) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('nav.tabs button').forEach(b => b.classList.remove('active'));
  document.getElementById(id)?.classList.add('active');
  document.querySelector(`[data-panel="${id}"]`)?.classList.add('active');
}

document.querySelectorAll('nav.tabs button').forEach(btn => {
  btn.addEventListener('click', () => {
    showPanel(btn.dataset.panel);
    if (btn.dataset.panel === 'consulta') cargarConsulta();
    if (btn.dataset.panel === 'reporte-prestadas') cargarReportePrestadas();
    if (btn.dataset.panel === 'reporte-devueltas') cargarReporteDevueltas();
  });
});

async function cargarCatalogos() {
  try {
    const fRes = await apiGet(API.catalogos, { action: 'fiscalias' });
    const dRes = await apiGet(API.catalogos, { action: 'despachos' });

    if (!fRes.success) {
      toast(fRes.message || 'No se pudieron cargar las fiscalías', false);
      return;
    }

    fiscaliasCache = fRes.data || [];
    despachosCache = dRes.data || [];

    if (fiscaliasCache.length === 0) {
      toast('No hay fiscalías registradas. Vaya a Catálogos para agregar una.', false);
    }

    document.querySelectorAll('select.fiscalia-select').forEach(sel => {
      const val = sel.value;
      sel.innerHTML = '<option value="">-- Fiscalía --</option>';
      fiscaliasCache.forEach(f => {
        sel.innerHTML += `<option value="${f.id}">${f.codigo} - ${f.nombre}</option>`;
      });
      if (!val && fiscaliasCache.length === 1) {
        sel.value = String(fiscaliasCache[0].id);
      } else {
        sel.value = val;
      }
    });

    const catDesp = document.getElementById('cat-despacho-fiscalia');
    if (catDesp) {
      catDesp.innerHTML = '<option value="">-- Fiscalía --</option>';
      fiscaliasCache.forEach(f => {
        catDesp.innerHTML += `<option value="${f.id}">${f.codigo}</option>`;
      });
    }

    const ingresoFiscalia = document.getElementById('ingreso-fiscalia');
    if (ingresoFiscalia?.value) {
      llenarDespachos(ingresoFiscalia.value, document.getElementById('ingreso-despacho'));
    }
  } catch (err) {
    toast('Error al cargar catálogos: ' + err.message, false);
    console.error(err);
  }
}

function llenarDespachos(fiscaliaId, selectEl) {
  if (!selectEl) return;
  selectEl.innerHTML = '<option value="">-- Despacho --</option>';
  despachosCache
    .filter(d => !fiscaliaId || String(d.fiscalia_id) === String(fiscaliaId))
    .forEach(d => {
      selectEl.innerHTML += `<option value="${d.id}">${d.codigo} - ${d.nombre}</option>`;
    });
}

document.getElementById('ingreso-fiscalia')?.addEventListener('change', e => {
  llenarDespachos(e.target.value, document.getElementById('ingreso-despacho'));
});

document.getElementById('filtro-fiscalia')?.addEventListener('change', e => {
  llenarDespachos(e.target.value, document.getElementById('filtro-despacho'));
});

document.getElementById('form-ingreso')?.addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const body = Object.fromEntries(fd.entries());
  body.fiscalia_id = parseInt(body.fiscalia_id, 10);
  body.despacho_id = parseInt(body.despacho_id, 10);
  body.folios = parseInt(body.folios, 10);

  const res = await apiPost(API.carpetas, { action: 'crear' }, body);
  toast(res.message || (res.success ? 'Registrado' : 'Error'), res.success);
  if (res.success) e.target.reset();
});

async function cargarConsulta() {
  const params = {
    action: 'consultar',
    numero_carpeta: document.getElementById('filtro-numero')?.value || '',
    imputado: document.getElementById('filtro-imputado')?.value || '',
    fiscalia_id: document.getElementById('filtro-fiscalia')?.value || '',
    despacho_id: document.getElementById('filtro-despacho')?.value || '',
    estado: document.getElementById('filtro-estado')?.value || '',
    delito: document.getElementById('filtro-delito')?.value || '',
  };

  const res = await apiGet(API.carpetas, params);
  const tbody = document.querySelector('#tabla-consulta tbody');
  if (!res.success || !res.data?.length) {
    tbody.innerHTML = '<tr><td colspan="11" class="empty">Sin resultados</td></tr>';
    return;
  }

  tbody.innerHTML = res.data.map(c => {
    const badgeClass =
      c.estado === 'PRESTADA' ? 'badge-prestada' :
      c.estado === 'DESARCHIVADA' ? 'badge-desarchivada' : 'badge-archivo';
    let alerta = '';
    if (c.alerta_prestamo) {
      alerta = `<span class="badge alerta-${c.alerta_prestamo.color}">${c.alerta_prestamo.dias} días</span>`;
    }
    return `<tr>
      <td>${c.numero_carpeta}</td>
      <td>${c.imputado}</td>
      <td>${c.agraviado}</td>
      <td>${c.delito}</td>
      <td>${c.fiscalia_codigo}</td>
      <td>${c.despacho_codigo}</td>
      <td>${c.fiscal_responsable}</td>
      <td>${c.folios}</td>
      <td><span class="badge ${badgeClass}">${c.estado_label}</span> ${alerta}</td>
      <td>${(c.fecha_registro || '').substring(0, 10)}</td>
      <td><button type="button" class="btn btn-secondary" data-hist="${c.id}">Histórico</button></td>
    </tr>`;
  }).join('');

  tbody.querySelectorAll('[data-hist]').forEach(btn => {
    btn.addEventListener('click', () => verHistorial(btn.dataset.hist));
  });
}

document.getElementById('btn-buscar')?.addEventListener('click', cargarConsulta);
document.getElementById('btn-limpiar')?.addEventListener('click', () => {
  document.getElementById('form-filtros')?.reset();
  cargarConsulta();
});

async function verHistorial(carpetaId) {
  const res = await apiGet(API.carpetas, { action: 'historial', id: carpetaId });
  const ul = document.getElementById('historial-modal-list');
  if (!res.data?.length) {
    ul.innerHTML = '<li class="empty">Sin movimientos</li>';
  } else {
    ul.innerHTML = res.data.map(h => `
      <li>
        <strong>${h.tipo_movimiento}</strong> — ${h.descripcion}
        <span class="fecha">${h.fecha_movimiento}</span>
      </li>`).join('');
  }
  document.getElementById('modal-historial').style.display = 'flex';
}

document.getElementById('cerrar-historial')?.addEventListener('click', () => {
  document.getElementById('modal-historial').style.display = 'none';
});

document.getElementById('form-prestamo')?.addEventListener('submit', async e => {
  e.preventDefault();
  const body = Object.fromEntries(new FormData(e.target).entries());
  const res = await apiPost(API.prestamos, { action: 'prestar' }, body);
  toast(res.message || 'Error', res.success);
  if (res.success) e.target.reset();
});

document.getElementById('form-devolucion')?.addEventListener('submit', async e => {
  e.preventDefault();
  const body = Object.fromEntries(new FormData(e.target).entries());
  const res = await apiPost(API.prestamos, { action: 'devolver' }, body);
  toast(res.message || 'Error', res.success);
  if (res.success) e.target.reset();
});

document.getElementById('form-desarchivo')?.addEventListener('submit', async e => {
  e.preventDefault();
  const body = Object.fromEntries(new FormData(e.target).entries());
  const res = await apiPost(API.prestamos, { action: 'desarchivar' }, body);
  toast(res.message || 'Error', res.success);
  if (res.success) e.target.reset();
});

async function cargarReportePrestadas() {
  const res = await apiGet(API.reportes, { action: 'prestadas' });
  const tbody = document.querySelector('#tabla-prestadas tbody');
  if (!res.data?.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="empty">No hay carpetas prestadas</td></tr>';
    return;
  }
  tbody.innerHTML = res.data.map(r => `
    <tr>
      <td>${r.numero_carpeta}</td>
      <td>${r.fiscalia_codigo}</td>
      <td>${r.solicitante}</td>
      <td>${r.fecha_prestamo}</td>
      <td>${r.motivo}</td>
      <td>${r.dias_prestamo}</td>
      <td><span class="badge alerta-${r.alerta.color}">${r.alerta.texto}</span></td>
      <td>${r.correo_electronico}</td>
    </tr>`).join('');
}

async function cargarReporteDevueltas() {
  const res = await apiGet(API.reportes, { action: 'devueltas' });
  const tbody = document.querySelector('#tabla-devueltas tbody');
  if (!res.data?.length) {
    tbody.innerHTML = '<tr><td colspan="6" class="empty">No hay devoluciones registradas</td></tr>';
    return;
  }
  tbody.innerHTML = res.data.map(r => `
    <tr>
      <td>${r.numero_carpeta}</td>
      <td>${r.fiscalia_codigo}</td>
      <td>${r.solicitante}</td>
      <td>${r.fecha_prestamo}</td>
      <td>${r.fecha_devolucion}</td>
      <td>${r.motivo}</td>
    </tr>`).join('');
}

document.getElementById('btn-recordatorios')?.addEventListener('click', async () => {
  const res = await apiGet(API.prestamos, { action: 'enviar-recordatorios' });
  toast(res.message || 'Procesado', res.success);
});

document.getElementById('form-catalogo-f')?.addEventListener('submit', async e => {
  e.preventDefault();
  const res = await apiPost(API.catalogos, { action: 'crear-fiscalia' }, Object.fromEntries(new FormData(e.target)));
  toast(res.success ? 'Fiscalía registrada' : res.message, res.success);
  if (res.success) { e.target.reset(); await cargarCatalogos(); }
});

document.getElementById('form-catalogo-d')?.addEventListener('submit', async e => {
  e.preventDefault();
  const body = Object.fromEntries(new FormData(e.target).entries());
  body.fiscalia_id = parseInt(body.fiscalia_id, 10);
  const res = await apiPost(API.catalogos, { action: 'crear-despacho' }, body);
  toast(res.success ? 'Despacho registrado' : res.message, res.success);
  if (res.success) { e.target.reset(); await cargarCatalogos(); }
});

(async function init() {
  await cargarCatalogos();
  const fechaEl = document.getElementById('ingreso-fecha');
  if (fechaEl) {
    fechaEl.textContent = 'Fecha de registro: automática al guardar (' + new Date().toLocaleDateString('es-PE') + ')';
  }
  document.querySelectorAll('input[type="date"]').forEach(inp => {
    if (!inp.value) inp.value = new Date().toISOString().slice(0, 10);
  });
})();
