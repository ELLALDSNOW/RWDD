const todoBoard = document.getElementById('todo-board');
const createbutton = document.getElementById('create-todo-page');


async function postJson(url, data) {
  const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
  return res.json();
}

function renderTaskItem(li, todoObj, taskObj) {
  const checkbox = document.createElement('input');
  checkbox.type = 'checkbox';
  checkbox.checked = !!taskObj.done;
  checkbox.addEventListener('change', async () => {
    taskObj.done = checkbox.checked;

    await postJson('api/update_todo.php', { todo_id: todoObj.todo_id, todo_data: todoObj.data });
  });
  li.appendChild(checkbox);
  const textSpan = document.createElement('span');
  textSpan.className = 'todo-task-text';
  textSpan.textContent = taskObj.text || '';
  textSpan.style.cursor = 'pointer';
  textSpan.addEventListener('click', (e) => {
    e.stopPropagation();

  window.location.href = `tododetails.php?todo_id=${encodeURIComponent(todoObj.todo_id)}`;
  });
  li.appendChild(textSpan);
  const delbutton = document.createElement('button');
  delbutton.innerHTML = '<i class="fa-solid fa-trash"></i>';
  delbutton.className = 'delete-task-button';
  delbutton.title = 'Delete task';
  delbutton.style.display = 'none';
  delbutton.addEventListener('click', async (e) => {
    e.stopPropagation();
    const idx = todoObj.data.indexOf(taskObj);
    if (idx >= 0) {
      todoObj.data.splice(idx, 1);
      li.remove();

      await postJson('api/update_todo.php', { todo_id: todoObj.todo_id, todo_data: todoObj.data });

      try {
        const card = li.closest('.todo-card');
        if (card) {
          const titleBtn = card.querySelector('.todo-title');
          const newCount = (todoObj.data||[]).length;
          if (titleBtn) titleBtn.textContent = `${todoObj.name || 'To-dos'} — ${newCount} item${newCount===1? '':'s'}`;
        }
      } catch (err) { /* ignore */ }
    }
  });
  li.appendChild(delbutton);
}

function createTodoCard(todoObj = { todo_id: 0, name: 'To-dos', data: [] }, alt = false) {

  const card = document.createElement('div');
  card.className = 'todo-card' + (alt ? ' alt' : '');


  const titleRow = document.createElement('div');
  titleRow.style.display = 'flex';
  titleRow.style.alignItems = 'center';
  titleRow.style.justifyContent = 'space-between';

    const title = document.createElement('button');
  title.className = 'todo-title title-button';
  title.type = 'button';
    const count = (todoObj.data||[]).length;
    title.textContent = `${todoObj.name || 'To-dos'} — ${count} item${count===1? '':'s'}`;
  title.title = 'Open details';
  title.addEventListener('click', () => {
    window.location.href = `tododetails.php?todo_id=${encodeURIComponent(todoObj.todo_id)}`;
  });
  titleRow.appendChild(title);

  const settingsbutton = document.createElement('button');
  settingsbutton.className = 'settings-button';
  settingsbutton.innerHTML = '<i class="fa-solid fa-ellipsis-v"></i>';
  settingsbutton.title = 'Show options';
  settingsbutton.style.background = 'none';
  settingsbutton.style.border = 'none';
  settingsbutton.style.cursor = 'pointer';
  settingsbutton.style.fontSize = '1.2em';
  settingsbutton.style.padding = '0 4px';
  titleRow.appendChild(settingsbutton);

  card.appendChild(titleRow);


  const ul = document.createElement('ul');
  ul.className = 'todo-list';
  card.appendChild(ul);


  const addRow = document.createElement('div');
  addRow.className = 'add-row';
  addRow.style.display = 'flex';
  addRow.style.alignItems = 'center';
  addRow.style.gap = '8px';

  const deleteTodoBtn = document.createElement('button');
  deleteTodoBtn.textContent = 'Delete';
  deleteTodoBtn.title = 'Delete this To-do';
  deleteTodoBtn.style.background = '#c03';
  deleteTodoBtn.style.color = '#fff';
  deleteTodoBtn.style.border = 'none';
  deleteTodoBtn.style.padding = '6px 8px';
  deleteTodoBtn.style.borderRadius = '6px';
  deleteTodoBtn.addEventListener('click', async (ev) => {
    ev.stopPropagation();
    if (!confirm('Delete this To-do for your account? This will remove your mapping and delete it if no other users exist.')) return;
    try {
      const form = new FormData();
      form.append('todo_id', todoObj.todo_id);
      const res = await fetch('api/delete_todo.php', { method: 'POST', body: form });
      const j = await res.json();
      if (j.ok) {
        card.remove();
      } else {
        alert('Delete failed');
      }
    } catch (err) { console.error('Delete error', err); alert('Delete failed'); }
  });


  const addTaskDiv = document.createElement('div');
  addTaskDiv.className = 'add-task';
  addTaskDiv.style.flex = '1';
  const input = document.createElement('input');
  input.type = 'text';
  input.placeholder = 'Add a new task...';
  input.setAttribute('aria-label', 'Add a new task');
  input.style.width = '100%';
  const addbutton = document.createElement('button');
  addbutton.textContent = '+';
  addbutton.style.background = '#ffcc00';
  addbutton.style.border = 'none';
  addbutton.style.padding = '8px 10px';
  addbutton.style.borderRadius = '6px';
  addTaskDiv.appendChild(input);
  addTaskDiv.appendChild(addbutton);
  addRow.appendChild(deleteTodoBtn);
  addRow.appendChild(addTaskDiv);
  card.appendChild(addRow);

 
  const footer = document.createElement('div');
  footer.className = 'todo-footer';
  footer.textContent = todoObj.last_edited || todoObj.created_time || `Edited Today, ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
  card.appendChild(footer);


  (todoObj.data || []).forEach(task => {
    const li = document.createElement('li');
    renderTaskItem(li, todoObj, task);
    ul.appendChild(li);
  });


  addbutton.onclick = async () => {
    const val = input.value.trim();
    if (!val) return;
    const taskObj = { text: val, done: false };
    todoObj.data = todoObj.data || [];
    todoObj.data.push(taskObj);
    const li = document.createElement('li');
    renderTaskItem(li, todoObj, taskObj);
    ul.appendChild(li);
    input.value = '';
    const newCount = ul.querySelectorAll('li').length;
    title.textContent = `${todoObj.name || 'To-dos'} — ${newCount} item${newCount===1? '':'s'}`;

    await postJson('api/update_todo.php', { todo_id: todoObj.todo_id, todo_data: todoObj.data });
  };
  input.addEventListener('keydown', e => { if (e.key === 'Enter') addbutton.click(); });


  settingsbutton.addEventListener('click', (e) => {
    e.stopPropagation();

    const deletebuttons = ul.querySelectorAll('.delete-task-button');
    const anyShown = Array.from(deletebuttons).some(b => b.style.display !== 'none');
    deletebuttons.forEach(button => { button.style.display = anyShown ? 'none' : 'inline-block'; });

 
    let menu = card.querySelector('.todo-settings-menu');
    if (!menu) {
      menu = document.createElement('div');
      menu.className = 'todo-settings-menu';
      menu.style.position = 'absolute';
      menu.style.background = '#fff';
      menu.style.border = '1px solid #ccc';
      menu.style.padding = '6px';
      menu.style.borderRadius = '6px';
      menu.style.right = '8px';
      menu.style.top = '36px';
      menu.style.zIndex = '100';


      card.style.position = card.style.position || 'relative';
      card.appendChild(menu);
    }


    menu.style.display = menu.style.display === 'none' || !menu.style.display ? 'block' : 'none';


    const closeMenu = (ev) => { if (!card.contains(ev.target)) { if (menu) menu.style.display = 'none'; document.removeEventListener('click', closeMenu); } };
    document.addEventListener('click', closeMenu);
  });

  todoBoard.appendChild(card);
}


async function loadTodos() {
  todoBoard.innerHTML = '';
  try {
    const resp = await fetch('api/get_todos.php', { cache: 'no-store' });
    const json = await resp.json();
    if (!json.ok) { console.error('Failed to load todos', json); return; }
    const todos = json.todos || [];
    todos.forEach((t, i) => createTodoCard(t, i % 2 === 1));
  } catch (err) { console.error('Error fetching todos', err); }
}


createbutton.onclick = async () => {
  try {
    const form = new FormData();
    form.append('name', 'New To-dos');
    const res = await fetch('api/create_todo.php', { method: 'POST', body: form });
    const j = await res.json();
    if (j.ok && j.todo_id) {

      await loadTodos();

  window.location.href = `tododetails.php?todo_id=${encodeURIComponent(j.todo_id)}`;
    }
  } catch (err) { console.error('Could not create todo', err); }
};


loadTodos();


window.addEventListener('pageshow', (event) => {

  if (event.persisted) loadTodos();
});







const hamburger = document.getElementById("hamburger");
const dropdown = document.getElementById("dropdown");


hamburger.addEventListener("click", () => {
  dropdown.classList.toggle("show");
});


window.addEventListener("click", (e) => {
  if (!hamburger.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.classList.remove("show");
  }
});


window.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && dropdown.classList.contains("show")) {
    dropdown.classList.remove("show");
  }
});




