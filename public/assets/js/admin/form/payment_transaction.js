crud.field('type').onChange(function(field) {
  if (field.value == 'Full'){
    crud.field('amount').hide();
  } else {
    crud.field('amount').show();
  }
}).change();