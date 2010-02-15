/*$(document).ready(function(){
	$('.abutton').each(function(){
		var b = $(this);
		var tt = b.text() || b.val();
		if ($(':submit,:button',this)) {
			b = $('<a>').insertAfter(this). addClass(this.className).attr('id',this.id);
			$(this).remove();
		}
		b.text('').css({cursor:'pointer'}). prepend('<i></i>').append($('<span>').
		text(tt).append('<i></i><span></span>'));
	});
});*/