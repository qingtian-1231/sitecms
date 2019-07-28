$(document).ready(function(){
	
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


function return_size(bytes) {
	var kb = 1024;         //Kilobyte   
	var mb = 1024 * kb;    //Megabyte   
	var gb = 1024 * mb;    //Gigabyte   
	var tb = 1024 * gb;    //Terabyte   

	if(bytes < kb)   
		return bytes + "B";   
	else if(bytes < mb)   
		return (bytes/kb).toFixed(2) + "KB";   
	else if(bytes < gb)   
		return (bytes/mb).toFixed(2) + "MB";   
	else if(bytes < tb)   
		return (bytes/gb).toFixed(2) + "GB";   
	else  
		return (bytes/tb).toFixed(2) + "TB";   
}