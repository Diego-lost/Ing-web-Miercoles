# Sistema de Control de Carpetas Fiscales — Archivo Central

Práctica de Ingeniería Web (Huancayo, 2026). Arquitectura **microservicios + MVC** con PHP y MySQL en **localhost** (XAMPP).

## Requisitos

- XAMPP (Apache + MySQL + PHP 8+)
- Navegador web

## Instalación

1. **Iniciar** Apache y MySQL en el Panel de Control de XAMPP.

2. **Crear la base de datos** — en phpMyAdmin (`http://localhost/phpmyadmin`):
   - Importar el archivo `database/schema.sql`
   - O ejecutar en consola:
   ```bash
   "c:\xampp\mysql\bin\mysql.exe" -u root < "c:\xampp\htdocs\Practica Ing.web\database\schema.sql"
   ```

3. **Configurar conexión** (si su MySQL tiene contraseña), editar `shared/config.php`:
   ```php
   'pass' => 'su_contraseña',
   ```

4. **Abrir la aplicación**:
   ```
   http://localhost/Practica%20Ing.web/public/
   ```

## Módulos implementados

| Módulo | Descripción |
|--------|-------------|
| **Ingreso de carpetas** | Registro con todos los campos del PDF; fiscalía y despacho pre-registrados; fecha automática |
| **Consulta de carpetas** | Listado con filtros; estado Prestada / Archivo Central / Desarchivada |
| **Préstamo** | Registro de solicitante, fecha y motivo; cambia estado a Prestada |
| **Devolución** | Verificación y vuelta a Archivo Central |
| **Desarchivamiento** | Estado Desarchivada (no se puede devolver) |
| **Histórico** | Movimientos con fechas (ingreso, préstamo, devolución, desarchivo) |
| **Reporte prestadas** | Alertas: ≤3 verde, 4-5 amarillo, >5 rojo |
| **Reporte devueltas** | Carpetas ya devueltas |
| **Catálogos** | Alta de fiscalías y despachos |

## Microservicios

```
services/
├── catalogos/   → Fiscalías y despachos
├── carpetas/    → Ingreso, consulta, histórico
├── prestamos/   → Préstamo, devolución, desarchivo, correos
└── reportes/    → Reportes prestadas y devueltas
```

Cada servicio sigue **MVC** (`models/`, `controllers/`, `index.php`).

## Recordatorios por correo (5 y 10 días)

- Botón **Enviar recordatorios** en el reporte de prestadas, o
- Script programado:
  ```bash
  php "c:\xampp\htdocs\Practica Ing.web\cron\recordatorios.php"
  ```

Requiere que `mail()` esté configurado en PHP (en XAMPP suele usarse un SMTP como Mercury o configurar `sendmail`).

## Datos de ejemplo (PDF)

Tras importar el SQL ya existen:
- Fiscalía: **4FPPC** (Huancayo)
- Despacho: **HYO**

Puede registrar la carpeta **12-2026** con los datos del enunciado desde el módulo de ingreso.
