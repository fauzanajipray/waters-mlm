crud.field('member_id').onChange(function(field) {
  if (field.value) {
    crud.field('shipping_address').show();
      crud.field('customer_id').show();
      crud.field('is_member').show();
      if(crud.field('is_member').value == 1) {
        crud.field('customer_id').hide();
      }
      
      var address = document.getElementsByName('shipping_address')[0];
        if(crud.field('is_member').value == 1) {
        $.ajax({
          url: '/customer/get-customer-is-member',
          type: 'POST',
          data: {
            member_id: crud.field('member_id').value,
            _token: $('meta[name="csrf-token"]').attr('content'),
          },
          success: function(data) {
            address.innerHTML = data.data;
          }
        })
      }
  } else {
      crud.field('customer_id').hide();
      crud.field('is_member').hide();
      crud.field('shipping_address').hide();
  }
}).change();

crud.field('is_member').onChange(function(field) {
  var address = document.getElementsByName('shipping_address')[0];
  if (field.value == 1) {
      crud.field('customer_id').hide();
      // AJAX call to get member address
      $.ajax({
        url: '/customer/get-customer-is-member',
        type: 'POST',
        data: {
          member_id: crud.field('member_id').value,
          _token: $('meta[name="csrf-token"]').attr('content'),
        },
        success: function(data) {
          address.innerHTML = data.data;
        }
      })
  } else {
      address.innerHTML = '';
      crud.field('customer_id').show();
  }
}).change();

crud.field('customer_id').onChange(function(field){
  var address = document.getElementsByName('shipping_address')[0];
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
        } else {
          address.innerHTML = data.data.address;
        }
      }
    });
  } else {
    address.innerHTML = '';
  }
});