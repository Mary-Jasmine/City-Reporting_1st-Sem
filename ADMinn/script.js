document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('modal');
  const btnAdd = document.getElementById('btnAdd');
  const modalClose = document.getElementById('modalClose');
  const btnCancel = document.getElementById('btnCancel');
  const annForm = document.getElementById('annForm');
  const actionInput = document.getElementById('action');
  const modalTitle = document.getElementById('modalTitle');

  btnAdd.addEventListener('click', () => openModal('create'));
  modalClose.addEventListener('click', closeModal);
  btnCancel.addEventListener('click', closeModal);

  function openModal(mode, data = null) {
    modal.classList.remove('hidden');
    if (mode === 'create') {
      modalTitle.textContent = 'Add Announcement Details';
      actionInput.value = 'create';
      annForm.reset();
      document.getElementById('announcements_id').value = '';
    } else {
      modalTitle.textContent = 'Edit Announcement Details';
      actionInput.value = 'update';
      document.getElementById('announcements_id').value = data.announcements_id;
      document.getElementById('title').value = data.title;
      document.getElementById('content').value = stripTags(data.content);
      document.getElementById('type').value = data.type;
      document.getElementById('priority').value = data.priority;
      document.getElementById('status').value = data.status;
      if (data.published_at) document.getElementById('published_at').value = formatLocalDatetime(data.published_at);
      if (data.expires_at) document.getElementById('expires_at').value = formatLocalDatetime(data.expires_at);
    }
  }

  function closeModal() {
    modal.classList.add('hidden');
  }

  document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function(){
      if (!confirm('Are you sure you want to delete this announcement?')) return;
      const id = this.dataset.id;
      fetch('process_announcement.php', {
        method: 'POST',
        body: new URLSearchParams({action: 'delete', announcements_id: id})
      }).then(r => r.json()).then(res => {
        alert(res.message);
        if (res.success) location.reload();
      });
    });
  });

  document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', function(){
      const id = this.dataset.id;
      fetch('get_announcement.php?id=' + id).then(r => r.json()).then(data => {
        if (!data.success) return alert(data.message || 'Cannot load entry');
        openModal('update', data.record);
      });
    });
  });

  annForm.addEventListener('submit', function(e){
    e.preventDefault();
    const fd = new FormData(annForm);
    fetch('process_announcement.php', {
      method: 'POST',
      body: fd
    }).then(r => r.json()).then(res => {
      alert(res.message);
      if (res.success) {
        closeModal();
        location.reload();
      }
    }).catch(err => {
      console.error(err);
      alert('Error saving announcement.');
    });
  });

  function stripTags(html) {
    const div = document.createElement('div');
    div.innerHTML = html;
    return div.textContent || div.innerText || '';
  }
  function formatLocalDatetime(sqlDatetime) {
    if (!sqlDatetime) return '';
    const dt = new Date(sqlDatetime);
    const pad = (v) => v.toString().padStart(2,'0');
    const YYYY = dt.getFullYear();
    const MM = pad(dt.getMonth()+1);
    const DD = pad(dt.getDate());
    const HH = pad(dt.getHours());
    const mm = pad(dt.getMinutes());
    return `${YYYY}-${MM}-${DD}T${HH}:${mm}`;
  }
});

function openReadMore(id) {
  window.open('view_announcement.php?id=' + id, '_blank', 'width=800,height=600');
}
