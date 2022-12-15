crud.field('type').onChange(function(field) {
  if (field.value == 'product') {
    crud.field('model').show();
    crud.field('capacity').show();
    crud.field('is_demokit').show();
  } else {
    crud.field('model').hide();
    crud.field('capacity').hide();
    crud.field('is_demokit').hide();
  }
}).change();
