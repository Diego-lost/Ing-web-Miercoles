<?php

class CarpetaModel
{
    private const SELECT_BASE = '
        SELECT c.*,
               f.codigo AS fiscalia_codigo, f.nombre AS fiscalia_nombre,
               d.codigo AS despacho_codigo, d.nombre AS despacho_nombre,
               p.id AS prestamo_activo_id, p.solicitante AS prestamo_solicitante,
               p.fecha_prestamo, p.motivo AS prestamo_motivo,
               DATEDIFF(CURDATE(), p.fecha_prestamo) AS dias_prestamo
        FROM carpetas c
        JOIN fiscalias f ON f.id = c.fiscalia_id
        JOIN despachos d ON d.id = c.despacho_id
        LEFT JOIN prestamos p ON p.carpeta_id = c.id AND p.estado = \'ACTIVO\'
    ';

    public function crear(array $data): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO carpetas (
                numero_carpeta, imputado, agraviado, delito,
                fiscalia_id, despacho_id, fiscal_responsable,
                folios, estado_correo, correo_electronico, estado, fecha_registro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'ARCHIVO_CENTRAL\', NOW())'
        );
        $stmt->execute([
            $data['numero_carpeta'],
            $data['imputado'],
            $data['agraviado'],
            $data['delito'],
            $data['fiscalia_id'],
            $data['despacho_id'],
            $data['fiscal_responsable'],
            $data['folios'],
            $data['estado_correo'],
            $data['correo_electronico'],
        ]);
        $id = (int) $pdo->lastInsertId();

        HistorialService::registrar(
            $id,
            'INGRESO',
            'Carpeta ingresada al Archivo Central',
            $id
        );

        return $id;
    }

    public function buscar(array $filtros): array
    {
        $sql = self::SELECT_BASE . ' WHERE 1=1';
        $params = [];

        if (!empty($filtros['numero_carpeta'])) {
            $sql .= ' AND c.numero_carpeta LIKE ?';
            $params[] = '%' . $filtros['numero_carpeta'] . '%';
        }
        if (!empty($filtros['imputado'])) {
            $sql .= ' AND c.imputado LIKE ?';
            $params[] = '%' . $filtros['imputado'] . '%';
        }
        if (!empty($filtros['fiscalia_id'])) {
            $sql .= ' AND c.fiscalia_id = ?';
            $params[] = (int) $filtros['fiscalia_id'];
        }
        if (!empty($filtros['despacho_id'])) {
            $sql .= ' AND c.despacho_id = ?';
            $params[] = (int) $filtros['despacho_id'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= ' AND c.estado = ?';
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['delito'])) {
            $sql .= ' AND c.delito LIKE ?';
            $params[] = '%' . $filtros['delito'] . '%';
        }

        $sql .= ' ORDER BY c.fecha_registro DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = Database::connection()->prepare(self::SELECT_BASE . ' WHERE c.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function obtenerPorNumero(string $numero): ?array
    {
        $stmt = Database::connection()->prepare(
            self::SELECT_BASE . ' WHERE c.numero_carpeta = ?'
        );
        $stmt->execute([$numero]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function actualizarEstado(int $id, string $estado): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE carpetas SET estado = ? WHERE id = ?'
        );
        $stmt->execute([$estado, $id]);
    }

    public function existeNumero(string $numero): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT 1 FROM carpetas WHERE numero_carpeta = ?'
        );
        $stmt->execute([$numero]);
        return (bool) $stmt->fetch();
    }
}
