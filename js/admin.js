jQuery(function($){
  
  var $targetId = '#the-list';
  
  $($targetId).sortable({ 
    handle: '.menu_order_custom',
	cursor: 'move',
	axis: 'y',
	containment: $($targetId).parent(),
	helper: function(e, tr) {
	  var $originals = tr.children();
	  var $helper = tr.clone();
	  $helper.children().each(function(index){
		$(this).width($originals.eq(index).width());
	  });
	  return $helper;
	},
	update: function( event, ui ) {
	  refreshlist();
	  orderaction();
	}

  }).disableSelection();
  
  function refreshlist() {
    $('tr:odd', $targetId).removeClass('alternate');
    $('tr:even', $targetId).addClass('alternate');
  }
  
  function orderaction() {

    //console.log( $($targetId).sortable('toArray') );

	var $xhr = $.ajax({
	  type :'post',
	  url : ajaxurl,
	  data : 'action=order_hook&data=' + $($targetId).sortable('toArray'),
	  cache:false,		
	});
	
	setTimeout(function() { $xhr.abort(); }, 200);

  }


});