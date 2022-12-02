crud.field('type_discount').onChange(function(field) {
  if(field.value == 'percent') {
    crud.field('discount_percentage').show();
    crud.field('discount_amount').hide();
  } else {
    crud.field('discount_percentage').hide();
    crud.field('discount_amount').show();
  }
}).change();