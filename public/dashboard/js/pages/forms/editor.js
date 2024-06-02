$(function () {
  $('.form-fields').sortable();
});

$(document).on('click', '.copy-form-url', function (e) {
  e.preventDefault();
  var $temp = $('<input>');
  $('body').append($temp);
  $temp.val($(this).attr('data-url')).select();
  document.execCommand('copy');
  $temp.remove();

  Toast.fire({
    title: 'Form link copied to clipboard!',
    position: 'bottom',
  });
});

$(document).on('click', '.clone-field', function (e) {
  let currentField = $(this).parents('.form-field');
  let clonedField = currentField.clone(true);

  clonedField.attr('data-field-id', '0');
  $('.form-fields').append(clonedField);
});

$(document).on('click', '.delete-field', function (e) {
  let field = $(this).parents('.form-field');
  let elementID = field.attr('data-field-id');
  Swal.fire({
    title: 'Are you sure you want to delete this field?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
  }).then((result) => {
    if (result.isConfirmed) {
      field.fadeOut(1000);
      field.prepend(
        `<input type="hidden" name="field[${elementID}][deleted]" value="1" />`
      );
    }
  });
});

$('.toggle-settings').click(function (e) {
  e.preventDefault();
  $('#formSettingsModal').modal('show');
});

function getFormDetails(id) {
  $.ajax({
    type: 'GET',
    url: `/dashboard/forms/${id}`,
    dataType: 'json',
    beforeSend: function () {
      $('.form-fields').html('Loading...');
    },
    success: function (response) {
      $('.form-fields').html('');
      let fields = response.form.fields;

      fields.forEach((field) => {
        var options = [];
        if (field.options && field.options.length > 0) {
          $.each(field.options, function (index, option) {
            options.push(option.option_value);
          });
        }
        appendElement(
          field.type,
          field.id,
          field.title,
          field.description,
          field.required,
          options
        );
      });
    },
  });
}

$('.save-form').click(function (e) {
  e.preventDefault();
  let form = $('#fieldsForm');
  let formURL = form.attr('action');

  let data = new FormData(form[0]);

  let details = {};
  let settings = {};

  let fields = [];

  $('.form-field').each(function () {
    let fieldID = $(this).data('field-id');

    let options = [];
    $(this)
      .find('.field-option-container input[type=text]')
      .each(function (index, element) {
        options.push($(element).val());
      });

    let field = {
      id: fieldID,
      title: $(this)
        .find('input[name="field[' + fieldID + '][title]"]')
        .val(),
      description: $(this)
        .find('input[name="field[' + fieldID + '][description]"]')
        .val(),
      type: $(this)
        .find('input[name="field[' + fieldID + '][type]"]')
        .val(),
      required: $(this)
        .find('input[name="field[' + fieldID + '][required]"]')
        .is(':checked')
        ? 1
        : 0,
      deleted:
        $(this)
          .find('input[name="field[' + fieldID + '][deleted]"]')
          .val() ?? 0,
      options: options,
    };
    fields.push(field);
  });

  for (let [key, value] of data.entries()) {
    if (key.startsWith('detail_')) {
      details[key] = value;
    } else if (key.startsWith('setting_')) {
      settings[key] = value;
    } else if (key.startsWith('field')) {
      continue;
    } else {
      console.error(`Invalid element key: ${key}`);
    }
  }

  let btn = $(this);

  $.ajax({
    type: 'POST',
    url: formURL,
    data: { details: details, fields: fields, settings: settings },
    dataType: 'json',
    beforeSend: function () {
      btn
        .html('<i class="fas fa-spin fa-circle-notch"></i> Saving...')
        .prop('disabled', true);
    },
    success: function (response) {
      Toast.fire({
        icon: 'success',
        title: 'Form Saved Successfully',
      });

      // if create form then redirect to edit page
      // Else reload fields with new ids
      if (response.status == 201) {
        window.location.href = 'edit/' + response.id;
      } else {
        getFormDetails(response.id);
      }
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
  let formURL = $(this).data('url');
  window.open(formURL, '_blank');
});

$('.add-element-node').click(function (e) {
  e.preventDefault();
  let elementType = parseInt($(this).attr('data-type'));
  let elementID = -Math.floor(Math.random() * 999999999);
  appendElement(elementType, elementID);
});

function appendElement(
  elementType,
  elementID,
  elementTitle = 'Untitled Question Title',
  elementDescription = null,
  elementRequired = true,
  elementOptions = ['First Option', 'Second Option', 'Third Option']
) {
  let element = null;

  switch (elementType) {
    case 1:
      element = prepareShortAnswerElement(
        elementID,
        elementType,
        elementTitle,
        elementDescription,
        elementRequired
      );
      break;
    case 2:
      element = prepareParagraphElement(
        elementID,
        elementType,
        elementTitle,
        elementDescription,
        elementRequired
      );
      break;
    case 3:
      element = prepareSingleElement(
        elementID,
        elementType,
        elementTitle,
        elementDescription,
        elementRequired,
        elementOptions
      );
      break;
    case 4:
      element = prepareMultipleElement(
        elementID,
        elementType,
        elementTitle,
        elementDescription,
        elementRequired,
        elementOptions
      );
      break;
    case 5:
      element = prepareFileUploadElement(
        elementID,
        elementType,
        elementTitle,
        elementDescription,
        elementRequired
      );
      break;
    case 6:
      element = prepareDateElement(
        elementID,
        elementType,
        elementTitle,
        elementDescription,
        elementRequired
      );
      break;
    case 7:
      element = prepareTimeElement(
        elementID,
        elementType,
        elementTitle,
        elementDescription,
        elementRequired
      );
      break;
    default:
      alert('Element not found');
      break;
  }

  $('.form-fields').append(element);
}

function prepareShortAnswerElement(
  elementID,
  elementType,
  elementTitle,
  elementDescription,
  elementRequired
) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <input name="field[${elementID}][id]" type="hidden" value="${elementID}" />
    <input name="field[${elementID}][type]" type="hidden" value="${elementType}" />
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field[${elementID}][title]" value="${elementTitle}" placeholder="Question Title"/>
    </div>
    <div class="form-group d-flex">
      ${elementRequired ? '<span class="text-danger">*</span>' : ''}
      <input class="ps-0 form-control" name="" disabled placeholder="Question Input"/>
    </div>
    ${prepareFieldActions(elementID, elementRequired)}
  </div>`;

  return element;
}

function prepareParagraphElement(
  elementID,
  elementType,
  elementTitle,
  elementDescription,
  elementRequired
) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <input name="field[${elementID}][id]" type="hidden" value="${elementID}" />
    <input name="field[${elementID}][type]" type="hidden" value="${elementType}" />
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field[${elementID}][title]" value="${elementTitle}" placeholder="Question Title"/>
    </div>
    <div class="d-flex form-group">
      ${elementRequired ? '<span class="text-danger">*</span>' : ''}
      <textarea class="ps-0 form-control" name="" disabled placeholder="Paragraph"></textarea>
    </div>
    ${prepareFieldActions(elementID, elementRequired)}
  </div>`;

  return element;
}

function prepareDateElement(
  elementID,
  elementType,
  elementTitle,
  elementDescription,
  elementRequired
) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <input name="field[${elementID}][id]" type="hidden" value="${elementID}" />
    <input name="field[${elementID}][type]" type="hidden" value="${elementType}" />
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field[${elementID}][title]" value="${elementTitle}" placeholder="Question Title"/>
    </div>
    <div class="d-flex form-group">
      ${elementRequired ? '<span class="text-danger">*</span>' : ''}
      <input type="date" class="ps-0 form-control" name="" disabled />
    </div>
    ${prepareFieldActions(elementID, elementRequired)}
  </div>`;

  return element;
}

function prepareTimeElement(
  elementID,
  elementType,
  elementTitle,
  elementDescription,
  elementRequired
) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <input name="field[${elementID}][id]" type="hidden" value="${elementID}" />
    <input name="field[${elementID}][type]" type="hidden" value="${elementType}" />
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field[${elementID}][title]" value="${elementTitle}" placeholder="Question Title"/>
    </div>
    <div class="form-group d-flex">
      ${elementRequired ? '<span class="text-danger">*</span>' : ''}
      <input type="time" class="ps-0 form-control" name="" value=""  disabled />
    </div>
    ${prepareFieldActions(elementID, elementRequired)}
  </div>`;

  return element;
}

function prepareFileUploadElement(
  elementID,
  elementType,
  elementTitle,
  elementDescription,
  elementRequired
) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <input name="field[${elementID}][id]" type="hidden" value="${elementID}" />
    <input name="field[${elementID}][type]" type="hidden" value="${elementType}" />
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field[${elementID}][title]" value="${elementTitle}" placeholder="Question Title"/>
    </div>
    <div class="form-group d-flex">
      ${elementRequired ? '<span class="text-danger">*</span>' : ''}
      <input type="file" class="ps-0 form-control" name="" value=""  disabled />
    </div>
    ${prepareFieldActions(elementID, elementRequired)}
  </div>`;

  return element;
}

function prepareMultipleElement(
  elementID,
  elementType,
  elementTitle,
  elementDescription,
  elementRequired,
  elementOptions
) {
  let element = `<div class="form-field" data-field-id="${elementID}">
  <input name="field[${elementID}][id]" type="hidden" value="${elementID}" />
  <input name="field[${elementID}][type]" type="hidden" value="${elementType}" />
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field[${elementID}][title]" value="${elementTitle}" placeholder="Question Title"/>
    </div>
    <div class="form-group">
      ${elementRequired ? '<span class="text-danger">*</span>' : ''}
      <div class="options">
        ${prepareFieldOptions(elementID, elementOptions, 'checkbox')}
      </div>
      <div class="text-right">
        <button class="btn btn-sm btn-primary add-field-option" data-field-id="${elementID}" type="button">Add New Option</button>
      </div>
    </div>
    ${prepareFieldActions(elementID, elementRequired)}
  </div>`;

  return element;
}

function prepareSingleElement(
  elementID,
  elementType,
  elementTitle,
  elementDescription,
  elementRequired,
  elementOptions
) {
  let element = `<div class="form-field" data-field-id="${elementID}">
    <input name="field[${elementID}][id]" type="hidden" value="${elementID}" />
    <input name="field[${elementID}][type]" type="hidden" value="${elementType}" />
    <div class="draggable-icon">
      <i class="fas fa-grip-horizontal"></i>
    </div>
    <div class="form-group">
      <input class="form-control" name="field[${elementID}][title]" value="${elementTitle}" placeholder="Question Title"/>
    </div>
    <div class="form-group">
    ${elementRequired ? '<span class="text-danger">*</span>' : ''}
      <div class="options">
        ${prepareFieldOptions(elementID, elementOptions, 'radio')}
      </div>
      <div class="text-right">
        <button class="btn btn-sm btn-primary add-field-option" data-field-id="${elementID}" type="button">Add New Option</button>
      </div>
      </div>
    ${prepareFieldActions(elementID, elementRequired)}
  </div>`;

  return element;
}

function prepareFieldActions(elementID, elementRequired) {
  return `<div class="form-group field-actions text-right">
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
          <input class="form-check-input" name="field[${elementID}][required]" 
          type="checkbox" ${elementRequired && 'checked'} />Required</label>
      </div>
    </div>
  </div>
  </div>`;
}

function prepareFieldOptions(elementID, elementOptions, optionType) {
  let fieldOptions = '';

  $.each(elementOptions, function (i, elementOption) {
    fieldOptions += `<label class="field-option-container d-flex">
       <input type="${optionType}" name="field[${elementID}][option][${i}]" />
       <input type="text" class="form-control" name="" value="${elementOption}" />
       ${
         i > 0
           ? `<button class="btn btn-sm btn-danger delete-field-option" type="button"><i class="fa fa-trash"></i></button>`
           : ''
       }
    </label>`;
  });

  return fieldOptions;
}

$(document).on('click', '.delete-field-option', function (e) {
  $(this).parents('.field-option-container').remove();
});

$(document).on('click', '.add-field-option', function (e) {
  let $this = $(this);
  let elementID = $this.attr('data-field-id');
  let $formField = $this.parents('.form-field');
  let $optionsContainer = $formField.find('.options');

  let optionType = $formField.find('.field-option-container input[type=radio]')
    .length
    ? 'radio'
    : 'checkbox';

  let i = $optionsContainer.find('.field-option-container').length;

  $optionsContainer.append(`<label class="field-option-container d-flex">
    <input type="${optionType}" name="field[${elementID}][option][${i}]" />
    <input type="text" class="form-control" name="" value="" />
    <button class="btn btn-sm btn-danger delete-field-option" type="button"><i class="fa fa-trash"></i></button>
  </label>`);
});
