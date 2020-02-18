jQuery(function($) {


  $(document).ready(function() {

    // Add Color Picker to all inputs that have 'color-field' class

    $('.cpa-color-picker').wpColorPicker();

    console.log('mama miiiia');
    /* Handle an additional level of radio buttons on the CPT */

    //show or hide the sections based on clicking the radio button
    $('input[class^="switch_"][type="radio"],input[class*=" switch_"][type="radio"]').on('click', SwitchShowHideradioType );

    //Show or hide on page load
    $('input[class^="switch_"][type="radio"]:checked,input[class^=" switch_"][type="radio"]:checked').trigger( 'click' );

        //show or hide the sections based on clicking the radio button
        $('input[class^="toggle_"][type="radio"],input[class=" toggle_"][type="radio"]').on('click', SwitchShowHideradioType );

        //Show or hide on page load
        $('input[class^="toggle_"][type="radio"]:checked,input[class^=" toggle_"][type="radio"]:checked').trigger( 'click' );
  }); //end $(document).ready(function()


  function SwitchShowHideradioType(element) {
    console.log('pussy and tits');
  }

});
