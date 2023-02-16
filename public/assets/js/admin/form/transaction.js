var baseURL = crud.field('url').value;

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
      var url = baseURL + '/customer/get-customer-is-member';
      $.ajax({
        url: url,
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
    // AJAX call to get member type
    $.ajax({
        url: baseURL + '/member/member-type?member_id=' + field.value,
        type: 'GET',
        success: function(data) {
            if(data == 'NSI'){
                crud.field('is_nsi').uncheck();
                crud.field('is_nsi').disable();
                crud.field('nsi').hide();
            } else {
                crud.field('is_nsi').enable();
                crud.field('is_nsi').uncheck();
            }
        }
    });
  } else {
        crud.field('customer_id').hide();
        crud.field('is_member').hide();
        crud.field('shipping_address').hide();
        crud.field('is_nsi').enable();
        crud.field('is_nsi').uncheck();
  }
}).change();

crud.field('is_member').onChange(function(field) {

  var address = document.getElementsByName('shipping_address')[0];
  if (field.value == 1) {
      crud.field('customer_id').hide();
      // AJAX call to get member address
      $.ajax({
        url: baseURL + '/customer/get-customer-is-member',
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
      url: baseURL + '/transaction/check-customer',
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


var form = document.querySelector('form');
form.addEventListener('submit', function(e) {
  var transactionDate = document.getElementsByName('transaction_date')[0];
  var date = new Date(transactionDate.value);
  var month = date.getMonth() + 1;
  var year = date.getFullYear();
  var currentMonth = new Date().getMonth() + 1;
  var currentYear = new Date().getFullYear();
  if (month != currentMonth || year != currentYear) {
    e.preventDefault();
    // alert yes or no
    var r = confirm("Transaction date is not equal to current month");
    if (r == true) {
      form.submit();
    } else {
      transactionDate.style.color = 'black';
      window.location.reload();
    }
  }
});


crud.field('product_id').onChange(function(field) {
  var discountPercentage = document.getElementsByName('discount_percentage')[0];
  var discountAmount = document.getElementsByName('discount_amount')[0];
  if (discountPercentage && discountAmount) {
    if (field.value && crud.field('quantity').value) {
      var url = baseURL + '/product/get-product?product_id=' + field.value;
      $.ajax({
        url: url,
        type: 'POST',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content'),
        },
        success: function(data) {
          var price =  data.netto_price * crud.field('quantity').value;
          var discount = price * discountPercentage.value / 100;
          discountAmount.setAttribute('value', discount);
        }
      });
    }
  }
}).change();

crud.field('quantity').onChange(function(field) {
  var discountPercentage = document.getElementsByName('discount_percentage')[0];
  var discountAmount = document.getElementsByName('discount_amount')[0];
  if (discountPercentage && discountAmount) {
    if (field.value && crud.field('product_id').value) {
      $.ajax({
        url: baseURL + '/product/get-product?product_id=' + crud.field('product_id').value,
        type: 'POST',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          var price =  data.netto_price * crud.field('quantity').value;
          var discount = price * discountPercentage.value / 100;
          discountAmount.setAttribute('value', discount);
        }
      });
    }
  }
}).change();

crud.field('is_nsi').onChange(function(field) {
    if (field.value == 1) {
        crud.field('nsi').show();
    } else {
        crud.field('nsi').hide();
    }
})
