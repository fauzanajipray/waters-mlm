crud.field('member_type').onChange(function(field) {
  if (field.value == 'PERSONAL' || field.value == 'NIS') {
    crud.field('branch_id').hide();
    crud.field('branch_office_id').show();
  } else {
    crud.field('branch_id').show();
    crud.field('branch_office_id').hide();
  }
}).change();
