<?php

namespace Dcat\Page\Admin\Grid;

use Dcat\Admin\Admin;
use Illuminate\Contracts\Support\Renderable;

class IndexButton implements Renderable
{
    public function render()
    {
        $this->setupScript();

        return "<a class='index-app btn btn-primary btn-sm'><i class='ti-server'></i><span class='hidden-xs'>&nbsp; 索引</span></a>&nbsp; ";
    }

    protected function setupScript()
    {
        $url = admin_base_path('dcat-page/index-app');

        Admin::script(
            <<<JS

$('.index-app').on('click', function () {
    var name = $(this).data('app'), self = $(this);
    
    LA.loading();
    $.post('$url', {
        _token: LA.token,
        name: name,
    }, function (response) {
         LA.loading(false);
    
       if (!response.status) {
           LA.error(response.message);
       }
       
       $('.content').prepend('<div class="row"><div class="col-md-12">'+response.content+'</div></div>');
    });
    
});

JS
        );
    }
}
