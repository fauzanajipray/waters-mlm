crud.field('member_id').onChange(function(field) {
  if (field.value) {
      crud.field('customer_id').show();
      crud.field('is_member').show();
      if(crud.field('is_member').value == 1) {
        crud.field('customer_id').hide();
      }
  } else {
      crud.field('customer_id').hide();
      crud.field('is_member').hide();
  }
}).change();

crud.field('is_member').onChange(function(field) {
  if (field.value == 1) {
      crud.field('customer_id').hide();
  } else {
      crud.field('customer_id').show();
  }
}).change();