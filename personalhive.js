document.addEventListener('DOMContentLoaded', () => {


  const dataEl = document.getElementById('project-data');
  let projectObj = null;
  if (dataEl) {
    try { projectObj = JSON.parse(dataEl.textContent); } catch(e){ projectObj = null; }
  }
  const project = projectObj ? projectObj.project : null;
  console.log('personalhive: parsed projectObj=', projectObj);

  const mainEl = document.querySelector('main.hive-content');
  const domProjectId = mainEl ? mainEl.dataset.projectId : null;
  let projectIdFromDom = domProjectId ? parseInt(domProjectId) : null;

  const projectSafe = project || (projectIdFromDom ? {
    project_id: projectIdFromDom,
    id: projectIdFromDom,
    name: (document.querySelector('.hive-project-name') ? document.querySelector('.hive-project-name').textContent.replace(/^Project:\s*/i,'') : 'Project'),
    description: (document.querySelector('.hive-project-desc') ? document.querySelector('.hive-project-desc').textContent.trim() : ''),
    dueDate: (function(){ try{ const el = document.querySelector('.hive-dates div b'); return el ? el.textContent.trim() : null; } catch(e){ return null; } })()
  } : null);
  if (!project && projectObj === null) console.warn('personalhive: server JSON missing or invalid, using DOM fallback', projectSafe);
  const tasksFromServer = projectObj ? (projectObj.tasks || []) : [];
  if (project) {

    project.tasks = tasksFromServer.map(t => ({
      id: t.task_id,
      name: t.title,
      description: t.description,

        status: (function(st){
          st = String(st || '').toLowerCase();
          if (st === 'done' || st === 'completed') return 'Completed';
          if (st === 'in_progress' || st === 'in progress') return 'In Progress';
          if (st === 'todo' || st === 'blocked' || st === 'uninitiated' || st === '') return 'Uninitiated';

          return st.charAt(0).toUpperCase() + st.slice(1);
        })(t.status),
      priority: t.priority ? (t.priority.charAt(0).toUpperCase() + t.priority.slice(1)) : 'Medium',
      timeSpent: (function(ca){ try { return formatTimeSince(ca); } catch(e){ return '0h 0min'; } })(t.created_at),
      deadline: t.due_date || '-'
    }));
  }


  function formatTimeSince(createdAt) {
    if (!createdAt) return '0h 0min';

    let d;
    try {
      if (typeof createdAt === 'string' && createdAt.indexOf(' ') > 0) d = new Date(createdAt.replace(' ', 'T'));
      else d = new Date(createdAt);
      if (isNaN(d)) return '0h 0min';
    } catch (e) { return '0h 0min'; }
    const now = new Date();
    let diff = Math.max(0, Math.floor((now - d) / 1000)); 
    const days = Math.floor(diff / 86400); diff -= days * 86400;
    const hours = Math.floor(diff / 3600); diff -= hours * 3600;
    const mins = Math.floor(diff / 60);
    if (days > 0) return `${days}d ${hours}h`;
    if (hours > 0) return `${hours}h ${mins}m`;
    return `${mins}m`;
  }


  if (projectSafe) {
    const profileImg = document.querySelector('.hive-avatar');
    if (profileImg && projectSafe.image) {
  
      if (profileImg.tagName && profileImg.tagName.toLowerCase() === 'img') profileImg.src = projectSafe.image;
      else profileImg.style.backgroundImage = `url(${projectSafe.image})`;
      try { profileImg.style.backgroundSize = 'cover'; profileImg.style.backgroundPosition = 'center'; } catch(e){}
    }
    const projectName = document.querySelector('.hive-project-name');
    if (projectName) projectName.textContent = `Project: ${projectSafe.name}`;
  
    let descDiv = document.querySelector('.hive-project-desc');
    if (!descDiv) {
      descDiv = document.createElement('div');
      descDiv.className = 'hive-project-desc';
      if (projectName && projectName.parentNode) {
        projectName.parentNode.insertBefore(descDiv, projectName.nextSibling);
      }
    }
      
      descDiv.textContent = projectSafe.description || '';
     
      try {
        if (projectName && projectName.parentNode) projectName.insertAdjacentElement('afterend', descDiv);
        else if (projectName && projectName.parentNode) projectName.parentNode.appendChild(descDiv);
      } catch (e) {
       
        const profileInfo = document.querySelector('.hive-profile-info');
        if (profileInfo) profileInfo.appendChild(descDiv);
      }
    
  const dateDiv = document.querySelector('.hive-date');
  if (dateDiv) {
    let createdVal = projectSafe.dateCreated || null;
    if (!createdVal) {
      
      const txt = dateDiv.textContent || '';
      const m = txt.match(/Date Created:\s*(\d{1,2}\/\d{1,2}\/\d{4})/i);
      if (m && m[1]) {
        const parts = m[1].split('/');
        if (parts.length === 3) {
          
          createdVal = `${parts[2]}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}`;
        }
      }
    }
    dateDiv.textContent = `Date Created: ${formatDate(createdVal)}`;
  }
  
    const dueDateDiv = document.querySelector('.hive-dates div b');
    
    if (dueDateDiv) {
      if (projectSafe.dueDate) {
        dueDateDiv.textContent = formatDate(projectSafe.dueDate);
      } 
    }
  }

  
  function formatDate(date) {
    if (!date) return '-';
    try {
      
      if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(String(date))) {
        const parts = String(date).split('/'); 
        const iso = `${parts[2]}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}`;
        const d = new Date(iso);
        if (!isNaN(d)) return d.toLocaleDateString('en-GB');
      }
      const d2 = new Date(date);
      if (!isNaN(d2)) return d2.toLocaleDateString('en-GB');
    } catch(e) {}
    return '-';
  }


  function renderHiveTasks() {

    const renderProject = project || projectSafe;
    if (!renderProject) return;
    const allTasks = Array.isArray(renderProject.tasks) ? renderProject.tasks : [];

    const inProgress = allTasks.filter(t => t.status === 'In Progress');
    const completed = allTasks.filter(t => t.status === 'Completed');
    const uninitiated = allTasks.filter(t => t.status === 'Uninitiated');


    const totalTasks = Array.isArray(renderProject.tasks) ? renderProject.tasks.length : 0;
    const completedTasks = completed.length;
    let percent = 0;
    if (totalTasks > 0) {
      percent = Math.round((completedTasks / totalTasks) * 100);
    }

    const progressBar = document.querySelector('.hive-progress');
    if (progressBar) {
      progressBar.style.width = percent + '%';
    }
    const completedLabel = document.querySelector('.hive-progress-completed');
    if (completedLabel) {
      completedLabel.textContent = percent + '% completed';
    }
    
    const stats = document.querySelector('.hive-stats');
    if (stats) {
      stats.innerHTML = `
        <span>Tasks: <b>${totalTasks}</b></span>
        <span>Completed: <b>${completedTasks}</b></span>
        <span>In-progress: <b>${inProgress.length}</b></span>
      `;
    }

    renderTaskSection(inProgress, '.hive-section:nth-of-type(1) .hive-tasks-row');
    renderTaskSection(completed, '.hive-section:nth-of-type(2) .hive-tasks-row');
    renderTaskSection(uninitiated, '.hive-section:nth-of-type(3) .hive-tasks-row');
  }

  function renderTaskSection(tasks, selector) {
    const row = document.querySelector(selector);
    if (!row) return;
    row.innerHTML = '';
    tasks.forEach(task => {
      const cardDiv = document.createElement('div');
      let cardClass = 'hive-task-card';
      if (task.status === 'Completed') cardClass += ' yellow';
      else if (task.status === 'In Progress') cardClass += ' yellowpale';
      else if (task.status === 'Uninitiated') cardClass += ' black';
      cardDiv.className = cardClass;
  
      const descColor = (task.status === 'In Progress' || task.status === 'Completed') ? '#000' : '#fff';
      cardDiv.innerHTML = `
        <div class="hive-task-title">${task.name}</div>
        <div class="hive-task-desc" style="font-size:13px;color:${descColor};font-weight:600;margin-top:6px;">${task.description ? task.description : ''}</div>
        <div class="hive-task-meta">Priority: <select class="priority-select">
          <option value="High" ${task.priority === 'High' ? 'selected' : ''}>High</option>
          <option value="Medium" ${task.priority === 'Medium' ? 'selected' : ''}>Medium</option>
          <option value="Low" ${task.priority === 'Low' ? 'selected' : ''}>Low</option>
        </select></div>
        <div class="hive-task-meta">Time Spent: <b>${task.timeSpent}</b></div>
        <div class="hive-task-meta">Deadline: <input type="text" class="deadline-input" value="${task.deadline}" style="width:70px;"></div>
        <div class="hive-task-meta">Status: <select class="status-select">
          <option value="In Progress" ${task.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
          <option value="Uninitiated" ${task.status === 'Uninitiated' ? 'selected' : ''}>Uninitiated</option>
          <option value="Completed" ${task.status === 'Completed' ? 'selected' : ''}>Completed</option>
        </select></div>
        <div style="margin-top:8px; display:flex; gap:8px; justify-content:flex-end;">
          <button class="task-edit-btn" style="background:#fff; border:1px solid #ccc; padding:6px 10px; border-radius:6px; cursor:pointer;">Edit</button>
          <button class="task-delete-btn" style="background:#ff6b6b; color:#fff; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;">Delete</button>
        </div>
      `;
     
      const deadlineInput = cardDiv.querySelector('.deadline-input');
      const statusSelect = cardDiv.querySelector('.status-select');
      const prioritySelect = cardDiv.querySelector('.priority-select');
      deadlineInput.addEventListener('change', (e) => {
        updateTask(task.id, { due_date: e.target.value });
      });
      statusSelect.addEventListener('change', (e) => {
        updateTask(task.id, { status: mapStatusToBackend(e.target.value) }).then(() => renderHiveTasks());
      });
      prioritySelect.addEventListener('change', (e) => {
        updateTask(task.id, { priority: e.target.value.toLowerCase() });
      });
    
      const editBtn = cardDiv.querySelector('.task-edit-btn');
      const deleteBtn = cardDiv.querySelector('.task-delete-btn');
      if (editBtn) {
        editBtn.addEventListener('click', (ev) => {
          ev.preventDefault();
         
          let modal = document.getElementById('addTaskModal');
          if (!modal) { 
            const addBtn = document.querySelector('.hive-btn.add');
            if (addBtn) addBtn.click();
            modal = document.getElementById('addTaskModal');
            if (!modal) return;
          }
         
          const titleEl = modal.querySelector('#addTaskTitle');
          const descEl = modal.querySelector('#addTaskDesc');
          const dueEl = modal.querySelector('#addTaskDue');
          const prEl = modal.querySelector('#addTaskPriority');
          const stEl = modal.querySelector('#addTaskStatus');
          const tidEl = modal.querySelector('#addTaskTaskId');
          if (titleEl) titleEl.value = task.name || '';
          if (descEl) descEl.value = task.description || '';
          if (dueEl) {
            try { dueEl.value = task.deadline && task.deadline !== '-' ? task.deadline : ''; } catch(e){ dueEl.value = ''; }
          }
          if (prEl) prEl.value = (task.priority || 'Medium').toLowerCase();
          if (stEl) {
            const stv = task.status === 'Completed' ? 'done' : (task.status === 'In Progress' ? 'in_progress' : 'todo');
            
            if (stv === 'done') stEl.value = 'done';
            else if (stv === 'in_progress') stEl.value = 'in_progress';
            else stEl.value = 'todo';
          }
          if (tidEl) tidEl.value = task.id;
          modal.style.display = 'flex';
        });
      }
      if (deleteBtn) {
        deleteBtn.addEventListener('click', async (ev) => {
          ev.preventDefault();
          if (!confirm('Delete this task?')) return;
          const pid = (project && (project.project_id || project.id)) || (projectSafe && (projectSafe.project_id || projectSafe.id)) || (document.querySelector('main.hive-content') ? (document.querySelector('main.hive-content').dataset.projectId || document.querySelector('main.hive-content').dataset.project_id) : null);
          if (!pid) { alert('No project id - cannot delete'); return; }
          const fd = new FormData(); fd.append('task_id', task.id); fd.append('project_id', pid);
          try {
            const res = await fetch('api/delete_task.php', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Delete failed');
            const json = await res.json().catch(()=>null);
            if (json && json.ok) {
              
              try {
                const arr = (project && Array.isArray(project.tasks)) ? project.tasks : (projectSafe && Array.isArray(projectSafe.tasks) ? projectSafe.tasks : null);
                if (arr) {
                  const idx = arr.findIndex(t => String(t.id) === String(task.id));
                  if (idx !== -1) arr.splice(idx,1);
                }
                if (projectObj && projectObj.project && Array.isArray(projectObj.tasks)) {
                  const idx2 = projectObj.tasks.findIndex(t => String(t.task_id) === String(task.id)); if (idx2 !== -1) projectObj.tasks.splice(idx2,1);
                }
              } catch(e){ console.warn('personalhive: failed to remove deleted task from memory', e); }
              try { renderHiveTasks(); } catch(e){}
            } else {
              alert('Failed to delete task');
            }
          } catch(err) { console.error(err); alert('Error deleting task'); }
        });
      }
      row.appendChild(cardDiv);
    });
  }

  function mapStatusToBackend(status) {
    if (status === 'Completed') return 'done';
    if (status === 'In Progress') return 'in_progress';
    if (status === 'Uninitiated') return 'todo';
    return status;
  }

  async function updateTask(taskId, updates) {
    
    try {
      const body = new FormData();
      body.append('task_id', taskId);
      for (const k in updates) body.append(k, updates[k]);
      const res = await fetch('api/update_task.php', { method: 'POST', body });
      if (!res.ok) throw new Error('Failed');
      
      let json = null;
      try { json = await res.json(); } catch (e) { json = null; }
     
      const memArr = (project && Array.isArray(project.tasks)) ? project.tasks : (projectSafe && Array.isArray(projectSafe.tasks) ? projectSafe.tasks : null);
      if (memArr) {
        const memIdx = memArr.findIndex(t => String(t.id) === String(taskId));
        if (memIdx !== -1) {
          const current = memArr[memIdx];
          const updated = { ...current };
         
          if (updates.status !== undefined) {
            const s = String(updates.status || '').toLowerCase();
            if (s === 'done' || s === 'completed') updated.status = 'Completed';
            else if (s === 'in_progress' || s === 'in progress') updated.status = 'In Progress';
            else if (s === 'todo' || s === 'uninitiated') updated.status = 'Uninitiated';
            else updated.status = updates.status;
          }
        
          if (updates.priority !== undefined) {
            const p = String(updates.priority || '').toLowerCase();
            updated.priority = p ? (p.charAt(0).toUpperCase() + p.slice(1)) : updated.priority;
          }
          
          if (updates.due_date !== undefined) {
            updated.deadline = updates.due_date || '-';
          }
          memArr[memIdx] = updated;
        }
      }
      
      if (projectObj && projectObj.project && Array.isArray(projectObj.tasks)) {
        const srvIdx = projectObj.tasks.findIndex(t => String(t.task_id) === String(taskId));
        if (srvIdx !== -1) {
          if (!projectObj.tasks[srvIdx]) projectObj.tasks[srvIdx] = {};
          if (updates.status !== undefined) projectObj.tasks[srvIdx].status = updates.status;
          if (updates.priority !== undefined) projectObj.tasks[srvIdx].priority = updates.priority;
          if (updates.due_date !== undefined) projectObj.tasks[srvIdx].due_date = updates.due_date;
        }
      }
      try { renderHiveTasks(); } catch(e){}
      return true;
    } catch (e) { console.error(e); return false; }
  }


 
  const addTaskBtn = document.querySelector('.hive-btn.add');
 
  if (addTaskBtn) {
    addTaskBtn.addEventListener('click', (e) => {
      e.preventDefault();
      
      let modal = document.getElementById('addTaskModal');
      if (!modal) {
        modal = document.createElement('div');
        modal.id = 'addTaskModal';
        modal.className = 'hive-modal';
        modal.style.display = 'none';
        modal.innerHTML = `
          <div class="hive-modal-backdrop"></div>
          <div class="hive-modal-content">
            <h2 style="margin:0 0 12px; text-align:center;">Add New Task</h2>
            <form id="ph_addTaskForm">
              <input type="hidden" id="addTaskTaskId" value="">
              <label style="display:block; font-weight:600; margin-bottom:6px;">Title</label>
              <input type="text" id="addTaskTitle" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; margin-bottom:10px;" required>
              <label style="display:block; font-weight:600; margin-bottom:6px;">Description</label>
              <textarea id="addTaskDesc" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; height:90px; margin-bottom:10px;"></textarea>
              <div style="display:flex; gap:12px; align-items:center; margin-bottom:10px;">
                <div style="flex:1;">
                  <label style="display:block; font-weight:600; margin-bottom:6px;">Due Date</label>
                  <input type="date" id="addTaskDue" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ddd;">
                </div>
                <div style="width:140px;">
                  <label style="display:block; font-weight:600; margin-bottom:6px;">Priority</label>
                  <select id="addTaskPriority" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ddd;">
                    <option value="high">High</option>
                    <option value="medium" selected>Medium</option>
                    <option value="low">Low</option>
                  </select>
                </div>
              </div>
              <div style="margin-bottom:10px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Status</label>
                <select id="addTaskStatus" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ddd;">
                  <option value="todo">Uninitiated</option>
                  <option value="in_progress">In Progress</option>
                  <option value="done">Completed</option>
                </select>
              </div>
              <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" id="addTaskCancelBtn" style="background:#fff; border:1px solid #ccc; padding:8px 14px; border-radius:8px; cursor:pointer;">Cancel</button>
                <button type="submit" id="addTaskSaveBtn" style="background:#ffe100; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:700;">Add Task</button>
              </div>
            </form>
          </div>
        `;
        document.body.appendChild(modal);

        
        modal.querySelector('#addTaskCancelBtn').addEventListener('click', () => { modal.style.display = 'none'; });
        const form = modal.querySelector('#ph_addTaskForm');
        form.addEventListener('submit', async (ev) => {
          ev.preventDefault();
          const title = (modal.querySelector('#addTaskTitle') || {}).value || '';
          if (!title.trim()) { alert('Please enter a title'); return; }
          const description = (modal.querySelector('#addTaskDesc') || {}).value || '';
          const due_date = (modal.querySelector('#addTaskDue') || {}).value || '';
          const priority = (modal.querySelector('#addTaskPriority') || {}).value || 'medium';
          const status = (modal.querySelector('#addTaskStatus') || {}).value || 'todo';
          const fd = new FormData();
          fd.append('title', title.trim());
          fd.append('description', description);
          if (due_date) fd.append('due_date', due_date);
          if (priority) fd.append('priority', priority);
          if (status) fd.append('status', status);
          const pid = (project && (project.project_id || project.id)) || (projectSafe && (projectSafe.project_id || projectSafe.id)) || (document.querySelector('main.hive-content') ? (document.querySelector('main.hive-content').dataset.projectId || document.querySelector('main.hive-content').dataset.project_id) : null);
          if (!pid) {
            
            if (confirm('No project selected. Create a Personal HIVE now?')) {
             
              try { const openEvt = new Event('personal.openProjectModal'); } catch(e){}
              
              const pm = document.getElementById('projectModal');
              if (pm) pm.style.display = 'flex';
              else window.location.href = 'personal.php';
              return;
            }
            return;
          }
          fd.append('project_id', pid);
          
          const existingTaskId = (modal.querySelector('#addTaskTaskId') || {}).value || null;
          if (existingTaskId) fd.append('task_id', existingTaskId);
          try {
            const res = await fetch('api/save_task.php', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            
            let text = '';
            try { text = await res.text(); } catch(e){ text = ''; }
            let json = null;
            try { json = text ? JSON.parse(text) : null; } catch(e){ json = null; }
            
            if (res.ok && ((json && (json.ok || json.success)) || String(text).trim() === 'OK')) {
              const returnedId = (json && json.task_id) ? json.task_id : (existingTaskId || ('t_' + Date.now()));
              const uiStatus = (status === 'done' ? 'Completed' : (status === 'in_progress' ? 'In Progress' : 'Uninitiated'));
              const uiPriority = priority ? (priority.charAt(0).toUpperCase() + priority.slice(1)) : 'Medium';
              const uiDeadline = due_date || '-';
              
              const serverLike = { task_id: returnedId, title: title.trim(), description: description, status: (status || 'todo'), priority: priority || 'medium', due_date: due_date || null };
              const uiLike = { id: returnedId, name: title.trim(), description: description, status: uiStatus, priority: uiPriority, timeSpent: formatTimeSince(new Date().toISOString()), deadline: uiDeadline };
              try {
                
                if (existingTaskId) {
                  
                  if (projectObj && projectObj.project && Array.isArray(projectObj.tasks)) {
                    const idx = projectObj.tasks.findIndex(t => String(t.task_id) === String(existingTaskId));
                    if (idx !== -1) projectObj.tasks[idx] = serverLike;
                    else projectObj.tasks.push(serverLike);
                  }
                  
                  const uiArr = (project && Array.isArray(project.tasks)) ? project.tasks : (projectSafe && Array.isArray(projectSafe.tasks) ? projectSafe.tasks : null);
                  if (uiArr) {
                    const uiIdx = uiArr.findIndex(t => String(t.id) === String(existingTaskId));
                    if (uiIdx !== -1) uiArr[uiIdx] = uiLike; else uiArr.push(uiLike);
                  }
                } else {
                  if (projectObj && projectObj.project && Array.isArray(projectObj.tasks)) projectObj.tasks.push(serverLike);
                  
                  if (project && Array.isArray(project.tasks)) {
                    project.tasks.push(uiLike);
                  } else if (projectSafe) {
                    if (!Array.isArray(projectSafe.tasks)) projectSafe.tasks = [];
                    projectSafe.tasks.push(uiLike);
                  }
                }
              } catch(e){ console.warn('personalhive: failed to append/update new task to in-memory arrays', e); }
              
              try { renderHiveTasks(); } catch(e){}
              
              const tidEl = modal.querySelector('#addTaskTaskId'); if (tidEl) tidEl.value = '';
              modal.style.display = 'none';
            } else {
              const txt = json && json.error ? json.error : (await res.text().catch(()=>''));
              alert('Failed to save task: ' + (txt || res.statusText));
            }
          } catch(err) { console.error('Failed to save task', err); alert('Error saving task'); }
        });
      }
      modal.style.display = 'flex';
    });
  }

 
  const deleteBtn = document.querySelector('.hive-btn.delete');
  if (deleteBtn && projectSafe) {
    deleteBtn.addEventListener('click', async () => {
      if (!confirm('Are you sure you want to delete this project?')) return;
      const body = new FormData();
  body.append('project_id', projectSafe.project_id || projectSafe.id);
  const res = await fetch('api/delete_project.php', { method: 'POST', body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (res.ok) {
        window.location.href = 'personal.php';
      } else {
        alert('Failed to delete project');
      }
    });
  }

  
  renderHiveTasks();

 
  const editBtn = document.querySelector('.hive-btn.edit');

  function openEditProjectModal() {
    const mainEl = document.querySelector('main.hive-content');
    const domProjId = mainEl ? (mainEl.dataset.projectId || mainEl.dataset.project_id) : null;
    
    function readCurrentProjectFromDOM() {
      const pn = document.querySelector('.hive-project-name');
      const pd = document.querySelector('.hive-project-desc');
      const avatar = document.querySelector('.hive-avatar');
      const dueB = document.querySelector('.hive-dates div b');
      const dateDiv = document.querySelector('.hive-date');
      const name = pn ? pn.textContent.replace(/^Project:\s*/i,'').trim() : '';
      const description = pd ? pd.textContent.trim() : '';
      const image = avatar ? avatar.src : null;
      const dueText = dueB ? dueB.textContent.trim() : null;
      const createdText = dateDiv ? (dateDiv.textContent.replace(/^Date Created:\s*/i,'').trim() || null) : null;
      return {
        project_id: domProjId || (project && (project.project_id || project.id)) || null,
        id: domProjId || (project && (project.project_id || project.id)) || null,
        name: (project && project.name) ? project.name : name,
        description: (project && project.description) ? project.description : description,
        image: (project && project.image) ? project.image : image,
        dueDate: (project && (project.dueDate || project.due_date)) ? (project.dueDate || project.due_date) : (dueText || null),
        dateCreated: (project && project.dateCreated) ? project.dateCreated : createdText,
        priority: (project && project.priority) ? project.priority : null
      };
    }

    const ps = project || readCurrentProjectFromDOM() || projectSafe || (domProjId ? { project_id: domProjId, id: domProjId, name: document.querySelector('.hive-project-name') ? document.querySelector('.hive-project-name').textContent.replace(/^Project:\s*/i,'') : '' } : null);
    const modal = document.getElementById('editProjectModal');
    if (!modal) { console.warn('personalhive: editProjectModal not found'); return; }

    
    const modalContent = `
      <div class="hive-modal-backdrop" style="position:absolute; top:0; left:0; right:0; bottom:0;"></div>
      <div class="hive-modal-content" style="position:relative; z-index:2; background:#fff; border-radius:18px; box-shadow:0 8px 32px rgba(0,0,0,0.25); width:520px; max-width:96vw; padding:20px;">
        <h2 style="margin:0 0 12px; font-size:1.25rem; text-align:center;">Edit Project</h2>
        <form id="ph_editProjectForm">
          <div style="display:grid; grid-template-columns:1fr 140px; gap:12px; align-items:start;">
            <div>
              <label style="display:block; font-weight:600; margin-bottom:6px;">Project Name</label>
              <input type="text" id="editProjectName" value="${(ps && ps.name) ? escapeHtml(ps.name) : ''}" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; margin-bottom:10px;">
              <label style="display:block; font-weight:600; margin-bottom:6px;">Description</label>
              <textarea id="editProjectDesc" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; height:100px;">${(ps && ps.description) ? escapeHtml(ps.description) : ''}</textarea>
              <div style="display:flex; gap:8px; margin-top:10px; align-items:center;">
                <div style="flex:1">
                  <label style="display:block; font-weight:600; margin-bottom:6px;">Due Date</label>
                  <input type="date" id="editProjectDue" value="${(ps && ps.dueDate) ? formatForInput(ps.dueDate) : ''}" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ddd;">
                </div>
                <div style="width:120px">
                  <label style="display:block; font-weight:600; margin-bottom:6px;">Priority</label>
                  <select id="editProjectPriority" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ddd;">
                    <option value="high" ${(ps && ps.priority === 'high') ? 'selected' : ''}>High</option>
                    <option value="medium" ${(ps && (ps.priority === 'medium' || !ps.priority)) ? 'selected' : ''}>Medium</option>
                    <option value="low" ${(ps && ps.priority === 'low') ? 'selected' : ''}>Low</option>
                  </select>
                </div>
              </div>
            </div>
            <div style="text-align:center;">
              <div style="width:120px; height:120px; margin:0 auto 8px; border-radius:10px; overflow:hidden; background:#f6f6f6; display:flex; align-items:center; justify-content:center;">
                <img src="${(ps && ps.image) ? ps.image : (document.querySelector('.hive-avatar') ? document.querySelector('.hive-avatar').src : 'https://via.placeholder.com/120')}" id="editProjectImagePreview" style="width:100%; height:100%; object-fit:cover;">
              </div>
              <input type="file" id="editProjectImage" accept="image/*" style="display:block; margin:0 auto 8px;">
              <small style="color:#888; display:block; margin-top:8px;">Optional. Max 2MB.</small>
            </div>
          </div>
          <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:8px;">
            <button type="button" id="editProjectCancelBtn" style="background:#fff; border:1px solid #ccc; padding:8px 14px; border-radius:8px; cursor:pointer;">Cancel</button>
            <button type="submit" id="editProjectSaveBtn" style="background:#ffe100; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:700;">Save</button>
          </div>
        </form>
      </div>
    `;

    
    const modalInner = modal.querySelector('.hive-modal-content') || modal;
    modalInner.innerHTML = modalContent;

    
    function formatForInput(v) {
      if (!v) return '';
      
      try {
        if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(String(v))) {
          const parts = String(v).split('/'); return `${parts[2]}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}`;
        }
        
        const d = new Date(v);
        if (isNaN(d)) return '';
        return d.toISOString().slice(0,10);
      } catch(e){ return ''; }
    }
    function escapeHtml(s){ if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    const form = modal.querySelector('#ph_editProjectForm');
    const cancelBtn = modal.querySelector('#editProjectCancelBtn');
    const saveBtn = modal.querySelector('#editProjectSaveBtn');
    const imgInput = modal.querySelector('#editProjectImage');
    const imgPreview = modal.querySelector('#editProjectImagePreview');

    if (cancelBtn) cancelBtn.onclick = () => { try{ modal.style.display = 'none'; } catch(e){} };
    if (imgInput) imgInput.onchange = (e) => { const f = e.target.files && e.target.files[0]; if (f && imgPreview){ const reader = new FileReader(); reader.onload = ev => { imgPreview.src = ev.target.result; }; reader.readAsDataURL(f); } };

    
    if (form) {
      form.onsubmit = async (ev) => {
        ev.preventDefault();
        try {
          saveBtn.disabled = true;
          const fd = new FormData();
          const pid = ps && (ps.project_id || ps.id) ? (ps.project_id || ps.id) : (mainEl ? (mainEl.dataset.projectId || mainEl.dataset.project_id) : null);
          if (pid) fd.append('project_id', pid);
          fd.append('name', (modal.querySelector('#editProjectName') || {value:''}).value.trim());
          fd.append('description', (modal.querySelector('#editProjectDesc') || {value:''}).value);
          const dueVal = (modal.querySelector('#editProjectDue') || {value:''}).value; if (dueVal) fd.append('due_date', dueVal);
          const pr = (modal.querySelector('#editProjectPriority') || {value:''}).value; if (pr) fd.append('priority', pr);
          const fileEl = modal.querySelector('#editProjectImage'); if (fileEl && fileEl.files && fileEl.files[0]) fd.append('image', fileEl.files[0]);

          const res = await fetch('api/save_project.php', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
          let json = null; try { json = await res.json(); } catch(e){ json = null; }
          if (res.ok && json && (json.ok || json.success)) {
            
            const newName = fd.get('name');
            const newDesc = fd.get('description');
            const projectNameEl = document.querySelector('.hive-project-name'); if (projectNameEl) projectNameEl.textContent = `Project: ${newName}`;
            let descEl = document.querySelector('.hive-project-desc'); if (!descEl){ descEl = document.createElement('div'); descEl.className = 'hive-project-desc'; const pn = document.querySelector('.hive-project-name'); if (pn) pn.insertAdjacentElement('afterend', descEl); }
            if (descEl) descEl.textContent = newDesc;
            const dueElB = document.querySelector('.hive-dates div b'); if (dueElB && dueVal) dueElB.textContent = (new Date(dueVal)).toLocaleDateString('en-GB');
            try { renderHiveTasks(); } catch(e){}
            
            try {
              if (!projectObj) projectObj = { project: null, tasks: [] };
              if (!projectObj.project) projectObj.project = {};
              projectObj.project.name = newName;
              projectObj.project.description = newDesc;
              if (dueVal) projectObj.project.dueDate = dueVal;
              if (fd.get('priority')) projectObj.project.priority = fd.get('priority');
              
              if (projectSafe) {
                projectSafe.name = newName;
                projectSafe.description = newDesc;
                if (dueVal) projectSafe.dueDate = dueVal;
                if (fd.get('priority')) projectSafe.priority = fd.get('priority');
              }
              
              try {
                const de = document.getElementById('project-data');
                if (de && projectObj) de.textContent = JSON.stringify(projectObj);
              } catch(e){}
            } catch(e){}
            modal.style.display = 'none';
          } else {
            const txt = json && json.error ? json.error : (await res.text().catch(()=>''));
            console.warn('personalhive: save failed', res.status, txt);
            alert('Failed to save project: ' + (txt || res.statusText));
          }
        } catch(err) { console.error('personalhive: save error', err); alert('Error saving project'); }
        finally { try { saveBtn.disabled = false; } catch(e){} }
      };
    }

    modal.style.display = 'flex';
  }

  if (editBtn) {
    try { editBtn.addEventListener('click', (e) => { e.preventDefault(); console.log('personalhive: editBtn click captured'); openEditProjectModal(); }); } catch(e){}
    
    try { editBtn.onclick = function(e){ e && e.preventDefault(); console.log('personalhive: editBtn property onclick fired'); openEditProjectModal(); }; } catch(e){}
    
    try { editBtn.style.cursor = 'pointer'; } catch(e){}
  }
  
  document.addEventListener('click', (e) => {
    try {
      const btn = e.target.closest && e.target.closest('.hive-btn.edit');
      if (btn) {
        e.preventDefault();
        console.log('personalhive: delegated edit click');
        openEditProjectModal();
      }
    } catch (err) { /* ignore */ }
  }, true);
 
  try { window.openEditProjectModal = openEditProjectModal; } catch(e){}

  try { document.addEventListener('personalhive.openEditModal', () => { try { openEditProjectModal(); } catch(e){} }); } catch(e){}

});


(function ensureClickableEditButton(){
  function replaceOnce(){
    try {
      const orig = document.querySelector('.hive-btn.edit');
      if (!orig) return false;
      
      if (document.getElementById('personalhive-edit-btn-clone')) return true;
      const clone = orig.cloneNode(true);
      clone.id = 'personalhive-edit-btn-clone';
      clone.style.cursor = 'pointer';
      clone.addEventListener('click', (e) => {
        e.preventDefault();
        console.log('personalhive: clone edit clicked');
        try {
          
          const opener = window.openEditProjectModal;
          if (typeof opener === 'function') { opener(); return; }
        } catch(e){}
        
        try {
          const modal = document.getElementById('editProjectModal');
          if (!modal) return;
          
          let ps = null;
          try {
            const dataEl = document.getElementById('project-data');
            if (dataEl) {
              const parsed = JSON.parse(dataEl.textContent || dataEl.innerText || '{}');
              if (parsed && parsed.project) ps = parsed.project;
            }
          } catch (e) { ps = null; }
          
          const pn = document.querySelector('.hive-project-name');
          const pd = document.querySelector('.hive-project-desc');
          const avatar = document.querySelector('.hive-avatar');
          const nameField = document.getElementById('editProjectName');
          const descField = document.getElementById('editProjectDesc');
          const imgPreview = document.getElementById('editProjectImagePreview');
          const nameVal = ps && (ps.name || ps.name === '') ? ps.name : (pn ? pn.textContent.replace(/^Project:\s*/i,'').trim() : '');
          const descVal = ps && (ps.description || ps.description === '') ? ps.description : (pd ? pd.textContent.trim() : '');
          const imgVal = ps && ps.image ? ps.image : (avatar && avatar.src ? avatar.src : null);
          if (nameField) nameField.value = nameVal;
          if (descField) descField.value = descVal;
          if (imgPreview) {
            if (imgVal) { imgPreview.src = imgVal; imgPreview.style.display = 'block'; } else imgPreview.style.display = 'none';
          }
          
          let dueInput = document.getElementById('editProjectDue');
          if (!dueInput) {
            dueInput = document.createElement('input'); dueInput.type='date'; dueInput.id='editProjectDue'; dueInput.style.width='100%'; dueInput.style.padding='8px';
            if (descField && descField.parentNode) { const wrapper=document.createElement('div'); wrapper.style.marginTop='8px'; const lbl=document.createElement('label'); lbl.textContent='Due Date'; wrapper.appendChild(lbl); wrapper.appendChild(dueInput); descField.parentNode.insertBefore(wrapper, descField.nextSibling); }
          }
          let prioritySel = document.getElementById('editProjectPriority');
          if (!prioritySel) { prioritySel = document.createElement('select'); prioritySel.id='editProjectPriority'; prioritySel.innerHTML='<option value="high">High</option><option value="medium">Medium</option><option value="low">Low</option>'; if (descField && descField.parentNode) { const wrap=document.createElement('div'); wrap.style.marginTop='8px'; const lbl=document.createElement('label'); lbl.textContent='Priority'; wrap.appendChild(lbl); wrap.appendChild(prioritySel); descField.parentNode.insertBefore(wrap, descField.nextSibling); } }
          
          try {
            
            const rawDue = ps && (ps.dueDate || ps.due_date) ? (ps.dueDate || ps.due_date) : '';
            if (rawDue && /^\d{1,2}\/\d{1,2}\/\d{4}$/.test(String(rawDue))) {
              const parts = String(rawDue).split('/');
              dueInput.value = `${parts[2]}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}`;
            } else {
              
              try { const d = new Date(rawDue); dueInput.value = isNaN(d) ? '' : d.toISOString().slice(0,10); } catch(e) { dueInput.value = rawDue || ''; }
            }
          } catch(e){ dueInput.value=''; }
          try { prioritySel.value = ps && ps.priority ? ps.priority.toLowerCase() : 'medium'; } catch(e){ prioritySel.value='medium'; }
          
          try {
            const cancelBtn = document.getElementById('editProjectCancelBtn');
            if (cancelBtn) cancelBtn.onclick = () => { try { modal.style.display = 'none'; } catch(e){} };
            const imageInput = document.getElementById('editProjectImage');
            if (imageInput) imageInput.onchange = (e) => {
              const f = e.target.files && e.target.files[0] ? e.target.files[0] : null;
              if (f && imgPreview) {
                const reader = new FileReader();
                reader.onload = ev => { imgPreview.src = ev.target.result; imgPreview.style.display = 'block'; };
                reader.readAsDataURL(f);
              }
            };
            const saveBtnLocal = document.getElementById('editProjectSaveBtn');
            if (saveBtnLocal) saveBtnLocal.onclick = async () => {
              const newName = (document.getElementById('editProjectName') || {}).value || '';
              const newDesc = (document.getElementById('editProjectDesc') || {}).value || '';
              const body = new FormData();
              
              const mainElLocal = document.querySelector('main.hive-content');
              const derivedPid = (ps && (ps.project_id || ps.id)) ? (ps.project_id || ps.id) : (mainElLocal ? (mainElLocal.dataset.projectId || mainElLocal.dataset.project_id) : '') ;
              if (derivedPid) body.append('project_id', derivedPid);
              body.append('name', newName.trim());
              body.append('description', newDesc);
              const dueEl = document.getElementById('editProjectDue'); if (dueEl && dueEl.value) body.append('due_date', dueEl.value);
              const prEl = document.getElementById('editProjectPriority'); if (prEl && prEl.value) body.append('priority', prEl.value.toLowerCase());
              const fileInput = document.getElementById('editProjectImage'); if (fileInput && fileInput.files && fileInput.files[0]) body.append('image', fileInput.files[0]);
              try {
                const res = await fetch('api/save_project.php', { method: 'POST', body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                let json = null; try { json = await res.json(); } catch(e){ json = null; }
                if (res.ok && json && json.ok) {
                  try { modal.style.display = 'none'; } catch(e){}
                  const projectNameEl = document.querySelector('.hive-project-name'); if (projectNameEl) projectNameEl.textContent = `Project: ${newName}`;
                  const descEl = document.querySelector('.hive-project-desc'); if (descEl) descEl.textContent = newDesc;
                  const dueDateEl = document.querySelector('.hive-dates div b'); if (dueDateEl && dueEl && dueEl.value) dueDateEl.textContent = (new Date(dueEl.value)).toLocaleDateString('en-GB');
                  
                  try {
                    if (ps) { ps.name = newName; ps.description = newDesc; if (dueEl && dueEl.value) ps.dueDate = dueEl.value; if (prEl && prEl.value) ps.priority = prEl.value.toLowerCase(); }
                    if (!projectObj) projectObj = { project: null, tasks: [] };
                    if (!projectObj.project) projectObj.project = {};
                    projectObj.project.name = newName; projectObj.project.description = newDesc;
                    if (dueEl && dueEl.value) projectObj.project.dueDate = dueEl.value;
                    if (prEl && prEl.value) projectObj.project.priority = prEl.value.toLowerCase();
                    if (projectSafe) { projectSafe.name = newName; projectSafe.description = newDesc; if (dueEl && dueEl.value) projectSafe.dueDate = dueEl.value; if (prEl && prEl.value) projectSafe.priority = prEl.value.toLowerCase(); }
                    try { const de = document.getElementById('project-data'); if (de && projectObj) de.textContent = JSON.stringify(projectObj); } catch(e){}
                  } catch(e){}
                  try { const mainElLocal2 = document.querySelector('main.hive-content'); if (mainElLocal2 && mainElLocal2.dataset) { /* nothing to set there, DOM updated below */ } } catch(e){}
                  try { renderHiveTasks(); } catch(e){}
                } else { const txt = json && json.error ? json.error : (await res.text().catch(()=>'')); console.warn('save_project response not OK, falling back to reload', res.status, txt); location.reload(); }
              } catch (err) { console.error('personalhive: save from clone failed', err); alert('Failed to save project'); }
            };
          } catch(e) { console.warn('personalhive: failed to wire fallback buttons', e); }
          modal.style.display = 'flex';
        } catch(err) { console.error('personalhive: clone open fallback failed', err); }
      });
      
      orig.parentNode.replaceChild(clone, orig);
      return true;
    } catch (err) { console.error('personalhive: replace edit btn failed', err); return false; }
  }
  
  if (replaceOnce()) return;
  const maxAttempts = 6; let attempts = 0;
  const iv = setInterval(() => { attempts++; if (replaceOnce() || attempts >= maxAttempts) clearInterval(iv); }, 500);
  
  try {
    const mo = new MutationObserver(() => { replaceOnce(); });
    mo.observe(document.body, { childList:true, subtree:true });
    
    setTimeout(() => mo.disconnect(), 30000);
  } catch(e){}
})();
