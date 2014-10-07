<?php


Route::get('rapyd-ajax/{hash}', array('as' => 'rapyd.remote', 'uses' => 'Zofe\Rapyd\Controllers\AjaxController@getRemote'));


## DataGrid 
\Zofe\Rapyd\Router::get(null, 'ord=(-?)(\w+)', array('as'=>'orderby', function($direction, $field) {
    echo 'ordina<br>';
    //dd($direction,$field);
    \Event::queue('dataset.sort', array($direction, $field));
}))->remove('page');

\Zofe\Rapyd\Router::get('/pg/(\d+)', null, array('as'=>'page', function($page) {
    echo 'pagina<br>';
    \Event::queue('dataset.page', $page);
}));


## DataForm 
\Zofe\Rapyd\Router::post(null, 'process=1' , array('as'=>'process', function() {
    \Event::queue('dataform.save');
}));

## DataEdit
\Zofe\Rapyd\Router::post(null, 'insert=(\d+)', array('as'=>'store', function($id) {
    \Event::queue('dataedit.insert', $id);
}));
\Zofe\Rapyd\Router::patch(null, 'update=(\d+)', array('as'=>'update',  function($id) {
    \Event::queue('dataedit.update', $id);
}));
\Zofe\Rapyd\Router::get(null, 'delete=(\d+)', array('as'=>'delete', function($id) {
    \Event::queue('dataedit.delete', $id);
}));

\Zofe\Rapyd\Router::delete(null, 'do_delete=(\d+)', array('as'=>'do_delete', function($id) {
    \Event::queue('dataedit.do_delete', $id);
}));


\Zofe\Rapyd\Router::dispatch();