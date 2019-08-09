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
})
