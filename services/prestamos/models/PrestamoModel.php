<?php

require_once dirname(__DIR__, 2) . '/carpetas/models/CarpetaModel.php';

class PrestamoModel
{
    private CarpetaModel $carpetaModel;

    public function __construct()
    {
        $this->carpetaModel = new CarpetaModel();
    }

    public function registrarPrestamo(int $carpetaId, string $solicitante, string $fecha, string $motivo): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO prestamos (carpeta_id, solicitante, fecha_prestamo, motivo)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$carpetaId, $solicitante, $fecha, $motivo]);
            $prestamoId = (int) $pdo->lastInsertId();

            $this->carpetaModel->actualizarEstado($carpetaId, 'PRESTADA');

            HistorialService::registrar(
                $carpetaId,
                'PRESTAMO',
                "Préstamo a {$solicitante}. Motivo: {$motivo}",
                $prestamoId
            );

            $pdo->commit();
            return $prestamoId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function registrarDevolucion(int $carpetaId): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'SELECT id FROM prestamos WHERE carpeta_id = ? AND estado = \'ACTIVO\' LIMIT 1'
            );
            $stmt->execute([$carpetaId]);
            $prestamo = $stmt->fetch();
            if (!$prestamo) {
                throw new RuntimeException('No hay préstamo activo para esta carpeta');
            }

            $upd = $pdo->prepare(
                'UPDATE prestamos SET estado = \'DEVUELTO\', fecha_devolucion = CURDATE() WHERE id = ?'
            );
            $upd->execute([$prestamo['id']]);

            $this->carpetaModel->actualizarEstado($carpetaId, 'ARCHIVO_CENTRAL');

            HistorialService::registrar(
                $carpetaId,
                'DEVOLUCION',
                'Carpeta devuelta y verificada. Estado: Archivo Central',
                (int) $prestamo['id']
            );

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function registrarDesarchivo(int $carpetaId, string $solicitante, string $fecha, string $motivo): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO desarchivamientos (carpeta_id, solicitante, motivo, fecha_desarchivo)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$carpetaId, $solicitante, $motivo, $fecha]);
            $id = (int) $pdo->lastInsertId();

            $activo = $pdo->prepare(
                'SELECT id FROM prestamos WHERE carpeta_id = ? AND estado = \'ACTIVO\''
            );
            $activo->execute([$carpetaId]);
            if ($activo->fetch()) {
                throw new RuntimeException('Debe devolver la carpeta antes de desarchivar, o registrar desarchivo sin préstamo activo');
            }

            $this->carpetaModel->actualizarEstado($carpetaId, 'DESARCHIVADA');

            HistorialService::registrar(
                $carpetaId,
                'DESARCHIVAMIENTO',
                "Desarchivamiento solicitado por {$solicitante}. Motivo: {$motivo}",
                $id
            );

            $pdo->commit();
            return $id;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function listarPrestados(): array
    {
        $sql = '
            SELECT c.*, f.codigo AS fiscalia_codigo, f.nombre AS fiscalia_nombre,
                   d.codigo AS despacho_codigo,
                   p.id AS prestamo_id, p.solicitante, p.fecha_prestamo, p.motivo,
                   DATEDIFF(CURDATE(), p.fecha_prestamo) AS dias_prestamo
            FROM prestamos p
            JOIN carpetas c ON c.id = p.carpeta_id
            JOIN fiscalias f ON f.id = c.fiscalia_id
            JOIN despachos d ON d.id = c.despacho_id
            WHERE p.estado = \'ACTIVO\'
            ORDER BY p.fecha_prestamo ASC
        ';
        return Database::connection()->query($sql)->fetchAll();
    }

    public function listarDevueltos(): array
    {
        $sql = '
            SELECT c.numero_carpeta, f.codigo AS fiscalia_codigo,
                   p.solicitante, p.fecha_prestamo, p.fecha_devolucion, p.motivo
            FROM prestamos p
            JOIN carpetas c ON c.id = p.carpeta_id
            JOIN fiscalias f ON f.id = c.fiscalia_id
            WHERE p.estado = \'DEVUELTO\'
            ORDER BY p.fecha_devolucion DESC
        ';
        return Database::connection()->query($sql)->fetchAll();
    }

    public function prestamosParaRecordatorio(): array
    {
        $sql = '
            SELECT p.*, c.numero_carpeta, c.correo_electronico, c.imputado,
                   DATEDIFF(CURDATE(), p.fecha_prestamo) AS dias_prestamo
            FROM prestamos p
            JOIN carpetas c ON c.id = p.carpeta_id
            WHERE p.estado = \'ACTIVO\'
              AND DATEDIFF(CURDATE(), p.fecha_prestamo) IN (5, 10)
        ';
        return Database::connection()->query($sql)->fetchAll();
    }

    public function yaSeEnvioRecordatorio(int $prestamoId, int $dias): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT 1 FROM correos_recordatorio WHERE prestamo_id = ? AND dias_transcurridos = ?'
        );
        $stmt->execute([$prestamoId, $dias]);
        return (bool) $stmt->fetch();
    }

    public function marcarRecordatorioEnviado(int $prestamoId, int $dias): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO correos_recordatorio (prestamo_id, dias_transcurridos) VALUES (?, ?)'
        );
        $stmt->execute([$prestamoId, $dias]);
    }
}
