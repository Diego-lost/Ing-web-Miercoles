<?php

class HistorialService
{
    public static function registrar(
        int $carpetaId,
        string $tipo,
        string $descripcion,
        ?int $referenciaId = null
    ): void {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO historial_movimientos (carpeta_id, tipo_movimiento, descripcion, referencia_id)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$carpetaId, $tipo, $descripcion, $referenciaId]);
    }

    public static function porCarpeta(int $carpetaId): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'SELECT * FROM historial_movimientos WHERE carpeta_id = ? ORDER BY fecha_movimiento DESC'
        );
        $stmt->execute([$carpetaId]);
        return $stmt->fetchAll();
    }
}
