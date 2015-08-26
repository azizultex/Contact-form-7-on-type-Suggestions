(function($) {
 
    /** jQuery Document Ready */
    $(document).ready(function(){
 
        $( '.ajax-suggest' ).on( 'input', function( e ) {
 
            /** Prevent Default Behaviour */
            e.preventDefault();
 
            /** Get last word from message value*/
            var srch_txt = $(this).val().trim().split(" ");
			var srch_txt = srch_txt.pop();
			
            /** Ajax Call */
            $.ajax({
 
                cache: false,
                timeout: 8000,
                url: the_ajax_script.ajaxurl,
                type: "POST",
                data: ({ action:'ajax_search', srch_txt:srch_txt }),
 
                beforeSend: function() {                    
                    $( '.suggestions' ).html( 'Suggestions loading...' );
                },
 
                success: function( data ){
                    var $ajax_response = $( data );
						$ajax_response.hide();
						$( '.suggestions' ).html( $ajax_response );
						$ajax_response.fadeIn(500);	
                },
 
                error: function( jqXHR, textStatus, errorThrown ){
                    console.log( 'The following error occured: ' + textStatus, errorThrown );   
                }
 
            });
 
        });
 
    });
 
})(jQuery);