jQuery(document).ready(function($) {
    if($('#picker').length != 0){
        var p = $('#picker').css('opacity', 1);
        var f = $.farbtastic('#picker');
        var selected;
        $('.colorwell')
          .each(function () { f.linkTo(this); $(this).css('opacity', 0.75); })
          .focus(function() {
            if (selected) {
              $(selected).css('opacity', 0.75).removeClass('colorwell-selected');
            }
            f.linkTo(this);
            p.css('opacity', 1);
            $(selected = this).css('opacity', 1).addClass('colorwell-selected');
          });
          rampColors();
          $('#color_ramp').on("keyup", rampColors);
    }
});

var $jq = jQuery.noConflict();
  
function rampColors(){
	var colors = $jq('#color_ramp').val().split(';');
	$jq('#rampPreview').html('<p>Preview:</p>');
	$jq.each(colors, function(i, val) {
		if (val != null && val.trim() != ''){
			$jq('#rampPreview').append('<svg width="30" height="50"><rect width="30" height="50" style="fill:'+val.trim()+';stroke-width:0.2;stroke:rgb(0,0,0)" /></svg>');
		}
	})
}
