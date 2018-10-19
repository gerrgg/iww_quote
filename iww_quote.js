jQuery(document).ready(function( $ ){
  var inputs = $('#bulk_quote_table').find( 'input' );
  inputs.blur(function(){
    var total = 0;
    inputs.each(function(){
      total += +$(this).val();
    });
    $( '#quote_total' ).html( total );
  });

  var $loc_checkboxes = $('.custom-loc');
  $loc_checkboxes.change(function(){
    checked = this.checked;
    loc = this.value;
    ( checked ) ? create_placement_form( loc ) : remove_placement_form( loc );
  });

  function create_placement_form( loc ){
    $form = $('<div/>', {
      id: 'loc-' + loc,
      class: 'form-group card my-2 p-3'
    }).appendTo('#customize-loc');

    $form.append('<h6 class="my-1">' + jsUcfirst(loc) + ' Placement</h6>');
    $form.append('<label>Are you adding a logo or text?</label>');
    $form.append( create_radio_button( loc, 'logo', 'Company Logo / Design' ) );
    $form.append( create_radio_button( loc, 'text', 'Basic Text' ) );

    $placement = $('<div/>', {
      id: 'placement-' + loc,
      class: 'form-group'
    }).appendTo($form);

    $form.on( 'change', 'input[type=radio]:checked', function(){
      var value = this.value;
      var opp = ( value == 'text' ) ? 'logo' : 'text';
      var loc = this.id;
      console.log(loc);
      $('#placement-' + loc).append( custom_form( value, loc ) );
      $('#'+ loc +'-custom-'+ opp +'-form').remove();
    } );
  }

  function custom_form( type, loc ){
    $form = $('<div/>', {
      id: loc +'-custom-'+ type +'-form',
      class: 'form-group my-1'
    });
    $form.append( ( type == 'text' ) ? get_text_options() : get_logo_options() );
    return $form;
  }

  function jsUcfirst(string)
  {
      return string.charAt(0).toUpperCase() + string.slice(1);
  }

  function get_text_options(){
    $text = $('<div/>', {
      id: 'text_form_options',
      class: 'form-group'
    });
    $comments = $('<textarea/>', {
      id: 'comments',
      name: 'comments',
      class: 'form-control'
    });

    $text.append( '<br><label for="text">What text would you like printed?</label>' );
    $text.append( '<input type="text" name="text" id="text" class="form-control">' );
    $text.append( '<br><label>Placement Comments: </label>' );
    $text.append( $comments );
    return $text;
  }

  function get_logo_options(){
    $upload = $('<div/>', {
      id: 'logo_form_options',
      class: 'form-group'
    });
    $file_input = $('<input/>', {
      type: 'file',
      id: 'logo',
      name: 'logo',
      class: 'my-3'
    })
    $comments = $('<textarea/>', {
      id: 'comments',
      name: 'comments',
      class: 'form-control'
    });
    $upload.append( $file_input );
    $upload.append( '<br><br><label>Placement Comments: </label>' );
    $upload.append( $comments );
    return $upload;
  }

  function remove_placement_form( loc ){
    $('#loc-' + loc).remove();
  }

  function create_radio_button(name, value, text = value){
    $wrapper = $('<div/>', {
      class: 'form-check'
    });
    $radio = $('<input/>', {
      class: 'form-check-input',
      style: 'margin-top: .3rem',
      type: 'radio',
      id: name,
      name: name,
      value : value
    });
    $label = $('<label/>', {
      class: 'form-check-label',
      for: name
    });
    $label.append(text);
    $wrapper.append($radio);
    $wrapper.append($label);
    return $wrapper;
  }

});
