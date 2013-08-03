/**
 * BuddyPress Wall Javascript
 */


BP_DTheme['my_favs']  = BPWALL_DTheme['my_favs'];
BP_DTheme['mark_as_fav']  = BPWALL_DTheme['mark_as_fav'];
BP_DTheme['remove_fav']  = BPWALL_DTheme['remove_fav'];

jQuery(document).ready(function(){
	var jq=jQuery;

	jq("form.ac-form textarea").val('');
	jq(".has-comments form.ac-form").show();

	//comment text area autogrow
	jq('.ac-textarea textarea').autosize();
 
	/** add remove active class on focus on textarea */
	jq(document).on('focus','.ac-textarea textarea', function(){
	     var ac_form=jq(this).parent().parent().parent();//parent form
	         ac_form.addClass('active');
			
	         jq('.ac-textarea').parents('form.ac-form').not(ac_form).removeClass('active');       
	});

   /** Handle the ESC/ENTER key in comment textarea */
	jq(document).on('keydown','.ac-textarea textarea', function(e) {
		element = e.target;

	    if( e.ctrlKey == true || e.altKey == true || e.metaKey == true )
			return;

        var keyCode = (e.keyCode) ? e.keyCode : e.which;
        //if ESC key was pressed
       if ( keyCode == 27 ) {
       		jq(element).val('');
            jq(element).animate({'height':'18px'});//reset back to its original height
            
            return false;              
        //if Enter pressed
        } else if(keyCode==13) {
            bp_wall_post_comment(element);
            return false;
        }                    
	});
  
   /** new post activity comment */
    function bp_wall_post_comment(target){
    	target=jq(target);
   
    	/* Activity comment posting */
		if ( target.hasClass('ac-input') ) {
			var form = target.parent().parent().parent();
			var form_parent = form.parent();
			var form_id = form.attr('id').split('-');

			if ( !form_parent.hasClass('activity-comments') ) {
				var tmp_id = form_parent.attr('id').split('-');
				var comment_id = tmp_id[1];
			} else {
				var comment_id = form_id[2];
			}

			jq( 'form#' + form.attr('id') + ' div.error').hide();

			target.next('.loader').addClass('loading').end().prop('disabled', true);

			if (typeof bp_get_cookies == 'function')
				var cookie = bp_get_cookies();
	    	else 
	    		var cookie = encodeURIComponent(document.cookie);
   
   			var ajaxdata = {
				action: 'new_activity_comment',
				'cookie': cookie,
				'_wpnonce_new_activity_comment': jq("input#_wpnonce_new_activity_comment").val(),
				'comment_id': comment_id,
				'form_id': form_id[2],
				'content': jq('form#' + form.attr('id') + ' textarea').val()
			}; 	

			// Akismet
			var ak_nonce = jq('#_bp_as_nonce_' + comment_id).val();
			if ( ak_nonce ) {
				ajaxdata['_bp_as_nonce_' + comment_id] = ak_nonce;
			}

			jq.post( ajaxurl, ajaxdata, function(response) {
				target.next('.loader').removeClass('loading');

				/* Check for errors and append if found. */
				if ( response[0] + response[1] == '-1' ) {
					form.append( response.substr( 2, response.length ) ).hide().fadeIn( 200 );
				} else {
					form.fadeOut( 200,
						function() {
							if ( 0 == form.parent().children('ul').length ) {
								if ( form.parent().hasClass('activity-comments') )
									form.parent().prepend('<ul></ul>');
								else
									form.parent().append('<ul></ul>');
							}

							form.parent().children('ul').append(response).hide().fadeIn( 200 );
							form.children('textarea').val('');
							form.parent().parent().addClass('has-comments');
						}
					);//form hiding
					jq( 'form#' + form.attr('id') + ' textarea').val('');
                                        target.height(20);
                                      //  form.removeClass('active');
                                        form.fadeIn(200);
                                        
					/* Increase the "Reply (X)" button count */
					jq('li#activity-' + form_id[2] + ' a.acomment-reply span').html( Number( jq('li#activity-' + form_id[2] + ' a.acomment-reply span').html() ) + 1 );
				}

				jq(target).prop("disabled", false);
			});

			return false;
		}
	}

	jq('div.activity .acomment-reply').click( function(event) {
			var target = jq(event.target);
			
	                var id = target.attr('id');
	                ids = id.split('-');

	                var a_id = ids[2]
	                var c_id = target.attr('href').substr( 10, target.attr('href').length );
	                var form = jq( '#ac-form-' + a_id );

	                form.css( 'display', 'none' );
	                form.removeClass('root');
	                //jq('.ac-form').hide();
	               
	                form.children('div').each( function() {
	                        if ( jq(this).hasClass( 'error' ) )
	                                jq(this).hide();
	                });

	                if ( ids[1] != 'comment' ) {
	                        jq('div.activity-comments li#acomment-' + c_id).append( form );
	                } else {
	                        jq('li#activity-' + a_id + ' div.activity-comments').append( form );
	                }

	                if ( form.parent().hasClass( 'activity-comments' ) )
	                        form.addClass('root');

	                form.slideDown( 200 );
	                jq.scrollTo( form, 500, { offset:-100, easing:'easeOutQuad' } );
	                jq('#ac-form-' + ids[2] + ' textarea').focus();

	                return false;
			
	});
});