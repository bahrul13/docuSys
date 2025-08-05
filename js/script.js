document.addEventListener('DOMContentLoaded', () => {
  const body = document.querySelector('body');
  const sidebar = document.querySelector('.sidebar');
  const toggle = sidebar?.querySelector('.toggle');

  // Sidebar toggle
  toggle?.addEventListener('click', () => {
    sidebar.classList.toggle('close');
  });

  // Search filter
  window.filterTable = () => {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const trs = document.querySelectorAll('table tr');

    trs.forEach((tr, i) => {
      if (i === 0) return; // Skip header
      const tds = tr.querySelectorAll('td');
      const visible = Array.from(tds).some(td =>
        td.textContent.toLowerCase().includes(filter)
      );
      tr.style.display = visible ? '' : 'none';
    });
  };

  // Modal helpers
  const showModal = (id) => document.getElementById(id).style.display = 'block';
  const hideModal = (id) => document.getElementById(id).style.display = 'none';

  window.openModal = () => showModal('addModal');
  window.closeModal = () => hideModal('addModal');

  window.openDeleteModal = (id) => {
    document.getElementById('deleteId').value = id;
    showModal('deleteModal');
  };
  window.closeDeleteModal = () => hideModal('deleteModal');

  window.openUpdateModal = (id, program, issuance_date) => {
    document.getElementById('updateId').value = id;
    document.getElementById('updateProgram').value = program;
    document.getElementById('updateDate').value = issuance_date;
    showModal('updateModal');
  };
  window.closeUpdateModal = () => hideModal('updateModal');
  window.closeUploadModal = () => hideModal('uploadModal');

  window.closeMessageModal = () => {
    hideModal('messageModal');
    const url = new URL(window.location);
    url.searchParams.delete('updated');
    window.history.replaceState({}, document.title, url);
  };

  // Close modals by clicking outside
  window.onclick = function (event) {
    const modalIds = [
      'addModal', 'deleteModal', 'updateModal',
      'uploadModal', 'successModal', 'messageModal'
    ];

    modalIds.forEach(id => {
      const modal = document.getElementById(id);
      if (modal && event.target === modal) {
        modal.style.display = 'none';
      }
    });
  };
});

function openModal() {
    document.getElementById('programModal').style.display = 'flex';
  }
  function closeModal() {
    document.getElementById('programModal').style.display = 'none';
  }

  document.getElementById('addProgramForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const newProgram = document.getElementById('newProgram').value;

    fetch('../handlers/add_program.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'program_name=' + encodeURIComponent(newProgram)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const select = document.getElementById('program');
        const option = document.createElement('option');
        option.value = newProgram;
        option.text = newProgram;
        option.selected = true;
        select.add(option);
        closeModal();
      } else {
        alert('Failed to add program: ' + data.message);
      }
    });
  });
