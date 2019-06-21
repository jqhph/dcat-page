<?php

namespace Dcat\Page\Admin\Grid;

use Dcat\Admin\Admin;
use Illuminate\Contracts\Support\Renderable;

class CreateAppButton implements Renderable
{
    public function render()
    {
        Admin::style(
            <<<'CSS'
.popover {max-width:350px}
CSS
        );

        $this->setupScript();

        $label = '创建应用';

        return "<a id='create-cms-app' class='btn btn-success btn-sm'><i class=\"glyphicon glyphicon-plus-sign\"></i><span class='hidden-xs'> &nbsp;$label</span></a>";
    }

    protected function setupScript()
    {
        $submit = trans('admin.submit');

        $url = admin_base_path('dcat-page/create-app');

        Admin::script(
            <<<JS
            
$('#create-cms-app').popover({
    html: true,
    title: false,
    content: function () {
        return '<div class="form-group " style="margin-top:5px"><error></error><div class="input-group input-group-sm"><span class="input-group-addon"><i class="ti-pencil"></i></span><input type="text" class="form-control " placeholder="Application Name" name="name" ></div></div>'
        + '<button id="submit-create" class="btn btn-primary btn-sm waves-effect waves-light">{$submit}</button>'
    }
});

$('#create-cms-app').on('shown.bs.popover', function () {
    var errTpl = '<label class="control-label"><i class="fa fa-times-circle-o"></i> {msg}</label>';
    $('#submit-create').click(function () {
        var _name = $('input[name="name"]'),
            name = _name.val();
        
        if (!name) {
            return displayError(_name, 'The application name is required.');
        }
        if (!isValid(name) || name.indexOf('/') !== -1) {
            return displayError(_name, 'The "'+name+'" is not a valid application name, please input a name like "dcat-page".');
        }
        removeError(_name);
        
        $('.popover').loading();
        $.post('$url', {
            _token: LA.token,
            name: name,
        }, function (response) {
            $('.popover').loading(false);
        
           if (!response.status) {
               LA.error(response.message);
           } else {
               $('#create-cms-app').popover('hide');
           }
           
           $(document).one('pjax:complete', function () { // 跳转新页面时移除弹窗
                $('.content').prepend('<div class="row"><div class="col-md-12">'+response.content+'</div></div>');
           });
           LA.reload();
           
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
