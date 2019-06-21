<?php

namespace Dcat\Page\Admin\Grid;

use Dcat\Admin\Admin;
use Illuminate\Contracts\Support\Renderable;

class CompileButton implements Renderable
{
    public function render()
    {
        $this->setupScript();

        return "<a class='compile-app btn btn-primary btn-sm'><i class='ti-write'></i><span class='hidden-xs'>&nbsp; 编译</span></a>&nbsp; ";
    }

    protected function setupScript()
    {
        $submit = trans('admin.submit');

        $url = admin_base_path('dcat-page/compile-app');

        Admin::script(
            <<<JS
            
$('.compile-app').popover({
    html: true,
    title: false,
    placement: 'bottom',
    content: function () {
        return '<div class="form-group " style="margin-top:5px"><error></error><div class="input-group input-group-sm"><span class="input-group-addon"><i class="ti-pencil"></i></span><input type="text" class="form-control " placeholder="Dir" name="dir" ></div></div>'
        + '<button id="submit-create" class="btn btn-primary btn-sm waves-effect waves-light">{$submit}</button>'
    }
});

$('.compile-app').on('shown.bs.popover', function () {
    var errTpl = '<label class="control-label"><i class="fa fa-times-circle-o"></i> {msg}</label>';
    var name = $(this).data('app');
    
    $('#submit-create').click(function () {
        var _dir = $('input[name="dir"]'),
            dir = _dir.val();
        
        if (dir && (!isValid(dir) || dir.indexOf('/') !== -1)) {
            return displayError(_dir, 'The "'+dir+'" is not a valid dir name, please input a name like "dcat-page".');
        }
        removeError(_dir);
        
        $('.popover').loading();
        $.post('$url', {
            _token: LA.token,
            dir: dir,
            name: name,
        }, function (response) {
            $('.popover').loading(false);
        
           if (!response.status) {
               LA.error(response.message);
           } else {
               $('.compile-app').popover('hide');
           }
           
           $('.content').prepend('<div class="row"><div class="col-md-12">'+response.content+'</div></div>');
        });
        
    });
    
    function displayError(obj, msg) {
        obj.parents('.form-group').addClass('has-error');
        obj.parents('.form-group').find('error').html(errTpl.replace('{msg}', msg));
    }
    
    function removeError(obj) {
        obj.parents('.form-group').removeClass('has-error');
        obj.parents('.form-group').find('error').html('');
    }
    
    function isValid(str) { 
        return /^[\w-\/\\\\]+$/.test(str); 
    }
    
});

JS
        );
    }
}
