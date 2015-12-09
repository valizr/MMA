$(document).load(function(){
	
	
	$('.iframeSocial').fancybox({
		maxWidth	: 800,
		maxHeight	: 800,
		fitToView	: false,		
		autoSize	: true,
		closeClick	: false,
		openEffect	: 'elastic',
		closeEffect	: 'elastic'			
	});
});

function resizeHTML(){
	$('.main').css('width', '100%').css('width', '-=260px');
	$('.map').css('min-height', $(this).height()-149+'px');
	$('.sidebar').css('height', $('.map').height());
	$('.sidebar .top').css('height', $('.sidebar').height()-73+'px');
	$('.pdRight').css('width', ($('.poiDet').width()-$('.pdLeft').width()-30)+'px');	
}