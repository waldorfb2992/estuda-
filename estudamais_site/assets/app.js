'use strict';

const STORAGE_KEY = 'estudamais_atividades_v1';
const $ = (selector) => document.querySelector(selector);

function offsetDate(days) {
  const d = new Date();
  d.setDate(d.getDate() + days);
  return [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2, '0')].join('-');
}
function examples() {
  return [
    {id: 1, title: 'Revisar aula de JavaScript', subject: 'Programação', due: offsetDate(1), priority: 'alta', notes: 'Praticar eventos e funções.', done: false},
    {id: 2, title: 'Ler capítulo de História', subject: 'História', due: offsetDate(3), priority: 'normal', notes: '', done: false},
    {id: 3, title: 'Organizar material de aula', subject: 'Outras', due: offsetDate(-1), priority: 'baixa', notes: '', done: true}
  ];
}
function getTasks() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (raw === null) {
      const initial = examples();
      localStorage.setItem(STORAGE_KEY, JSON.stringify(initial));
      return initial;
    }
    const value = JSON.parse(raw);
    return Array.isArray(value) ? value : [];
  } catch (e) {
    return [];
  }
}
function saveTasks(tasks) {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(tasks)); }
  catch (e) { alert('Não foi possível salvar no navegador. Confira as configurações de armazenamento.'); }
}
function progress(tasks) {
  return tasks.length ? Math.round(tasks.filter((task) => task.done).length * 100 / tasks.length) : 0;
}
function formatDate(iso) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(iso || '')) return 'Sem prazo';
  const [y,m,d] = iso.split('-');
  return `${d}/${m}/${y}`;
}
function elem(tag, className, text) {
  const el = document.createElement(tag);
  if (className) el.className = className;
  if (text !== undefined) el.textContent = String(text);
  return el;
}
function updateHome() {
  const tasks = getTasks();
  const done = tasks.filter((task) => task.done).length;
  $('#stat-total').textContent = tasks.length;
  $('#stat-done').textContent = done;
  $('#stat-pending').textContent = tasks.length - done;
  $('#hero-progress-label').textContent = `${progress(tasks)}%`;
  $('#hero-progress').style.width = `${progress(tasks)}%`;
}
function initTasks() {
  const form = $('#task-form');
  const list = $('#task-list');
  const error = $('#task-error');
  function setError(text) { error.hidden = !text; error.textContent = text; }
  function resetForm() {
    form.reset();
    $('#task-id').value = '';
    $('#task-due').value = offsetDate(1);
    $('#task-submit').textContent = '＋ Adicionar atividade';
    $('#task-cancel').hidden = true;
    $('#form-title').textContent = 'Nova atividade';
    setError('');
  }
  function render() {
    const all = getTasks();
    const term = $('#task-search').value.trim().toLocaleLowerCase('pt-BR');
    const filter = $('#task-filter').value;
    const shown = all.filter((task) => {
      if (filter === 'pendentes' && task.done) return false;
      if (filter === 'concluidas' && !task.done) return false;
      return `${task.title} ${task.subject} ${task.notes}`.toLocaleLowerCase('pt-BR').includes(term);
    }).sort((a, b) => Number(a.done) - Number(b.done) || String(a.due).localeCompare(String(b.due)));
    list.replaceChildren();
    $('#task-summary').textContent = `${all.length} atividade${all.length === 1 ? '' : 's'} no total`;
    const done = all.filter((t) => t.done).length;
    $('#task-progress-text').textContent = `${done} de ${all.length} concluída${done === 1 ? '' : 's'}`;
    $('#task-progress').style.width = `${progress(all)}%`;
    if (!shown.length) {
      const empty = elem('div', 'empty-state');
      empty.append(elem('span','empty-icon','✦'), elem('strong','', 'Nenhuma atividade encontrada.'), elem('p','', 'Cadastre uma nova tarefa ou altere o filtro.'));
      list.append(empty); return;
    }
    shown.forEach((task) => {
      const card = elem('article', `task-item${task.done ? ' done' : ''}`);
      const check = elem('input', 'task-tick');
      check.type = 'checkbox'; check.checked = Boolean(task.done); check.setAttribute('aria-label', `Concluir atividade ${task.title}`);
      check.addEventListener('change', () => { const tasks = getTasks(); const found = tasks.find((t) => t.id === task.id); if (found) found.done = check.checked; saveTasks(tasks); render(); });
      const body = elem('div','task-body');
      const title = elem('h3','',task.title);
      const meta = elem('div','task-meta');
      meta.append(elem('span','',task.subject), elem('span','',`◷ ${formatDate(task.due)}`), elem('span',`priority-${task.priority}`,`Prioridade ${task.priority}`));
      body.append(title, meta);
      if (task.notes) body.append(elem('p','task-description',task.notes));
      const actions = elem('div','task-actions');
      const edit = elem('button','icon-btn','Editar'); edit.type = 'button'; edit.setAttribute('aria-label', `Editar ${task.title}`);
      edit.addEventListener('click', () => {
        $('#task-id').value = task.id; $('#task-title').value = task.title;
        $('#task-subject').value = task.subject; $('#task-due').value = task.due;
        $('#task-priority').value = task.priority; $('#task-notes').value = task.notes || '';
        $('#task-submit').textContent = 'Salvar alterações'; $('#task-cancel').hidden = false;
        $('#form-title').textContent = 'Editar atividade'; setError('');
        form.scrollIntoView({behavior: 'smooth', block: 'start'}); $('#task-title').focus();
      });
      const del = elem('button','icon-btn delete','Excluir'); del.type = 'button'; del.setAttribute('aria-label', `Excluir ${task.title}`);
      del.addEventListener('click', () => {
        if (!confirm(`Excluir a atividade "${task.title}"?`)) return;
        saveTasks(getTasks().filter((t) => t.id !== task.id)); resetForm(); render();
      });
      actions.append(edit,del);card.append(check,body,actions);list.append(card);
    });
  }
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const title = $('#task-title').value.trim();
    const subject = $('#task-subject').value;
    const due = $('#task-due').value;
    const priority = $('#task-priority').value;
    const notes = $('#task-notes').value.trim();
    if (title.length < 3 || !subject || !/^\d{4}-\d{2}-\d{2}$/.test(due)) { setError('Preencha nome (mínimo de 3 letras), matéria e prazo.'); return; }
    const tasks = getTasks();
    const id = Number($('#task-id').value);
    const previous = tasks.find((t) => t.id === id);
    if (previous) Object.assign(previous, {title, subject, due, priority, notes});
    else tasks.push({id: Date.now(), title, subject, due, priority, notes, done: false});
    saveTasks(tasks); resetForm(); render();
  });
  $('#task-cancel').addEventListener('click', resetForm);
  $('#task-search').addEventListener('input', render);
  $('#task-filter').addEventListener('change', render);
  resetForm(); render();
}
function initContact() {
  const form = $('#contact-form'); const feedback = $('#contact-feedback');
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const name = $('#contact-name').value.trim();
    const email = $('#contact-email').value.trim();
    const msg = $('#contact-message').value.trim();
    const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    feedback.hidden = false;
    if (name.length < 2 || !validEmail || msg.length < 10) {
      feedback.classList.remove('success'); feedback.textContent = 'Confira seu nome, e-mail e mensagem (mínimo de 10 caracteres).'; return;
    }
    feedback.classList.add('success');
    feedback.textContent = `Obrigado, ${name}! A validação funcionou. Este formulário é apenas uma demonstração e não envia mensagens.`;
    form.reset();
  });
}
document.addEventListener('DOMContentLoaded', () => {
  const menu = $('.menu-toggle');
  const nav = $('.main-nav');
  if (menu && nav) {
    menu.addEventListener('click', () => { const open = nav.classList.toggle('open'); menu.setAttribute('aria-expanded', String(open)); menu.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu'); });
  }
  const page = document.body.dataset.page;
  if (page === 'home') updateHome();
  if (page === 'tasks') initTasks();
  if (page === 'contact') initContact();
});
