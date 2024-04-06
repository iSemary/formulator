function deleteForm(id, row, btn) {
  $.ajax({
    type: 'POST',
    url: `/dashboard/forms/delete/${id}`,
    dataType: 'json',
    success: function (response) {
      row.find('td:nth-of-type(3)').text('In Active');
      btn
        .removeClass('btn-danger delete-form')
        .addClass('btn-warning restore-form')
        .text('Restore');

      Toast.fire({
        icon: 'success',
        title: 'Form Deleted Successfully',
      });
    },
    error: function (xhr, status, error) {
      console.error(xhr.responseText);
    },
  });
}

function restoreForm(id, row, btn) {
  $.ajax({
    type: 'POST',
    url: `/dashboard/forms/restore/${id}`,
    dataType: 'json',
    success: function (response) {
      row.find('td:nth-of-type(3)').text('Active');
      btn
        .removeClass('btn-warning restore-form')
        .addClass('btn-danger delete-form')
        .text('Delete');

      Toast.fire({
        icon: 'success',
        title: 'Form Restored Successfully',
      });
    },
    error: function (xhr, status, error) {
      console.error(xhr.responseText);
    },
  });
}

$(document).on('click', '.delete-form', function (e) {
  e.preventDefault();
  let id = $(this).data('id');
  let row = $(this).closest('tr');
  let btn = $(this);
  Swal.fire({
    title: 'Are you sure you want to delete this form?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
  }).then((result) => {
    if (result.isConfirmed) {
      deleteForm(id, row, btn);
    }
  });
});

$(document).on('click', '.restore-form', function (e) {
  e.preventDefault();
  let id = $(this).data('id');
  let row = $(this).closest('tr');
  let btn = $(this);
  Swal.fire({
    title: 'Are you sure you want to restore this form?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, restore it!',
  }).then((result) => {
    if (result.isConfirmed) {
      restoreForm(id, row, btn);
    }
  });
});
