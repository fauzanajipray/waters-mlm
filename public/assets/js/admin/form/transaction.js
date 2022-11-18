var buttonAdd = null;

crud.field('member_id').onChange(function(field) {
  // console.log(crud.field('customer'));
  if (field.value) {
      crud.field('customer').show();
      // MODIFY URL VALUE AJAX
      var customer = document.querySelector('select[name="customer"]');
      var url = customer.getAttribute('data-data-source');
      url += '?member_id=' + field.value;
      customer.setAttribute('data-data-source', url);

      // Get button add
      // var button = $('.inline-create-button');
      // console.log(button[0]);
      

      // // if inline create show
      // var customer_inline = document.querySelector('input[id="inline-create-dialog"]');
      // if (customer_inline) {
      //   console.log("Show inline create");
      // }


      // // MODIFY MEMBER ID MODAL
      // var elmt = document.getElementsByName('member_id');
      // console.log(elmt[0]);
      // // elmt.setAttribute = ('value', field.value);
  } else {
      crud.field('customer').hide();
      // edit attribute
      var customer = document.querySelector('select[name="customer"]');
      var url = customer.getAttribute('data-data-source');
      url = url.replace(/member_id=\d+/, '');
      customer.setAttribute('data-data-source', url);
  }
}).change();

var inlineCreateDialog = document.querySelector('input[id="inline-create-dialog"]');
// console.log(inlineCreateDialog);
// $('#inline-create-dialog').on('shown', function (e) {
//   console.log("show inline create");
// });

if(inlineCreateDialog){
  alert("Element exists");
} else {
  alert("Element does not exist");
}

