<?php

class CatalogoModel
{
    public function listarFiscalias(): array
    {
        return Database::connection()->query('SELECT * FROM fiscalias ORDER BY nombre')->fetchAll();
    }

    public function listarDespachos(?int $fiscaliaId = null): array
    {
        $pdo = Database::connection();
        if ($fiscaliaId) {
            $stmt = $pdo->prepare(
                'SELECT d.*, f.codigo AS fiscalia_codigo
                 FROM despachos d
                 JOIN fiscalias f ON f.id = d.fiscalia_id
                 WHERE d.fiscalia_id = ?
                 ORDER BY d.nombre'
            );
            $stmt->execute([$fiscaliaId]);
            return $stmt->fetchAll();
        }
        return $pdo->query(
            'SELECT d.*, f.codigo AS fiscalia_codigo
             FROM despachos d
             JOIN fiscalias f ON f.id = d.fiscalia_id
             ORDER BY f.nombre, d.nombre'
        )->fetchAll();
    }

    public function crearFiscalia(string $codigo, string $nombre): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO fiscalias (codigo, nombre) VALUES (?, ?)'
        );
        $stmt->execute([$codigo, $nombre]);
        return (int) Database::connection()->lastInsertId();
    }

    public function crearDespacho(int $fiscaliaId, string $codigo, string $nombre): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO despachos (fiscalia_id, codigo, nombre) VALUES (?, ?, ?)'
        );
        $stmt->execute([$fiscaliaId, $codigo, $nombre]);
        return (int) Database::connection()->lastInsertId();
    }
}
