
require('../vendor/slimscroll.js');

function init() {
    var options = {
            min_width: '1440px',
            max_layer: 3,
            left: 85,
        },
        $container = $('body article'),
        counter = {};

    if ($container.find('.the-404').length) {
        return;
    }

    var h2s = $container.find('h2');
    var h3s = $container.find('h3');
    var h4s = $container.find('h4');
    var h5s = $container.find('h5');

    var titles = {h2: h2s.length, h3: h3s.length, h4: h4s.length, h5: h5s.length},
        hTags = [];
    for (var i in titles) {
        if (titles[i] > 0 && hTags.length < options.max_layer) {
            hTags.push(i);
            counter[i] = 1;
        }
    }

    if (!hTags.length) {
        return;
    }

    function build () {
        // <li style="padding:0 0 0.9rem  0.9rem;color:#a0aec0"><span><i class="fa fa-align-right"></i> MENU</span></li>
        $('body').prepend('<div class="toc">' +
            '<div class="toc-content" id="toc-content"> </div>' +
            '</div>');

        var $toc = $('.toc'),
            $tocContent = $('#toc-content');

        function setup_container() {
            if (!window.matchMedia(`(min-width:${options.min_width})`).matches) {
                $toc.hide();
                $tocContent.hide();
                return;
            }
            $toc.show();
            $tocContent.show();

            options.top = $container.offset().top + 10;
            var left = $container.offset().left + $container.width() + options.left;

            $toc.css({top: top + 'px', left: left+ 'px', display: 'block'});

            $toc.attr('data-top', top);

            var height = ($(window).height() - 150) + 'px',
                heightObj = {
                    height: height
                };
            $tocContent.slimScroll(heightObj);
            $tocContent.css('max-height', height);
            $('.slimScrollDiv').css(heightObj);
        }

        $container.find('h1,h2,h3,h4,h5').each(function (i, item) {
            var id = '',
                tag = $(item).get(0).tagName.toLowerCase(),
                className = '',
                text = $(this).find('a').html() || $(this).html();

            // 添加页面标题
            if (tag == 'h1') {
                id = 'h1-0';
                className = 'item-h0';

                $(item).attr('id', 'target' + id);
                $(item).addClass('target-name');

                $tocContent.append('<li><a class="nav-item ' + className + ' anchor-link" onclick="return false;" href="#target' + id + '" link="#target' + id + '">' + text + '</a></li>');
            }

            hTags.forEach(function (title, i) {
                if (tag != title) {
                    return;
                }
                i++;
                counter[tag]++;

                id = title + '-' + i + '-' + counter[tag];
                className = 'item-h' + i;

                $(item).attr('id', 'target' + id);
                $(item).addClass('target-name');

                $tocContent.append('<li><a class="nav-item ' + className + ' anchor-link" onclick="return false;" href="#target' + id + '" link="#target' + id + '">' + text + '</a></li>');

            });

            setup_container();
        });

        $(window).on('resize', setup_container);

        $toc.find('.anchor-link').click(function () {
            $('html,body').animate({scrollTop: $($(this).attr('link')).offset().top}, 500);
        });

        var tocNavs = $toc.find('li .nav-item');
        var tocTops = [];
        var scrollable = false;

        $('.target-name').each(function (i, n) {
            tocTops.push($(n).offset().top);
        });

        // 标题点击事件
        tocNavs.click(function () {
            var $li = $(this).closest('li');

            // 标记选中的标题项
            $toc.find('li').removeClass('____');
            $li.addClass('____');

            // 添加默认选中效果
            setTimeout(function () {
                if (scrollable) {
                    return;
                }
                scrollable = false;

                $toc.find('li').removeClass('active');
                $li.addClass('active');
            }, 180);
        });

        // 滚动选中
        $(window).scroll(function () {
            let scrollTop = $(window).scrollTop(),
                timer;
            scrollable = true;

            $.each(tocTops, function (i, n) {
                var distance = n - scrollTop,
                    $item = $(tocNavs[i]).closest('li');

                if (distance >= 0) {
                    $tocContent.find('li').removeClass('active');
                    $item.addClass('active').removeClass('____');
                    return false;
                }
            });

            if (scrollTop == 0) {
                $tocContent.animate({scrollTop: 0}, 100);
            }
            if (scrollTop + $(window).height() == $(document).height()) {
                $tocContent.animate({scrollTop: $tocContent.height()}, 100);
            }

            clearTimeout(timer);
            timer = setTimeout(function () {
                if (! isScrollEnd(scrollTop)) {
                    return;
                }

                // 滚动结束后自动选中标记过的标题
                $.each($tocContent.find('li'), function (k, v) {
                    let li = $(v);
                    if (li.hasClass('____')) {
                        $tocContent.find('li').removeClass('active');
                        li.addClass('active').removeClass('____');
                    }
                });

                scrollable = false;
            }, 100);
        });

        $(window).scroll(function() {
            var scroH = $(document).scrollTop(),
                style = {
                    top: '20px',
                };

            if (scroH <= 80) {
                style = {
                    top: options.top + 'px',
                };
            }

            $toc.css(style);
        });

        function isScrollEnd(top) {
            return top == $(window).scrollTop();
        }

        $toc.find('li').eq(0).addClass('active');
    }

    build();
}

export {init}
