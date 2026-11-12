# Estuda+ — Projeto Final de Desenvolvimento Web

**Tema:** Organizador de estudos. Projeto didático usando HTML, CSS e JavaScript; com desafio extra em PHP, PDO e SQLite.

## Estrutura

- `index.html` — página inicial, menu, introdução e estatísticas.
- `atividades.html` — formulário e lista de atividades.
- `contato.html` — formulário demonstrativo com validação JavaScript.
- `assets/style.css` — estilos de todas as páginas, incluindo responsividade.
- `assets/app.js` — criação, busca, filtro, edição, conclusão e exclusão de tarefas; validação do formulário; menu móvel.
- `materias.php` — CRUD extra de matérias usando PDO e banco SQLite.
- `database.php` — conexão PDO e criação automática da tabela `materias`.
- `screenshots/` — capturas demonstrativas do projeto rodando.

## Para abrir o site (HTML, CSS e JavaScript)

1. Extraia o ZIP.
2. Abra `index.html` no navegador (Chrome ou Edge). Para garantir que o localStorage seja compartilhado entre páginas, **prefira executar um servidor local** com `python -m http.server 5500` na pasta do projeto e abrir `http://localhost:5500/index.html` (ou use a extensão Live Server do VS Code).
3. Visite **Atividades** para cadastrar, pesquisar, editar e concluir tarefas.
4. Visite **Contato** para testar a validação de formulário.

As atividades ficam no **localStorage deste navegador**; a lista inicialmente contém três exemplos. O site funciona sem PHP nas três páginas HTML. Os dados ficam apenas no navegador, não são enviados a um servidor.

## Desafio extra: PHP + banco de dados + PDO

Para testar o CRUD de matérias, é necessário **PHP 8 ou superior com PDO SQLite habilitado**:

1. Abra um terminal **dentro da pasta** `estudamais_site`.
2. Execute `php -S localhost:8000`.
3. No navegador, abra `http://localhost:8000/materias.php`.
4. Faça os quatro testes: **cadastrar**, **listar**, **editar** e **excluir** uma matéria.

Na primeira execução, o programa cria a tabela `materias` no arquivo `storage/materias.sqlite`. A pasta deve permitir gravação. O arquivo de banco **não está no Git** (`.gitignore`) para evitar compartilhar dados locais.

### Alternativa XAMPP (Windows)

Instale XAMPP, copie a pasta do projeto para `C:\\xampp\\htdocs\\estudamais_site`, inicie o módulo Apache e acesse `http://localhost/estudamais_site/materias.php`. Se houver aviso de PDO SQLite, habilite as extensões `pdo_sqlite` e `sqlite3` no `php.ini` e reinicie o Apache.

