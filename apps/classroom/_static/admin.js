var MY_JS_PATH = SITE_URL+'/apps/'+APPNAME+'/_static/';

//导入后台公用的JS
document.write(unescape('%3Cscript src="'+MY_JS_PATH+'admin/common.js"%3E%3C/script%3E'));

document.write(unescape('%3Cscript src="'+MY_JS_PATH+'mz.js"%3E%3C/script%3E'));
document.write(unescape('%3Cscript src="'+MY_JS_PATH+'wayne.js"%3E%3C/script%3E'));
