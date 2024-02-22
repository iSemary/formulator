$(function () {
  $('.form-fields').sortable();
});

$(document).on('click', '.clone-field', function (e) {
  let currentField = $(this).parents('.form-field');
  let clonedField = currentField.clone(true);

  clonedField.attr('data-field-id', '0');
  $('.form-fields').append(clonedField);
});

$(document).on('click', '.delete-field', function (e) {
  let field = $(this).parents('.form-field');
  // TODO Add SweetAlert

  field.fadeOut(1000);
  setTimeout(() => {
    field.remove();
  }, 1000);
});
