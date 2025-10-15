document.addEventListener('DOMContentLoaded', () => {
  const body = document.querySelector('body');
  const sidebar = document.querySelector('.sidebar');
  const toggle = sidebar?.querySelector('.toggle');

  // ===== Sidebar toggle =====
  toggle?.addEventListener('click', () => {
    sidebar.classList.toggle('close');
  });

  // ===== Search filter =====
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

  // ===== General Modal Helpers =====
  const showModal = (id) => {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'block';
  };

  const hideModal = (id) => {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
  };

  // ===== Common Modals =====
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

  // ===== Program Modals =====
  window.openProgramModal = () => showModal('programModal');
  window.closeProgramModal = () => hideModal('programModal');

  document.getElementById('addProgramForm')?.addEventListener('submit', function(e) {
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
          hideModal('programModal');
        } else {
          alert('Failed to add program: ' + data.message);
        }
      });
  });

  // ===== Update/Delete Program =====
  window.openUpdateProgramModal = (id, name) => {
    document.getElementById('updateProgramId').value = id;
    document.getElementById('updateProgramName').value = name;
    showModal('updateProgramModal');
  };

  window.closeUpdateProgramModal = () => hideModal('updateProgramModal');

  window.openDeleteProgramModal = (id) => {
    document.getElementById('deleteProgramId').value = id;
    showModal('deleteProgramModal');
  };

  window.closeDeleteProgramModal = () => hideModal('deleteProgramModal');

  // ===== SFR Modals =====
  window.openUpdateSfrModal = (id, programName, surveyType, surveyDate) => {
    document.getElementById('updateSfrId').value = id;
    document.getElementById('updateSfrProgramName').value = programName;
    document.getElementById('updateSfrSurveyType').value = surveyType;
    document.getElementById('updateSfrSurveyDate').value = surveyDate;
    showModal('updateSfrModal');
  };

  window.closeUpdateSfrModal = () => hideModal('updateSfrModal');

  window.openDeleteSfrModal = (id) => {
    document.getElementById('deleteSfrId').value = id;
    showModal('deleteSfrModal');
  };

  window.closeDeleteSfrModal = () => hideModal('deleteSfrModal');

  // ===== Document Modals =====
  window.openUpdateDocuModal = (id, name) => {
    document.getElementById('updateDocuId').value = id;
    document.getElementById('updateDocuName').value = name;
    showModal('updateDocuModal');
  };

  window.closeUpdateDocuModal = () => hideModal('updateDocuModal');

  window.openDeleteDocuModal = (id) => {
    document.getElementById('deleteDocuId').value = id;
    showModal('deleteDocuModal');
  };

  window.closeDeleteDocuModal = () => hideModal('deleteDocuModal');

  // ===== TRBA Modals =====
window.openUpdateTrbaModal = (id, program_Name, survey_Type, survey_Date) => {
  document.getElementById('updateTrbaId').value = id;
  document.getElementById('updateTrbaProgram').value = program_Name;
  document.getElementById('updateTrbaSurveyType').value = survey_Type;
  document.getElementById('updateTrbaSurveyDate').value = survey_Date;
  showModal('updateTrbaModal');
};

window.closeUpdateTrbaModal = () => hideModal('updateTrbaModal');

window.openDeleteTrbaModal = (id) => {
  document.getElementById('deleteTrbaId').value = id;
  showModal('deleteTrbaModal');
};

window.closeDeleteTrbaModal = () => hideModal('deleteTrbaModal');


  // ===== Unified Click Outside Close =====
  window.addEventListener('click', (event) => {
    const modalIds = [
      'addModal', 'deleteModal', 'updateModal', 'uploadModal',
      'successModal', 'messageModal', 'programModal',
      'updateProgramModal', 'deleteProgramModal',
      'updateSfrModal', 'deleteSfrModal',
      'updateDocuModal', 'deleteDocuModal',
      'updateTrbaModal', 'deleteTrbaModal'
    ];

    modalIds.forEach(id => {
      const modal = document.getElementById(id);
      if (modal && event.target === modal) {
        modal.style.display = 'none';
      }
    });
  });
});
