jQuery(function($) {
  $(document).ready(function(){
    
    // show and hide form on button click
    var form = $('#wpccontactform');
    $('#wpcshowformbtn').click(function(){
      if(form.css('display') === 'none'){
        form.show(500);
      } else {
        form.hide(500);        
      }
    });
    
    // show form if contact is to be edited
    var edit = $('#wpcedit');
    if(edit.val() === 'Edit'){
      form.show(500);
    }
  });
});

