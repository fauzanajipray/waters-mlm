crud.field('type').onChange(function(field) {
  if (field.value == 'product') {
    crud.field('is_demokit').show();
  } else {
    crud.field('is_demokit').hide();
  }
}).change();
