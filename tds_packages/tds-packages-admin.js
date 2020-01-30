jQuery(function($) {


$(document).ready(function() {


  // Add Color Picker to all inputs that have 'color-field' class

  $('.cpa-color-picker').wpColorPicker();


  //function to show or hide link or upload
  function ShowHideLinkType() {
   //get the value of the button clicked
   var linkTypeValue = $(this).val();

   if(linkTypeValue == 'url') {
     //hide the PDF upload and show the link field
     //Find the containing row for it
     var rowToHide = $('#tds_package_pdf_itinerary').closest('tr');
     var rowToShow = $('#tds_package_page_url').closest('tr')
    //  rowToHide.fadeOut();
  } else if(linkTypeValue == 'pdf') {
    //hide the link field and show the PDF upload
    //Find the containing row for it
    var rowToHide = $('#tds_package_page_url').closest('tr')
    var rowToShow = $('#tds_package_pdf_itinerary').closest('tr');
  }
  rowToHide.fadeOut(function() {
     rowToShow.fadeIn()
   }); //end rowToHide.fadeOut(function()
  } //end $("input[name$='tds_package_link_type[]']").click(function()

  //show or hide the sections based on clicking the radio button
  $("input[name='tds_package_link_type']").on('click', ShowHideLinkType );

  //Show or hide on page load
  $("input[name='tds_package_link_type']:checked").trigger( "click" );

}); //end $(document).ready(function()


});
