$(document).on("click", ".btn-delete-customer", function () {
  console.log("Delete Customer Clicked");
  var link = $(this).attr("href");
  //Sweet Alert for update action publish
  Swal.fire({
    title: "Are you sure?",
    text: "You won't be able to revert this!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "No, cancel!",
  }).then((result) => {
    if (result.value) {
      window.location.href = link;
    }
  });

  return false;
});