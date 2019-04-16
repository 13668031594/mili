"use strict";
function ajax_submit_func( form , callback , extras ){
    //console.log('获得的属性', typeof form ) ;
    callback = callback || default_callback ;

    if( !form ) return false ;

    var useForm = $(form) ;
    var data = new FormData(useForm[0]);
    if( extras ){
        for( var i in extras ){
            data.append( i , extras[i] ) ;
        }
    }
    $.ajax({
        type:useForm.prop('method') ,
        url:useForm.prop('action')  ,
        cache:false,
        contentType: false,
        processData: false,
        data:data,
        beforeSend:function(){

        },
        complete:function(){

        },
        success:callback ,
        error:error
    });

    return false ;
}

function error(error){
    document.write(error.responseText) ;
}
function default_callback( data ){
    console.log(data) ;
}

function order_callback(){

}