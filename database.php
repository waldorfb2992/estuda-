<?php
// PDO + SQLite: cria o banco de dados automaticamente na primeira execução.
// Requer PHP com extensão pdo_sqlite habilitada.
declare(strict_types=1);
$directory = __DIR__ . '/storage';
if (!is_dir($directory)) {
    mkdir($directory, 0775, true);
}
try {
    $pdo = new PDO('sqlite:' . $directory . '/materias.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('CREATE TABLE IF NOT EXISTS materias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        descricao TEXT NOT NULL DEFAULT \'\',
        cor TEXT NOT NULL DEFAULT \'#5c59e8\',
        criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');
} catch (PDOException $e) {
    http_response_code(500);
    exit('Erro ao conectar ao banco. Verifique se o PHP tem a extensão PDO SQLite habilitada e permissão de escrita na pasta storage.');
}
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
