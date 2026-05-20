<?php
$config = require dirname(__DIR__) . '/shared/config.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$api = [
    'catalogos' => $base . '/../services/catalogos/index.php',
    'carpetas'  => $base . '/../services/carpetas/index.php',
    'prestamos' => $base . '/../services/prestamos/index.php',
    'reportes'  => $base . '/../services/reportes/index.php',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($config['app']['name']) ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    #modal-historial {
      display: none;
      position: fixed; inset: 0;
      background: rgba(0,0,0,.5);
      align-items: center; justify-content: center;
      z-index: 50;
    }
    #modal-historial .modal-box {
      background: #fff; padding: 1.5rem; border-radius: 10px;
      max-width: 500px; width: 90%; max-height: 80vh; overflow: auto;
    }
    .btn-sm { padding: .35rem .75rem; font-size: .8rem; }
  </style>
</head>
<body data-base="<?= htmlspecialchars($base) ?>">

<header>
  <h1><?= htmlspecialchars($config['app']['name']) ?></h1>
  <p>Huancayo — Control de carpetas fiscales · Arquitectura microservicios + MVC</p>
</header>

<nav class="tabs">
  <button type="button" class="active" data-panel="ingreso">Ingreso de carpetas</button>
  <button type="button" data-panel="consulta">Consulta de carpetas</button>
  <button type="button" data-panel="prestamo">Préstamo</button>
  <button type="button" data-panel="devolucion">Devolución</button>
  <button type="button" data-panel="desarchivo">Desarchivamiento</button>
  <button type="button" data-panel="reporte-prestadas">Reporte prestadas</button>
  <button type="button" data-panel="reporte-devueltas">Reporte devueltas</button>
  <button type="button" data-panel="catalogos">Catálogos</button>
</nav>

<main>

  <!-- INGRESO -->
  <section id="ingreso" class="panel active">
    <div class="card">
      <h2>Ingreso de carpetas al Archivo Central</h2>
      <p id="ingreso-fecha" style="margin-bottom:1rem;font-size:.9rem;color:#718096;"></p>
      <form id="form-ingreso" class="grid-form">
        <label>N° Carpeta fiscal
          <input name="numero_carpeta" required placeholder="12-2026">
        </label>
        <label>Imputado
          <input name="imputado" required placeholder="JUAN PEREZ QUIJADA">
        </label>
        <label>Agraviado
          <input name="agraviado" required placeholder="ROSA CARDENAS PONCE">
        </label>
        <label>Delito
          <input name="delito" required placeholder="ROBO SIMPLE">
        </label>
        <label>Fiscalía <span class="arch-badge">pre-registrada</span>
          <select name="fiscalia_id" id="ingreso-fiscalia" class="fiscalia-select" required></select>
        </label>
        <label>Despacho <span class="arch-badge">pre-registrado</span>
          <select name="despacho_id" id="ingreso-despacho" required>
            <option value="">-- Seleccione fiscalía primero --</option>
          </select>
        </label>
        <label>Fiscal responsable
          <input name="fiscal_responsable" required placeholder="MILAGROS AMAYA LINARES">
        </label>
        <label>Folios
          <input name="folios" type="number" min="1" required value="200">
        </label>
        <label>Estado correo
          <select name="estado_correo" required>
            <option value="ARCHIVO">ARCHIVO</option>
            <option value="CONSENTIDO">CONSENTIDO</option>
          </select>
        </label>
        <label>Correo electrónico
          <input name="correo_electronico" type="email" required placeholder="mila@gmail.com">
        </label>
        <div class="btn-row" style="grid-column:1/-1">
          <button type="submit" class="btn btn-primary">Registrar carpeta</button>
        </div>
      </form>
    </div>
  </section>

  <!-- CONSULTA -->
  <section id="consulta" class="panel">
    <div class="card">
      <h2>Consulta de carpetas</h2>
      <form id="form-filtros" class="grid-form" onsubmit="return false">
        <label>N° Carpeta
          <input id="filtro-numero" placeholder="12-2026">
        </label>
        <label>Imputado
          <input id="filtro-imputado">
        </label>
        <label>Delito
          <input id="filtro-delito">
        </label>
        <label>Fiscalía
          <select id="filtro-fiscalia" class="fiscalia-select"></select>
        </label>
        <label>Despacho
          <select id="filtro-despacho"></select>
        </label>
        <label>Estado
          <select id="filtro-estado">
            <option value="">Todos</option>
            <option value="ARCHIVO_CENTRAL">Archivo Central</option>
            <option value="PRESTADA">Prestada</option>
            <option value="DESARCHIVADA">Desarchivada</option>
          </select>
        </label>
      </form>
      <div class="btn-row">
        <button type="button" id="btn-buscar" class="btn btn-primary">Buscar</button>
        <button type="button" id="btn-limpiar" class="btn btn-secondary">Limpiar filtros</button>
      </div>
    </div>
    <div class="card">
      <table id="tabla-consulta">
        <thead>
          <tr>
            <th>N° Carpeta</th><th>Imputado</th><th>Agraviado</th><th>Delito</th>
            <th>Fiscalía</th><th>Despacho</th><th>Fiscal resp.</th><th>Folios</th>
            <th>Estado</th><th>F. registro</th><th></th>
          </tr>
        </thead>
        <tbody><tr><td colspan="11" class="empty">Use los filtros y pulse Buscar</td></tr></tbody>
      </table>
    </div>
  </section>

  <!-- PRESTAMO -->
  <section id="prestamo" class="panel">
    <div class="card">
      <h2>Préstamo de carpeta</h2>
      <form id="form-prestamo" class="grid-form">
        <label>N° Carpeta fiscal
          <input name="numero_carpeta" required placeholder="12-2026">
        </label>
        <label>Solicitante
          <input name="solicitante" required placeholder="MILAGROS AMAYA LINARES">
        </label>
        <label>Fecha de préstamo
          <input name="fecha_prestamo" type="date" required>
        </label>
        <label>Motivo de préstamo
          <textarea name="motivo" rows="2" required placeholder="COPIA DE ACTUADOS"></textarea>
        </label>
        <div class="btn-row" style="grid-column:1/-1">
          <button type="submit" class="btn btn-accent">Registrar préstamo</button>
        </div>
      </form>
    </div>
  </section>

  <!-- DEVOLUCION -->
  <section id="devolucion" class="panel">
    <div class="card">
      <h2>Devolución de carpeta</h2>
      <p style="margin-bottom:1rem;font-size:.9rem;">Verifique la carpeta prestada y regístrela como devuelta. El estado volverá a <strong>Archivo Central</strong>.</p>
      <form id="form-devolucion" class="grid-form">
        <label>N° Carpeta fiscal
          <input name="numero_carpeta" required>
        </label>
        <div class="btn-row">
          <button type="submit" class="btn btn-primary">Registrar devolución</button>
        </div>
      </form>
    </div>
  </section>

  <!-- DESARCHIVO -->
  <section id="desarchivo" class="panel">
    <div class="card">
      <h2>Desarchivamiento</h2>
      <p style="margin-bottom:1rem;font-size:.9rem;color:#c05621;">Una carpeta desarchivada <strong>no podrá devolverse</strong> al Archivo Central.</p>
      <form id="form-desarchivo" class="grid-form">
        <label>N° Carpeta fiscal
          <input name="numero_carpeta" required>
        </label>
        <label>Solicitante
          <input name="solicitante" required>
        </label>
        <label>Fecha
          <input name="fecha_desarchivo" type="date" required>
        </label>
        <label>Motivo
          <textarea name="motivo" rows="2" required></textarea>
        </label>
        <div class="btn-row" style="grid-column:1/-1">
          <button type="submit" class="btn btn-accent">Registrar desarchivamiento</button>
        </div>
      </form>
    </div>
  </section>

  <!-- REPORTE PRESTADAS -->
  <section id="reporte-prestadas" class="panel">
    <div class="card">
      <h2>Reporte de carpetas prestadas</h2>
      <p style="margin-bottom:1rem;font-size:.85rem;">
        Alertas: <span class="badge alerta-verde">≤3 días verde</span>
        <span class="badge alerta-amarillo">4-5 días amarillo</span>
        <span class="badge alerta-rojo">&gt;5 días rojo (≥10 crítico)</span>
      </p>
      <div class="btn-row" style="margin-bottom:1rem">
        <button type="button" id="btn-recordatorios" class="btn btn-secondary">Enviar recordatorios (5 y 10 días)</button>
      </div>
      <table id="tabla-prestadas">
        <thead>
          <tr>
            <th>Carpeta</th><th>Fiscalía</th><th>Solicitante</th><th>F. préstamo</th>
            <th>Motivo</th><th>Días</th><th>Alerta</th><th>Correo</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </section>

  <!-- REPORTE DEVUELTAS -->
  <section id="reporte-devueltas" class="panel">
    <div class="card">
      <h2>Reporte de carpetas devueltas</h2>
      <table id="tabla-devueltas">
        <thead>
          <tr>
            <th>Carpeta</th><th>Fiscalía</th><th>Solicitante</th>
            <th>F. préstamo</th><th>F. devolución</th><th>Motivo</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </section>

  <!-- CATALOGOS -->
  <section id="catalogos" class="panel">
    <div class="card">
      <h2>Registrar Fiscalía</h2>
      <form id="form-catalogo-f" class="grid-form">
        <label>Código <input name="codigo" required placeholder="4FPPC"></label>
        <label>Nombre <input name="nombre" required></label>
        <div class="btn-row"><button type="submit" class="btn btn-primary">Guardar fiscalía</button></div>
      </form>
    </div>
    <div class="card">
      <h2>Registrar Despacho</h2>
      <form id="form-catalogo-d" class="grid-form">
        <label>Fiscalía
          <select name="fiscalia_id" id="cat-despacho-fiscalia" required></select>
        </label>
        <label>Código <input name="codigo" required placeholder="HYO"></label>
        <label>Nombre <input name="nombre" required></label>
        <div class="btn-row"><button type="submit" class="btn btn-primary">Guardar despacho</button></div>
      </form>
    </div>
  </section>

</main>

<div id="modal-historial">
  <div class="modal-box">
    <h2 style="margin-bottom:1rem;color:var(--primary)">Histórico de movimientos</h2>
    <ul id="historial-modal-list" class="historial-list"></ul>
    <div class="btn-row" style="margin-top:1rem">
      <button type="button" id="cerrar-historial" class="btn btn-secondary">Cerrar</button>
    </div>
  </div>
</div>

<script>
  window.API_ENDPOINTS = <?= json_encode($api, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
