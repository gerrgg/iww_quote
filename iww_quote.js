jQuery(document).ready(function( $ ){

  $('form.variations_form').change(function(){
    var id = $('.variation_id').val();
    if( id.length ){
      // $('#bulk-quote-link').attr('href', 'http://d.iwantworkwear.com/large-quantity/?id=' + id);
    }
  });

  var inputs = $('#bulk_quote_table').find( 'input' );
  inputs.blur(function(){
    var total = 0;
    inputs.each(function(){
      total += +$(this).val();
    });
    $( '#quote_total' ).html( total );
  });

});
