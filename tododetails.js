
(function () {
  const root = document.getElementById('detail-root');
  const backBtn = document.getElementById('back-button');
  if (!root) return;

 
  const params = new URLSearchParams(window.location.search);
  const todoId = params.get('todo_id');
  if (!todoId) {
    root.innerHTML = '<p>No todo selected. Use the board to open a todo.</p>';
    return;
  }

  async function fetchTodo(id) {
    try {
      const res = await fetch(`api/get_todo.php?todo_id=${encodeURIComponent(id)}`);
      const j = await res.json();
      if (!j.ok || !j.todo) {
        root.innerHTML = '<p>Could not load todo.</p>';
        return null;
      }
      return j.todo;
    } catch (err) {
      console.error('Failed to fetch todo', err);
      root.innerHTML = '<p>Error loading todo.</p>';
      return null;
    }
  }

  async function render() {
    const data = await fetchTodo(todoId);
    if (!data) return;
    root.innerHTML = '';

  const nameInput = document.createElement('input');
  nameInput.type = 'text';
  nameInput.value = data.name || 'To-do Details';
  nameInput.style.fontSize = '1.5em';
  nameInput.style.padding = '8px 12px';
  nameInput.style.borderRadius = '6px';
  nameInput.style.border = '1px solid #ddd';
  nameInput.style.width = '60%';
  nameInput.style.display = 'block';
  nameInput.style.marginBottom = '12px';
  root.appendChild(nameInput);

    const meta = document.createElement('div');
    meta.style.marginBottom = '12px';
    meta.textContent = data.last_edited || '';
    root.appendChild(meta);


  const controls = document.createElement('div');
    controls.style.margin = '12px 0';
    const input = document.createElement('input');
    input.type = 'text';
    input.placeholder = 'New task description';
    input.style.marginRight = '8px';
    const addBtn = document.createElement('button');
    addBtn.textContent = 'Add Task';
  addBtn.style.marginRight = '8px';
  addBtn.style.background = '#ffcc00';
  addBtn.style.border = 'none';
  addBtn.style.padding = '8px 10px';
  addBtn.style.borderRadius = '6px';
    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Save';
  saveBtn.style.marginRight = '8px';
  saveBtn.style.background = '#2baf2b';
  saveBtn.style.color = '#fff';
  saveBtn.style.border = 'none';
  saveBtn.style.padding = '8px 10px';
  saveBtn.style.borderRadius = '6px';
    const deleteBtn = document.createElement('button');
    deleteBtn.textContent = 'Delete To-do';
  deleteBtn.style.background = '#c03';
  deleteBtn.style.color = '#fff';
  deleteBtn.style.border = 'none';
  deleteBtn.style.padding = '8px 10px';
  deleteBtn.style.borderRadius = '6px';
    controls.appendChild(input);
    controls.appendChild(addBtn);
    controls.appendChild(saveBtn);
    controls.appendChild(deleteBtn);
    root.appendChild(controls);

    const ul = document.createElement('ul');
    ul.style.listStyle = 'none';
    ul.style.padding = '0';

    function buildList() {
      ul.innerHTML = '';
      (data.data || []).forEach((t, idx) => {
        const li = document.createElement('li');
        li.style.display = 'flex';
        li.style.alignItems = 'center';
        li.style.gap = '10px';
        li.style.marginBottom = '8px';

        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.checked = !!t.done;
        cb.addEventListener('change', () => { t.done = cb.checked; });
        li.appendChild(cb);

        const span = document.createElement('span');
        span.textContent = t.text;
        li.appendChild(span);

        const del = document.createElement('button');
        del.textContent = 'Remove';
        del.addEventListener('click', () => {
          data.data.splice(idx, 1);
          buildList();
        });
        li.appendChild(del);

        ul.appendChild(li);
      });
    }

    buildList();
    root.appendChild(ul);

    addBtn.addEventListener('click', () => {
      const v = input.value.trim();
      if (!v) return;
      data.data = data.data || [];
      data.data.push({ text: v, done: false });
      input.value = '';
      buildList();
    });

    saveBtn.addEventListener('click', async () => {
      try {
        const payload = { todo_id: data.todo_id, todo_data: data.data, todo_name: (nameInput.value||'').trim() };
        await fetch('api/update_todo.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });

        const refreshed = await fetchTodo(todoId);
        if (refreshed) {
          data.last_edited = refreshed.last_edited;
          data.name = refreshed.name || data.name;
          meta.textContent = data.last_edited || '';
          if (nameInput) nameInput.value = data.name;
        }
        alert('Saved');
      } catch (err) { console.error('Could not save todo', err); alert('Save failed'); }
    });

    deleteBtn.addEventListener('click', async () => {
      if (!confirm('Delete this To-do for your account? This will remove your mapping and delete it if no other users exist.')) return;
      try {
        const form = new FormData();
        form.append('todo_id', data.todo_id);
        const res = await fetch('api/delete_todo.php', { method: 'POST', body: form });
        const j = await res.json();
        if (j.ok) {
          window.location.href = 'todo.php';
        } else {
          alert('Delete failed');
        }
      } catch (err) { console.error('Delete error', err); alert('Delete failed'); }
    });
  }

  render();

  if (backBtn) {
    backBtn.addEventListener('click', () => {
      window.location.href = 'todo.php';
    });
  }
})();
