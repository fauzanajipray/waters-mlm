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

crud.field('customer_id').onChange(function(field){
  if(field.value){
    // check ajax
    $.ajax({
      url: '/transaction/check-customer',
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        customer_id: field.value,
        member_id: crud.field('member_id').value
      },
      success: function(data) {
        if(!data.status) {
          var customer = document.getElementsByName('customer_id')[0];
          for (var i = 0; i < customer.options.length; i++) {
            if (customer.options[i].value == field.value) {
              customer.remove(i);
            }
          }
        }
      }
    });
  }
});