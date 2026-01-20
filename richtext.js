function setupRichTextEditor(toolbarId, contentId) {
  const toolbar = document.getElementById(toolbarId);
  const content = document.getElementById(contentId);
  if (!toolbar || !content) return;

  toolbar.addEventListener('click', function(e) {
    if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) return;
    const btn = e.target.closest('button');
    const cmd = btn.getAttribute('data-cmd');
    content.focus();
    if (cmd === 'h1') document.execCommand('formatBlock', false, 'H1');
    else if (cmd === 'h3') document.execCommand('formatBlock', false, 'H3');
    else if (cmd === 'p') document.execCommand('formatBlock', false, 'P');
    else if (cmd === 'ul') document.execCommand('insertUnorderedList');
    else if (cmd === 'ol') document.execCommand('insertOrderedList');
    else if (cmd === 'checkbox') {
      document.execCommand('insertUnorderedList');
      
      setTimeout(() => {
        const lists = content.querySelectorAll('ul');
        if (lists.length) {
          lists[lists.length - 1].classList.add('checkbox-list');
          lists[lists.length - 1].querySelectorAll('li').forEach(li => {
            if (!li.querySelector('input[type=checkbox]')) {
              const cb = document.createElement('input');
              cb.type = 'checkbox';
              li.insertBefore(cb, li.firstChild);
            }
          });
        }
      }, 10);
    }
    else if (cmd === 'bold') document.execCommand('bold');
    else if (cmd === 'italic') document.execCommand('italic');
  });
}