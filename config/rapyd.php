<?php


return [


    /*
    |--------------------------------------------------------------------------
    | Data Edit defaults
    |--------------------------------------------------------------------------
    */
    'data_edit' => [
        'button_position' => [
            'save' => 'BL', // BR = Bottom Right, BL = Bottom Left, TL, TR
            'show' => 'TR',
            'modify' => 'TR',
            'undo' => 'TR',
            'delete' => 'BL',
            ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Cell sanitization defaults
    |--------------------------------------------------------------------------
    */
    'sanitize' => [
        'num_characters' => 100, // Number of characters to return during cell value sanitization. 0 = no limit
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Class
    |--------------------------------------------------------------------------
    */
    'field'=> [
        'attributes' => ['class'=>'form-control'],
    ],
	
	/*
	|--------------------------------------------------------------------------
	| TinyMCE configuration
	|--------------------------------------------------------------------------
	*/
	'tinymce' => [
		'language' => false // Could be any value equal to filename from folder `public\assets\tinymce\langs` (ex. 'en_GB') or `false`
	]
];

