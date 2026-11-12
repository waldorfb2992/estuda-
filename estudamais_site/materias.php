<?php
// Desafio extra — CRUD completo de Matérias usando PDO e SQLite.
declare(strict_types=1);
require __DIR__ . '/database.php';
session_start();
if (empty($_SESSION['token'])) { $_SESSION['token'] = bin2hex(random_bytes(32)); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['token'], (string)($_POST['token'] ?? ''))) {
        $error = 'Falha de segurança: atualize a página.';
    } else {
        $action = (string)($_POST['acao'] ?? '');
        if ($action === 'salvar') {
            $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            $nome = trim((string)($_POST['nome'] ?? ''));
            $descricao = trim((string)($_POST['descricao'] ?? ''));
            $cor = (string)($_POST['cor'] ?? '#5c59e8');
            if ($nome === '' || (function_exists('mb_strlen') ? mb_strlen($nome) : strlen($nome)) > 80 || (function_exists('mb_strlen') ? mb_strlen($descricao) : strlen($descricao)) > 300 || !preg_match('/^#[0-9a-fA-F]{6}$/', $cor)) {
                $error = 'Preencha um nome válido (até 80 caracteres), descrição de até 300 e escolha uma cor.';
            } else {
                if ($id) {
                    $query = $pdo->prepare('UPDATE materias SET nome = ?, descricao = ?, cor = ? WHERE id = ?');
                    $query->execute([$nome, $descricao, $cor, $id]);
                } else {
                    $query = $pdo->prepare('INSERT INTO materias (nome, descricao, cor) VALUES (?, ?, ?)');
                    $query->execute([$nome, $descricao, $cor]);
                }
                header('Location: materias.php?salvo=1'); exit;
            }
        } elseif ($action === 'excluir') {
            $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
            if ($id) {
                $query = $pdo->prepare('DELETE FROM materias WHERE id = ?');
                $query->execute([$id]);
                header('Location: materias.php?excluido=1'); exit;
            }
            $error = 'Identificador inválido.';
        }
    }
}
$editar = null;
$idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
if ($idEditar) {
    $query = $pdo->prepare('SELECT * FROM materias WHERE id = ?');
    $query->execute([$idEditar]);
    $editar = $query->fetch() ?: null;
}
$materias = $pdo->query('SELECT * FROM materias ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Estuda+ | Matérias (PHP)</title><link rel="stylesheet" href="assets/style.css"><script src="assets/app.js" defer></script></head>
<body data-page="php"><a class="skip-link" href="#conteudo">Pular para conteúdo</a><header class="site-header"><div class="container header-inner"><a class="brand" href="index.html"><span class="brand-mark">✦</span> Estuda<span class="brand-plus">+</span></a><button class="menu-toggle" aria-expanded="false" aria-controls="menu-principal" aria-label="Abrir menu">☰</button><nav class="main-nav" id="menu-principal" aria-label="Menu principal"><a class="nav-link" href="index.html">Início</a><a class="nav-link" href="atividades.html">Atividades</a><a class="nav-link" href="contato.html">Contato</a><a class="nav-link active nav-extra" aria-current="page" href="materias.php">Matérias <span class="mini-tag">PHP extra</span></a></nav></div></header>
<main class="container page-content" id="conteudo"><div class="page-banner"><div><span class="section-kicker">DESAFIO EXTRA — PHP + PDO</span><h1>Cadastro de matérias <span class="sparkle">✳</span></h1><p>Cadastre, liste, edite e exclua registros em banco de dados SQLite.</p></div><span class="banner-decoration">▤</span></div>
<?php if ($error): ?><div class="alert bad" role="alert"><?= h($error) ?></div><?php endif; ?>
<?php if (isset($_GET['salvo'])): ?><div class="alert ok">Matéria salva com sucesso!</div><?php endif; ?>
<?php if (isset($_GET['excluido'])): ?><div class="alert ok">Matéria excluída com sucesso!</div><?php endif; ?>
<div class="crud-grid"><section class="surface form-panel"><div class="panel-heading"><span class="panel-icon">✎</span><div><h2><?= $editar ? 'Editar matéria' : 'Nova matéria' ?></h2><p>Registros persistentes no SQLite</p></div></div>
<form method="post" action="materias.php"><input type="hidden" name="token" value="<?= h($_SESSION['token']) ?>"><input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= $editar ? (int)$editar['id'] : '' ?>">
<div class="field"><label for="nome">Nome da matéria *</label><input id="nome" name="nome" required maxlength="80" placeholder="Ex.: Programação Web" value="<?= h((string)($editar['nome'] ?? '')) ?>"></div>
<div class="field"><label for="descricao">Descrição</label><textarea id="descricao" name="descricao" maxlength="300" rows="3" placeholder="Sobre o que é essa matéria?"><?= h((string)($editar['descricao'] ?? '')) ?></textarea></div>
<div class="field"><label for="cor">Cor de identificação</label><input type="color" name="cor" id="cor" value="<?= h((string)($editar['cor'] ?? '#5c59e8')) ?>" style="height:44px;padding:5px"></div><button class="btn btn-primary btn-full" type="submit"><?= $editar ? 'Salvar alterações' : 'Cadastrar matéria' ?></button>
<?php if ($editar): ?><a class="btn btn-text btn-full" href="materias.php">Cancelar edição</a><?php endif; ?></form><p class="help-text">O armazenamento em SQLite é diferente do localStorage usado nas atividades.</p></section>
<section class="surface task-panel"><div class="panel-heading"><span class="panel-icon">▤</span><div><h2>Matérias cadastradas</h2><p><?= count($materias) ?> registro(s) no banco</p></div></div>
<?php if (!$materias): ?><div class="empty-state"><span class="empty-icon">✦</span><strong>Nenhuma matéria cadastrada</strong><p>Use o formulário ao lado para adicionar a primeira.</p></div>
<?php else: ?><table class="crud-list"><thead><tr><th>Matéria</th><th>Descrição</th><th>Ações</th></tr></thead><tbody>
<?php foreach ($materias as $item): ?><tr><td><span class="swatch" style="background:<?= h($item['cor']) ?>"></span><strong><?= h($item['nome']) ?></strong></td><td><?= h($item['descricao'] ?: '—') ?></td><td><div class="crud-actions"><a class="btn btn-light" href="materias.php?editar=<?= (int)$item['id'] ?>">Editar</a><form method="post" action="materias.php" onsubmit="return confirm('Excluir esta matéria?')"><input type="hidden" name="token" value="<?= h($_SESSION['token']) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button type="submit" class="btn btn-danger">Excluir</button></form></div></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?></section></div></main><footer class="site-footer"><div class="container footer-inner"><a class="brand footer-brand" href="index.html"><span class="brand-mark">✦</span> Estuda<span class="brand-plus">+</span></a><p>Projeto acadêmico • PHP + PDO + SQLite (extra)</p><span>2026</span></div></footer></body></html>
