$(document).ready(function(){
}).on('keydown', function(e){
	if(e.keyCode === 13 && $('.modal').hasClass('show')) e.preventDefault();
}).on('click', '.check-btn', function(){

	$ip = $('input[name=modal_ip]').val();

	if(IPvalidationCheck($ip)){
		$.ajax({
			type: 'GET',
			dataType: 'json',
			// url: 'https://extreme-ip-lookup.com/json/' + $ip + '?key=PvE6F2vw1kVEUTJYtbx6',
			url: 'http://127.0.0.8/ipcheck/ip_lookup/' + $ip,
			async: false,
			success: function(result) {
				if(result.status == 'success'){
					console.log(result.status);
					if(result.country == ''){
						$('input[name=modal_ip_nation]').val('');
						alert('IP에 맞는 국가가 없습니다.');
					}else{
						$('input[name=modal_ip_nation]').val(result.country);
					}
				}else{
					console.log(result.status);
					$('input[name=modal_ip_nation]').val('');
					alert('IP에 맞는 국가가 없습니다.');
				}
				
			},
			error: function(XMLHttpRequest, textStatus, errorThrow) {
				console.log("error XMLHttpRequest", XMLHttpRequest, ' textStatus ', textStatus);
				val = XMLHttpRequest.responseText;
				console.log(val);
			}
		});
	}else{
		$('input[name=modal_ip_nation]').val('');
		alert('IP 형식이 맞지 않습니다.');
	}
}).on('click', '.save-btn', function(){
	$('#ipModalForm').submit();
});

function IPvalidationCheck(ip = '') {
var REGEXP_IPV4_IPV6_ADDR = /((^\s*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\s*$)|(^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$))/;

	if(REGEXP_IPV4_IPV6_ADDR.test(ip)){
		return true;
	}else{
		return false;
	}
}