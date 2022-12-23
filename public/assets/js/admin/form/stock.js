
var baseURL = crud.field('url').value;

crud.field('branch_id').onChange(function(field) {
  if (field.value) {
    if(field.value != 1){
      crud.field('origin_branch_id').show();
    } else {
      crud.field('origin_branch_id').hide();
    }
  } else {
    crud.field('origin_branch_id').hide();
  }
}).change();

crud.field('product_id').onChange(function(field) {
  if (field.value) {
    if(crud.field('branch_id').value != 1){
      crud.field('product_stock').show();
      var product_stock = document.getElementsByName('product_stock')[0];
      var quantity = document.getElementsByName('quantity')[0];
      $.ajax({
        url: baseURL+'/product/'+field.value+'/branch/'+crud.field('origin_branch_id').value,
        type: 'GET',
        success: function(data) {
          product_stock.setAttribute('value', data);
          quantity.setAttribute('max', data);
          quantity.setAttribute('min', 1);
        }
      });
    } 
  } else {
    crud.field('product_stock').hide();
  }
}).change();