 (function ($) {
	//下标数
    var adlenth=$(".switch-tab a").size();
    $.fn.extend({
        "nav": function (con) {
            var $this = $(this), $nav = $this.find('.switch-tab'), t = (con && con.t) || 3000, a = (con && con.a) || 500, i = 0, autoChange = function () {
                $nav.find('a:eq(' + (i + 1 === adlenth ? 0 : i + 1) + ')').addClass('current').siblings().removeClass('current');
                $this.find('.event-item:eq(' + i + ')').css('display', 'none').end().find('.event-item:eq(' + (i + 1 === adlenth ? 0 : i + 1) + ')').css({
                    display: 'block',
                    opacity: 0
                }).animate({
                    opacity: 1
                }, a, function () {
                    i = i + 1 === adlenth ? 0 : i + 1;
                }).siblings('.event-item').css({
                    display: 'none',
                    opacity: 0
                });
            }, st = setInterval(autoChange, t);
            $this.hover(function () {
                clearInterval(st);
                return false;
            }, function () {
                st = setInterval(autoChange, t);
                return false;
            }).find('.switch-nav>a').bind('click', function () {
                if($('.switch-tab>a').size() == 1) return false;
                var current = $nav.find('.current').index();
                //i = $(this).attr('class') === 'prev' ? current - 2 : current;
                if($(this).attr('class') === 'prev'){
                   i = current == 0 ? $('.switch-tab a').size() - 2 : current - 2;
                 }else{
                  i = current;
                }
                autoChange();
                return false;
            }).end().find('.switch-tab>a').bind('click', function () {
                i = $(this).index()-1;
                autoChange();
                return false;
            });
            return $this;
        }
    });
}(jQuery));