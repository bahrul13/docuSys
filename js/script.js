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

  //PROGRAM MODALS

   function openUpdateProgramModal(id, name) {
    document.getElementById('updateProgramId').value = id;
    document.getElementById('updateProgramName').value = name;
    document.getElementById('updateProgramModal').style.display = 'block';
  }

  function closeUpdateProgramModal() {
    document.getElementById('updateProgramModal').style.display = 'none';
  }

  function openDeleteProgramModal(id) {
    document.getElementById('deleteProgramId').value = id;
    document.getElementById('deleteProgramModal').style.display = 'block';
  }

  function closeDeleteProgramModal() {
    document.getElementById('deleteProgramModal').style.display = 'none';
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    const updateModal = document.getElementById('updateProgramModal');
    const deleteModal = document.getElementById('deleteProgramModal');
    if (event.target === updateModal) updateModal.style.display = 'none';
    if (event.target === deleteModal) deleteModal.style.display = 'none';
  };


  //SFR MODALS

  // Open Update Modal
function openUpdateSfrModal(id, programName, surveyType, surveyDate) {
  document.getElementById('updateSfrId').value = id;
  document.getElementById('updateSfrProgramName').value = programName;
  document.getElementById('updateSfrSurveyType').value = surveyType;
  document.getElementById('updateSfrSurveyDate').value = surveyDate;
  document.getElementById('updateSfrModal').style.display = 'block';
}

// Close Update Modal
function closeUpdateSfrModal() {
  document.getElementById('updateSfrModal').style.display = 'none';
}

// Open Delete Modal
function openDeleteSfrModal(id) {
  document.getElementById('deleteSfrId').value = id;
  document.getElementById('deleteSfrModal').style.display = 'block';
}

// Close Delete Modal
function closeDeleteSfrModal() {
  document.getElementById('deleteSfrModal').style.display = 'none';
}