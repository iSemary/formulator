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

$('.toggle-settings').click(function (e) {
  e.preventDefault();
});

$('.save-form').click(function (e) {
  e.preventDefault();
});

$('.preview-form').click(function (e) {
  e.preventDefault();
});

$('.add-element-node').click(function (e) {
  e.preventDefault();
  let elementType = parseInt($(this).attr('data-type'));
  appendElement(elementType);
});

function appendElement(elementType) {
  let element = null;
  switch (elementType) {
    case 1:
      element = 'Text Element';
      break;
    default:
      alert('Element not found');
      break;
  }

  $('.form-fields').append(element);
}
