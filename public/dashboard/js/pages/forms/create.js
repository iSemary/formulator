$(function () {
  $('.form-fields').sortable();
});

let fieldActions = `<div class="form-group field-actions text-right">
<div class="field-buttons-action">
  <button class="btn btn-sm clone-field" type="button">
    <i class="far fa-clone"></i>
  </button>
  <button class="btn btn-sm delete-field" type="button">
    <i class="far fa-trash-alt"></i>
  </button>
</div>
<div class="field-extra-action">
  <div class="field-required">
    <div class="form-check form-switch">
      <label class="form-check-label">
        <input class="form-check-input" type="checkbox"/>Required</label>
    </div>
  </div>
</div>
</div>`;

$(document).on('click', '.clone-field', function (e) {
  let currentField = $(this).parents('.form-field');
  let clonedField = currentField.clone(true);

  clonedField.attr('data-field-id', '0');
  $('.form-fields').append(clonedField);
});

$(document).on('click', '.delete-field', function (e) {
  let field = $(this).parents('.form-field');
  Swal.fire({
    title: 'Are you sure you want to delete this field?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
  }).then((result) => {
    if (result.isConfirmed) {
      field.fadeOut(1000);
      setTimeout(() => {
        field.remove();
      }, 1000);
    }
  });
});

$('.toggle-settings').click(function (e) {
  e.preventDefault();
  $('#formSettingsModal').modal('show');
});

$('.save-form').click(function (e) {
  e.preventDefault();
  let data = new FormData($('#fieldsForm')[0]);

  let details = {};
  let settings = {};
  let fields = {};
  for (let [key, value] of data.entries()) {
    if (key.startsWith('detail_')) {
      details[key] = value;
    } else if (key.startsWith('setting_')) {
      settings[key] = value;
    } else if (key.startsWith('field_')) {
      fields[key] = value;
    } else {
      console.error(`Invalid element key: ${key}`);
    }
  }

  let btn = $(this);

  $.ajax({
    type: 'POST',
    url: '/dashboard/forms/store',
    data: { details: details, fields: fields, settings: settings },
    dataType: 'json',
    beforeSend: function () {
      btn.html('<i class="fas fa-spin fa-circle-notch"></i> Saving...').prop('disabled', true);
    },
    success: function (response) {
      Toast.fire({
        icon: 'success',
        title: 'Form Saved Successfully',
      });
    },
    error: function (xhr) {
      Toast.fire({
        icon: 'error',
        title: 'Something went wrong, Check Console!',
      });
      console.error(xhr);
    },
    complete: function () {
      btn.html('<i class="fas fa-save"></i> Save').prop('disabled', false);
    },
  });
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
  let elementID = -Math.floor(Math.random() * 999999999);

  switch (elementType) {
    case 1:
      element = prepareShortAnswerElement(elementID);
      break;
    case 2:
      element = prepareParagraphElement(elementID);
      break;
    case 3:
      element = prepareSingleElement(elementID);
      break;
    case 4:
      element = prepareMultipleElement(elementID);
      break;
    case 5:
      element = prepareFileUploadElement(elementID);
      break;
    case 6:
      element = prepareDateElement(elementID);
      break;
    case 7:
      element = prepareTimeElement(elementID);
      break;
    default:
      alert('Element not found');
      break;
  }

  $('.form-fields').append(element);
}

function prepareShortAnswerElement(elementID) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field_title[${elementID}]" value="Untitled Question Title" placeholder="Question Title"/>
    </div>
    <div class="form-group">
      <input class="form-control" name="field_input[${elementID}]" placeholder="Question Input"/>
    </div>
  </div>`;

  return element;
}

function prepareParagraphElement(elementID) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field_title[${elementID}]" value="Untitled Question Title" placeholder="Question Title"/>
    </div>
    <div class="form-group">
      <textarea class="form-control" name="field_input[${elementID}]" placeholder="Paragraph"></textarea>
    </div>
  </div>`;

  return element;
}

function prepareDateElement(elementID) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field_title[${elementID}]" value="Untitled Question Title" placeholder="Question Title"/>
    </div>
    <div class="form-group">
      <input type="date" class="form-control" name="field_input[${elementID}]"  />
    </div>
  </div>`;

  return element;
}

function prepareTimeElement(elementID) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field_title[${elementID}]" value="Untitled Question Title" placeholder="Question Title"/>
    </div>
    <div class="form-group">
      <input type="time" class="form-control" name="field_input[${elementID}]"  />
    </div>
  </div>`;

  return element;
}

function prepareFileUploadElement(elementID) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field_title[${elementID}]" value="Untitled Question Title" placeholder="Question Title"/>
    </div>
    <div class="form-group">
      <input type="file" class="form-control" name="field_input[${elementID}]"  />
    </div>
  </div>`;

  return element;
}

function prepareMultipleElement(elementID) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field_title[${elementID}]" value="Untitled Question Title" placeholder="Question Title"/>
    </div>
    <div class="form-group">
      <label>
        <input type="text" class="form-control" name="field_title[${elementID}]" />
        <input type="checkbox" name="field_title[${elementID}]" />
      </label>
    </div>
  </div>`;

  return element;
}

function prepareSingleElement(elementID) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field_title[${elementID}]" value="Untitled Question Title" placeholder="Question Title"/>
    </div>
    <div class="form-group">
      <label>
        <input type="text" class="form-control" name="field_title[${elementID}]" />
        <input type="radio" name="field_title[${elementID}]" />
      </label>
    </div>
  </div>`;

  return element;
}
