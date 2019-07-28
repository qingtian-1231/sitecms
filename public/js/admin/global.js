$(document).ready(function(){
    $(document).on('click', 'a.layer-ajax-form', function() {
        var href = $(this).attr('href');
        var title = $(this).data('title') ? $(this).data('title') : $(this).html();
        if (!is_layer_loading) {
            return true;
        }
        if ($(window).width() < 1000) {
            return true;
        }
        layer.open({
            type : 2,
            title : title,
            skin: 'frame-form',
            maxmin :true,
            content : href,
            area: [($(window).width() * 0.8 <= 1000 ? $(window).width() * 0.8 : 1000) + 'px', ($(window).height() * 0.9 <= 650 ? $(window).height() * 0.9 : 650) + 'px'],
            btn: ['确定', '重置'],
            yes: function(index, layero){
                layero.find("iframe").get(0).contentWindow.$('form').trigger('submit');
                
                /*
                setTimeout(function(){
                    layer.close(index);
                    location.reload();
                },5)
                */
            }
            ,btn2: function(index, layero){
                layero.find("iframe").get(0).contentWindow.$('form')[0].reset();
                return false;
            }
        })
        
        return false;
    })
    
    if (self != top) {
        if (parent.$('.layui-layer-iframe').length >= 1) {
            $('body').addClass('layer-iframe-loading');
            $('html').height('auto');
            $('.admin_main').css({
                'padding-top' : 15,
                'padding-bottom' : 0
            });
            $('.load-frame-hidden').hide();
        }
    }
    
	$(document)
		.on('click','.new_tab',function(ev){
		 
            var title=$(this).attr('data-title') ? $(this).attr('data-title') : $(this).text();
            var href=$(this).attr('href');
            var icon  =  $(this).attr('data-icon') ? $(this).attr('data-icon') :  $(this).find('i').attr('data-icon'); 
              
            if(parent && parent.Tab){
            	parent.Tab.tabAdd({
                    title: title,
                    href : href,
                    icon : icon
                });
                top.$('#loadLine').find('i').stop(true, true).animate({
                    width : '50%'
                }, 800, function(){
                    var that = top.$('#loadLine').find('i');
                    that.delay(200).fadeOut(200, function(){
                        that.width(0).show();
                    })
                })
            }
            else{
            	Tab.tabAdd({
                    title: title,
                    href : href,
                    icon : icon
                });
                $('#loadLine').find('i').stop(true, true).animate({
                    width : '50%'
                }, 800, function(){
                    var that = $(this);
                    $(this).delay(200).fadeOut(200, function(){
                        that.width(0).show();
                    })
                })
            }
            
            if ( ev && ev.preventDefault ) ev.preventDefault(); else window.event.returnValue = false; return false;
    	})
    .on('click','.javascript',function(ev){
		var callback;

		if(callback=$(this).attr('rel')){
			if(window[callback]){
				window[callback].call(this);
			}
		}
		if ( ev && ev.preventDefault ) ev.preventDefault(); else window.event.returnValue = false; return false;
	});
        
    
    
    $('.tooltip').hover(function(){
        var text  = $(this).attr('data-tip-text')  ;
        var type  = $(this).attr('data-tip-type') ? $(this).attr('data-tip-type') : 2 ;
        var bg    = $(this).attr('data-tip-bg') ? $(this).attr('data-tip-bg') : '#393D49';
        var number = $(this).attr('data-tip-number') ? parseInt($(this).attr('data-tip-number')) : 0;
        if (number > 0) {
            var hovered = $(this).attr('data-tip-hovered') ? parseInt($(this).attr('data-tip-hovered')) + 1 : 1;
            $(this).attr('data-tip-hovered', hovered);
            if (hovered > number) {
                $(this).removeClass('tooltip');
                return false;
            }
        }
        
        if(text){
            layer.tips(text, $(this), {
                tips: [ type, bg],
                time : 0
            });
        }
    },function(){    
        layer.close(layer.tips()) ;
    });
    
    if ($('.admin_main .action').find('a').length < 1) {
        $('.admin_main .action').remove();
    }
    
});

var HKUC={
	nl2br:function (str){
		if(typeof(str)=='string')
			return str.replace(/\r?\n/g,'<br />');
		else
			return str;
	},
	
	parse_serial_array:function (input,cols_type){
		if(!cols_type)cols_type={}
		var tmp={};

		for(var i=0;i<input.length; i++){
			switch(cols_type[input[i].name]){
				case 'checker':
					input[i].value=!!parseInt(input[i].value);
					break;
				case 'integer':
					input[i].value=parseInt(input[i].value);
					break;
			}

			var eval_str='tmp.'+input[i].name;
			var append=false;

			if(eval_str.substr(eval_str.length-2)=='[]'){
				eval_str=eval_str.substring(0,eval_str.length-2);
				append=true;
			}

			eval_str=eval_str.replace(/\[/g,'["').replace(/\]/g,'"]');
			var checkpos=4;

			while((checkpos=eval_str.indexOf('[',checkpos))!==-1){
				if(!eval(eval_str.substr(0,checkpos))){
					eval(eval_str.substr(0,checkpos)+'={}');
				}
				checkpos+=1;
			}

			if(append){
				if(!eval(eval_str))eval(eval_str+'=[]');
				var max_index=eval('Array.prototype.push.call('+eval_str+',input[i].value)');
				if(!eval(eval_str+'.length'))
					eval(eval_str+'['+max_index+']=input[i].value')
			}
			else{
				eval(eval_str+'=input[i].value');
			}
		}

		return tmp;
	},
	
	isJsonValidate:function isJsonValidate(str){
		return str.match(/^(\[|\{).*(\}|\])$/);
	},
	
	default_successHandler:function (msg,data){
		if(msg)alert(msg);
		else alert('提交成功');//提交成功
		return true;
	},
	
	default_failHandler:function (msg,data){
		if(msg)alert(msg);
		else alert('提交失败');//提交失败
		return false;
	},
	
	ajax_request:function(url,data,successHandlers,errorHandlers){
		successHandlers=$.extend({},arguments.callee.defaultSuccessHandlers,successHandlers);
		errorHandlers=$.extend({},arguments.callee.defaultErrorHandlers,errorHandlers);
		
		return $.ajax({
			'url':url,
			'data':data,
			'type':data?'post':'get',
			'success':$.proxy(
				function(response){
					if(HKUC.isJsonValidate($.trim(response))){
						var rslt=eval('('+response+')');
                        
						if(this.handler[rslt.result]){
							return this.handler[rslt.result].call(this.self,rslt.message,rslt.data,this.run);
						}
						return false;
					}
					else{
						if(this.handler['_']){
							this.handler['_'].call(this.self,response,this.run);
						}
						else{
							alert(response);
						}
					}
				},
				{
					'self':this,
					'handler':successHandlers?successHandlers:{},
					'run':$.proxy(
						function(){
							return this.arguments.callee.apply(this.self,this.arguments);
						},
						{
							'arguments':arguments,
							'self':this
						}
					)
				}
			),
			'error':$.proxy(
				function(XMLHttpRequest, textStatus, errorThrown){
					if(this.handler[XMLHttpRequest.status]){
						return this.handler[XMLHttpRequest.status].call(this.self,errorThrown,this.run);
					}
					else if(this.handler['_']){
						return this.handler['_'].call(this.self,errorThrown,this.run);
					}
				},
				{
					'self':this,
					'handler':errorHandlers?errorHandlers:{},
					'run':$.proxy(
						function(){
							return this.arguments.callee.apply(this.self,this.arguments);
						},
						{
							'arguments':arguments,
							'self':this
						}
					)
				}
			)			
		})
	},
	
	imgFit:function (obj,width,height,shrink){
		var imageRate1=0,imageRate2=0;
		if(!obj)return;
		var temp_img = new Image();
		temp_img.src=obj.src;
		if(temp_img.width>width || temp_img.height>height)
		{
			if(width)imageRate1=temp_img.width/width;
			if(height)imageRate2=temp_img.height/height;

			if(height){
				if(width){
					if(imageRate2>imageRate1){
						obj.style.height = temp_img.height/imageRate2+"px";
						obj.style.width = 'auto';
					}
					else{
						obj.style.width = temp_img.width/imageRate1 +"px";
						obj.style.height = 'auto';
					}
				}
				else{
					obj.style.height = temp_img.height/imageRate2+"px";
					obj.style.width = 'auto';
				}
			}
			else{
				obj.style.width = temp_img.width/imageRate1 +"px";
				obj.style.height = 'auto';
			}
		}

		
		if(shrink && temp_img.height<=obj.offsetHeight && temp_img.width<=obj.offsetWidth){
			obj.style.height = temp_img.height+"px";
			obj.style.width = temp_img.width+"px";
		}
	},
	
	imgCache:function(url){
		if(!arguments.callee.cache)arguments.callee.cache=[];
		var temp_img = new Image();
			temp_img.src=url;
		arguments.callee.cache.push(temp_img);	
	},
	
	dummy:'dummy'
}


var ASSOC_OBJECT = null;

function assoc_select_load()
{
    var $this = $(this);
    var href  = $this.attr('href');
    ASSOC_OBJECT = $this;
    layer.closeAll();
    layer.alert('<div id="assoc_select_load"></div>', {
        area: ['400px', '460px']
        ,btn :false
        ,title: '关联模型数据选择'
        ,offset: $(window).width() > 1000 ? [$this.offset().top + 'px', ($this.offset().left + $this.outerWidth() + 10) + 'px'] : 'auto'
    });
    assoc_select_load_real(href);
}    

function assoc_select_load_real(url)
{
    $('#assoc_select_load').html('<div style="text-align:center;padding-top:140px;"><img src="'+wwwroot+'images/admin/ajax-loader.gif"></div>').load(url + ' .assoc_load', function(data) {
        
    });
}

function assoc_select_load_search()
{
    var href  = $(this).attr('href');
    var keyword = $.trim($(this).siblings('input').val());
    if (keyword) {
        href = href.replace('KEY_WORD_HOLDER', keyword)
    } else {
        href = href.replace('KEY_WORD_HOLDER', '')
    }
    assoc_select_load_real(href);
    return false; 
}

function assoc_selected()
{
    var id = $(this).attr('data-id');
    var value = $(this).attr('data-value');
    if (ASSOC_OBJECT) {
        ASSOC_OBJECT.siblings('input[type="hidden"]').val(id).end().siblings('.assoc_select_value').html(id + '=>' + value);
    }
    layer.closeAll();
}

$(document).on('click', '#assoc_select_load .pagination a', function(){    
    if (!$(this).hasClass('disabled-link')) {
        var href  = $(this).attr('href'); 
        assoc_select_load_real(href);
    }
    return false;
})

$(document).on('dblclick', '#assoc_select_load tbody tr', function(){
    $(this).find('a').trigger('click');
})

 

var multi_select = function(selector, data, default_value, mdl, fields, url){
    var back_this=this;
    this.data = data;
    this.field = selector;
    this.$container = $('.multi_select_' + selector);
    this.default_value = default_value;
    this.mdl = mdl;
    this.fields = fields;
    this.fileds_count = 0;
    this.url = url;
    
    for(i in this.fields) this.fileds_count++;
    
    var top_id = parseInt(back_this.data['data']['top_id']);
    
    layui.use(['form'], function(){
        back_this.form = layui.form;
        
        if (back_this.data.options !== 'ajax') {
            if (back_this.fileds_count) {
                
                if (back_this.default_value.length > 0) {
                    var family = back_this.default_value;
                    var index = 0;
                    for(i in family) {
                        back_this.show_options(family[i], family[parseInt(i)+1], index);
                        index++;
                    }
                } else {
                    back_this.show_options(top_id)
                }
            } else {
                back_this.default_value = parseInt(back_this.default_value);
                if (back_this.default_value > 0) {
                    var family = [];
                    var form_id = back_this.default_value;
                    
                    while(true) {
                        family.unshift(form_id);
                        if (form_id == top_id) break;
                        var form_id = back_this.data['list'][form_id]['parent_id'];
                    }
                    for(i in family) {
                        back_this.show_options(family[i], family[parseInt(i)+1])
                    }
                }  else {
                    back_this.show_options(top_id)
                }
            }
        } else {
            
            if (back_this.fileds_count) {                
                if (back_this.default_value.length > 1) { 
                    var family = back_this.default_value;
                    var index = -1;
                    for(i in family) {
                        if (index == -1) {
                            index++;
                            continue;
                        }
                        back_this.show_options(family[parseInt(i) -1], family[parseInt(i)], index);
                        index++;
                    }
                } else { 
                    back_this.show_options(top_id)
                }
            } else {
                if (back_this.default_value.length > 0) {
                    var family = back_this.default_value;
                    var index = 0;
                    for(i in family) {
                        back_this.show_options(family[i], family[parseInt(i)+1], index);
                        index++;
                    }
                } else {
                    back_this.show_options(top_id)
                }
            }
        }        
        
        back_this.form.on('select(multi_select_'+back_this.field+')', function(data){
           
            $(data.othis).nextAll().remove();            
            var val = '';
            if (data['value']) {
                back_this.show_options(data['value']);
                var val = data['value'];
            } else {
                if (typeof(data.othis.prevAll('.layui-form-select')) != 'undefined') {
                    val = $(data.othis.prevAll('.layui-form-select')[0]).find('dd.layui-this').attr('lay-value');
                }
            }
            if (typeof(val) == 'undefined') val = '';
            back_this.$container.siblings('input').val(val);
        });
    })
}

multi_select.prototype = {
    $setter:null,
	$container:null,
	default_value:0,
	data:null,
    form:null,
    field:null,
    fields:null,
    fileds_count:0,
    mdl:'',
    cache:{},
    show_options:function(parent_id, value, index){ 
        
        if (typeof(parent_id) == 'undefined') return false;
        
        var field_name = 0, field_title = '';
        if (this.fileds_count) { 
            if (typeof(index) != 'undefined') {
                select_count = index;
            } else {
                var select_count = this.$container.find('[lay-filter="multi_select_'+this.field+'"]').length;
            }
            
            if (select_count+1 > this.fileds_count) return false;
            var count = 0;
            for (i in this.fields) {
                field_name = i;
                field_title =  this.fields[i];
                if (count == select_count) {
                    break;
                }
                count++;
            }
        } else if(this.data.options !== 'ajax') {
            if (this.data['options'][parent_id].length <= 0) return false;
        }
        
        if (!field_name)
            var html = "<select lay-filter=\"multi_select_"+this.field+"\" data-index=\""+index+"\" class=\"multi_each\"><option value=\"\">≡请选择≡</option>";
        else 
            var html = "<select lay-filter=\"multi_select_"+this.field+"\" data-index=\""+index+"\" name=\"data["+this.mdl+"]["+field_name+"]\" class=\"multi_each\"><option value=\"\">≡请选择"+field_title+"≡</option>";
        
        if (this.data.options !== 'ajax') {
            for(i in this.data['options'][parent_id]) {
                var my_id = this.data['options'][parent_id][i]; 
                if (value != my_id) {
                    html += "<option value=\""+my_id+"\">"+ this.data['list'][my_id][this.data['data']['field']] +"</option>";
                } else {
                    html += "<option selected value=\""+my_id+"\">"+ this.data['list'][my_id][this.data['data']['field']] +"</option>";
                }
            }            
            this.$container.append(html);
            this.form.render('select'); 
        } else {
            
            var that = this;
            if (typeof(that.cache[parent_id]) == 'undefined') {
                HKUC.ajax_request(this.url, {parent_id : parent_id, field: this.field},
    				{
    					'success':function(msg,data){
    					    that.cache[parent_id] = data;
                            if (data.length == 0) return false;
    						for(i in data) {
    						    var my_id = data[i]['id'];
    						    if (value != my_id) {
                                    html += "<option value=\""+my_id+"\">"+ data[i]['title'] +"</option>";
                                } else {
                                    html += "<option selected value=\""+my_id+"\">"+ data[i]['title'] +"</option>";
                                }
    						}       
                                           
                            if (index >= 0) { 
                                if (index > 0) {
                                    var is_append = false;
                                    for (i = index-1;i >=0; i--) {
                                        if (that.$container.find('select[data-index="'+i+'"]').length) {
                                            $(html).insertAfter(that.$container.find('select[data-index="'+i+'"]'));
                                            is_append = true;
                                            break;
                                        }
                                    }
                                    if (!is_append) {
                                        that.$container.prepend(html);
                                    }
                                } else {
                                    that.$container.prepend(html);
                                }
                            } else {
                                that.$container.append(html);
                            }        
                            that.form.render('select'); 
    					}
    				}
    			);
            } else {
                var data = that.cache[parent_id];   
                if (data.length == 0) return false;             
                for(i in data) {
    			    var my_id = data[i]['id'];
    			    if (value != my_id) {
                        html += "<option value=\""+my_id+"\">"+ data[i]['title'] +"</option>";
                    } else {
                        html += "<option selected value=\""+my_id+"\">"+ data[i]['title'] +"</option>";
                    }
    			}                
                that.$container.append(html);
                that.form.render('select'); 
            }
        }
    }
}

function ckeditor_select(callback){
	var $this=$(this);

	var data_name=$this.attr('data');
	var url=$this.attr('href');
	if(!arguments.callee.hWnd || arguments.callee.hWnd.closed){
		arguments.callee.hWnd=window.open(url, 'ckeditor' ,' toolbar=no, menubar=no, resizable=no,location=no, status=no, fullscreen=no, width='+Math.floor(window.screen.availWidth*0.8)+', height='+Math.floor(window.screen.availHeight*0.7)+', left='+ Math.floor(window.screen.availWidth*0.1)+ ', top='+Math.floor(window.screen.availHeight*0.15)+'');
	}
	arguments.callee.hWnd.focus();
	
	if(!callback)callback=function(url, org_path){
	    
		if(url){
			var target=$this.attr('rev');
			$('#'+target).val(org_path);
            
            $('#'+target).prev('input[type="hidden"]').val('true').closest('.layui-input-inline').find('.form_elem_info').find('.form_file_hidden').remove().end().find('.img_show').find('img').attr('src', url).end().end().find('.file_show').attr('href', url)
		}
	}
	
	window['ckeditor_select_callback'] = callback;
}

function ckeditor_select_multi_image(callback) {
    var $this=$(this);
	var url = $this.attr('href');
	if(!arguments.callee.hWnd || arguments.callee.hWnd.closed){
		arguments.callee.hWnd=window.open(url, 'ckeditor' ,' toolbar=no, menubar=no, resizable=no,location=no, status=no, fullscreen=no, width='+Math.floor(window.screen.availWidth*0.8)+', height='+Math.floor(window.screen.availHeight*0.7)+', left='+ Math.floor(window.screen.availWidth*0.1)+ ', top='+Math.floor(window.screen.availHeight*0.15)+'');
	}
	arguments.callee.hWnd.focus();
	
	if(!callback)callback=function(url, org_path){
		if(url){
            add_multi_image($this.closest('.multi_image'), org_path)
		}
	}
    window['ckeditor_select_callback'] = callback;
}

function add_multi_image(obj, url) {
    
    var field = obj.data('field');
    var mdl = obj.data('mdl');
    var maxLength  = parseInt(obj.data('length'));
    
    if (maxLength > 0 && obj.find('.file_list ul').find('li').length >= maxLength) {
        layer.msg('最多'+maxLength+'张图片', {
            time: 1000
        })
        return true;
    }
    
    
    var fileHtml  = '<li style="background-image: url(' + wwwroot + url +');" class="coverBg">';
        fileHtml +=    '<a href="javascript:void(0);" class="image-sorted-left"><i class="layui-icon-left layui-icon"></i></a>';
        fileHtml +=    '<a href="javascript:void(0);" class="image-delete"><i class="layui-icon-delete layui-icon"></i></a>';
        fileHtml +=    '<a href="javascript:void(0);" class="image-sorted-right"><i class="layui-icon-right layui-icon"></i></a>';
        fileHtml += '</li>';
    var inputHtml = '<input name="data['+mdl+']['+field+'][]" type="hidden" value="'+url+'"/>';
    
    obj.find('.file_list ul').append($(fileHtml))
    obj.find('.input_list').append($(inputHtml))
    return true;
}






