$(function(){
  var tips;
  $('.layui-tooltip').on({
    mouseenter:function(){
      var that = this;
      var tipMessage = $(that).attr('tooltip') || '请设置提示信息';
      var tootipDirection = $(that).attr('tootipDirection') || 2;
      var tip = '<span style=\'color:#fff;\'>' + tipMessage + '</span>';

      tips = layer.tips(
        tip,
        that,
        {
          tips : [tootipDirection, '#436caa'],
          time:0,
          area: 'auto',
          maxWidth : 500
        }
      );
    },
    mouseleave:function(){
      layer.close(tips);
    }
  });

  $workForm = $('.search-work').find('#ArticleForm');
  $formAction = $workForm.attr('action');
  $submitButton = $workForm.find('.layui-btn');
  $originWorks = $('.cms_case_jiugu_list ').html();

  console.log(window.location.origin + '/article/ajax_select_work.html');
  $submitButton.on('click', function () {
    event.preventDefault();
    $formValue1 = $workForm.find("input[type='text']").val();
    $formValue2 = $workForm.find('.filter_menu_id').attr('value');

    if ($formValue1) {
      $.post("/article/ajax_select_work.html",{
        title : $formValue1,
        menu_id: $formValue2
      }, function(res){
        var result = JSON.parse(res);
        var html = '';
        if (result.result === 'success') {
          $('.cms_case_jiugu_list').html(result.data);
        }
      })
    } else {
      $('.cms_case_jiugu_list').html($originWorks);
    }
  });
})
