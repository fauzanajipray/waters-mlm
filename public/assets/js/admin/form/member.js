console.log("Member Js Loaded");

crud.field('member_type').onChange(function(field) {
  if (field.value == 'DEFAULT') {
    crud.field('branch_id').hide();
  } else {
    crud.field('branch_id').show();
  }
}).change();

// crud.field('office_type').onChange(function(field) {
//   if (!field.value) {
//     crud.field('branch_office_id').hide();
//   } else {
//     crud.field('branch_office_id').show();
//   }
// }).change();