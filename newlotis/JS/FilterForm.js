let filterTimeout;

$(document).ready(function() {
    $(document).on('change', 'form.filterform input.filterfield, form.filterform select.filterfield', function () {
        clearTimeout(filterTimeout);
        const $form = $(this).closest('form');
        const formId = $form.attr('id');

        filterTimeout = setTimeout(() => {
            var values = ltsForm.values(formId);
            LTS.get(formId).filter(values);
        }, 300);
    });

      // === Обработчик клика по кнопке очистки поля ===
    $(document).on('click', 'button.filterdelbutton', function (e) {
        const $button = $(this);
        const formId = $button.data('form');
        const fieldName = $button.data('field');
        ltsForm.value(formId, fieldName, '');
        var values = ltsForm.values(formId);
        LTS.get(formId).filter(values);
    });

      // === Обработчик клика по кнопке списка ===
    $(document).on('click', 'button.filterlistbutton', function (e) {
        e.stopPropagation(); // Чтобы не вызвать закрытие сразу

        const $button = $(this);
        const tableId = $button.data('table');
        const formId = $button.data('form');
        const fieldName = $button.data('field');
        const values = ltsDataTable.fieldvalues(tableId, fieldName); 

        $('#dropdown-menu').remove(); // Удаляем предыдущий, если есть

        const dropdown = $('<ul>', {
        id: 'dropdown-menu',
        class: 'dropdown-menu',
        css: {
            top: $button.offset().top + $button.outerHeight(),
            left: $button.offset().left
        }
    });

    values.forEach(value => {
      dropdown.append(
        $('<li>').append(
          $('<a>', {
            text: value,
            href: '#',
            'data-form': formId,
            'data-field': fieldName,
            'data-value': value
          })
        )
      );
    });

    $('body').append(dropdown);

    // === Добавляем обработчики событий только после открытия ===
    bindOutsideClick();
    bindEscapeKey();
  });

  // === Обработчик клика по пункту списка ===
  $(document).on('click', '.dropdown-menu li a', function (e) {
    e.preventDefault();
    e.stopPropagation();

    const formId = $(this).attr('data-form');
    const fieldName = $(this).attr('data-field');
    const value = $(this).attr('data-value');

    const $form = $('#' + formId);
    const $input = $form.find(`[name="${fieldName}"]`);

    if ($input.length) 
      $input.val(value).trigger('change');

    closeDropdown();
  });

  // === Вспомогательные функции ===

  function bindOutsideClick() {
    // Закрытие при клике вне списка
    $(document).on('click.dropdown-close', function (e) {
      if ($('#dropdown-menu').length && !$('#dropdown-menu').is(e.target) && $('#dropdown-menu').has(e.target).length === 0) {
        closeDropdown();
      }
    });
  }

  function bindEscapeKey() {
    // Закрытие при нажатии ESC
    $(document).on('keydown.dropdown-close', function (e) {
      if (e.key === 'Escape') {
        closeDropdown();
      }
    });
  }

  function closeDropdown() {
    $('#dropdown-menu').remove();

    // Удаляем временные обработчики
    $(document).off('click.dropdown-close');
    $(document).off('keydown.dropdown-close');
  }
});